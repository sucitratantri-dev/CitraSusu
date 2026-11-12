<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $key = config('services.gemini.key');

        if (!$key) {
            return response()->json([
                'reply' => 'Maaf, layanan AI belum dikonfigurasi.'
            ], 503);
        }

        $sessionId = $request->session()->getId();
        $message = trim($request->message);

        try {
            DB::table('ai_chat_messages')->insert([
                'session_id' => $sessionId,
                'sender' => 'user',
                'message' => $message,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Ambil kategori
            $categories = DB::table('categories')
                ->where('is_active', 1)
                ->get(['id', 'name', 'slug']);

            // Ambil produk beserta kategori
            $query = DB::table('products')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->where('products.is_active', 1)
                ->whereNull('products.deleted_at');

            // Pencarian berdasarkan nama, kategori, dan deskripsi
            $query->where(function ($q) use ($message) {
                $q->where('products.name', 'LIKE', "%{$message}%")
                  ->orWhere('products.description', 'LIKE', "%{$message}%")
                  ->orWhere('categories.name', 'LIKE', "%{$message}%");
            });

            $products = $query
                ->select(
                    'products.name',
                    'products.description',
                    'products.price',
                    'products.stock',
                    'products.unit',
                    'categories.name as category'
                )
                ->limit(15)
                ->get();

            // Jika pencarian tidak menemukan hasil,
            // berikan katalog agar AI tetap dapat membantu.
            if ($products->isEmpty()) {
                $products = DB::table('products')
                    ->join('categories', 'categories.id', '=', 'products.category_id')
                    ->where('products.is_active', 1)
                    ->whereNull('products.deleted_at')
                    ->select(
                        'products.name',
                        'products.description',
                        'products.price',
                        'products.stock',
                        'products.unit',
                        'categories.name as category'
                    )
                    ->limit(15)
                    ->get();
            }

            $system = "Kamu adalah CitraSusu AI, asisten virtual toko Citra Susu.

Gunakan bahasa Indonesia yang ramah, sopan, dan mudah dipahami.
Panggil pelanggan dengan sebutan Bunda.

Tugasmu:
1. Membantu pelanggan mencari produk susu bayi.
2. Membantu mencari popok atau pampers.
3. Membantu mencari perlengkapan bayi.
4. Membantu mencari alat mandi dan perawatan bayi.
5. Membantu mencari kebutuhan rumah tangga.
6. Menjelaskan harga dan stok berdasarkan database.
7. Merekomendasikan produk yang sesuai dengan pertanyaan pelanggan.

DAFTAR KATEGORI:
" . json_encode($categories, JSON_UNESCAPED_UNICODE) . "

DATA PRODUK TOKO:
" . json_encode($products, JSON_UNESCAPED_UNICODE) . "

ATURAN:
- Jika Bunda bertanya ada susu, tampilkan produk kategori Susu Bayi.
- Jika bertanya pampers atau popok, tampilkan kategori Popok Bayi.
- Jika bertanya sabun atau alat mandi bayi, tampilkan kategori Perawatan Bayi.
- Jika bertanya perlengkapan bayi, tampilkan kategori Perlengkapan Bayi.
- Sebutkan nama, harga, dan stok sesuai database.
- Jangan mengarang harga maupun stok.
- Jika produk tidak ditemukan, arahkan pelanggan melihat katalog atau menghubungi admin.
- Jangan memberikan diagnosis atau dosis medis bayi.
- Jangan mengaku sudah membuat pesanan atau menerima pembayaran.
- Jawab secara ringkas dan ramah.";

            // Ambil riwayat percakapan
            $history = DB::table('ai_chat_messages')
                ->where('session_id', $sessionId)
                ->orderByDesc('id')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();

            $contents = [];

            foreach ($history as $item) {
                $contents[] = [
                    'role' => $item->sender === 'user' ? 'user' : 'model',
                    'parts' => [
                        ['text' => $item->message]
                    ]
                ];
            }

            $response = Http::timeout(45)
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/'
                    . config('services.gemini.model')
                    . ':generateContent?key=' . $key,
                    [
                        'system_instruction' => [
                            'parts' => [
                                ['text' => $system]
                            ]
                        ],
                        'contents' => $contents,
                        'generationConfig' => [
                            'temperature' => 0.5,
                            'maxOutputTokens' => 800
                        ]
                    ]
                );

            if (!$response->successful()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);

                return response()->json([
                    'reply' => 'Maaf Bunda, layanan AI sedang mengalami gangguan. Silakan coba kembali.'
                ], 502);
            }

            $reply = collect(
                $response->json('candidates.0.content.parts', [])
            )->pluck('text')->filter()->implode("\n");

            if (!$reply) {
                $reply = 'Maaf Bunda, saya belum dapat memberikan jawaban.';
            }

            DB::table('ai_chat_messages')->insert([
                'session_id' => $sessionId,
                'sender' => 'ai',
                'message' => $reply,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'reply' => $reply
            ]);

        } catch (\Throwable $e) {
            Log::error('CitraSusu AI exception', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'reply' => 'Maaf Bunda, terjadi gangguan koneksi. Silakan coba kembali.'
            ], 500);
        }
    }
}
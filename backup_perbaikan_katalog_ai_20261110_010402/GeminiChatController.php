<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class GeminiChatController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $key = config('services.gemini.key');

        if (!$key) {
            return response()->json([
                'reply' => 'Maaf, layanan AI belum dikonfigurasi.'
            ], 503);
        }

        $sessionId = $request->session()->getId();
        $message = trim($request->message);

        DB::table('ai_chat_messages')->insert([
            'session_id' => $sessionId,
            'sender' => 'user',
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $products = [];

        try {
            if (Schema::hasTable('products')) {
                $columns = Schema::getColumnListing('products');

                $nameColumn = collect(['name', 'product_name', 'nama_produk'])
                    ->first(fn($column) => in_array($column, $columns));

                if ($nameColumn) {
                    $selected = array_values(array_intersect(
                        ['id', $nameColumn, 'price', 'selling_price',
                         'stock', 'category_id', 'description'],
                        $columns
                    ));

                    $query = DB::table('products');

                    $query->where($nameColumn, 'LIKE', '%' . $message . '%');

                    $products = $query->limit(8)->get($selected)->toArray();

                    if (empty($products)) {
                        $products = DB::table('products')
                            ->limit(8)
                            ->get($selected)
                            ->toArray();
                    }
                }
            }
        } catch (\Throwable $e) {
            $products = [];
        }

        $system = "Kamu adalah CitraSusu AI, asisten ramah toko perlengkapan bayi.
Gunakan bahasa Indonesia yang sopan, hangat, dan mudah dipahami.
Bantu pelanggan mencari produk, kategori, dan informasi toko.
Data produk berikut berasal dari database toko:
" . json_encode($products, JSON_UNESCAPED_UNICODE) . "
Jangan mengarang harga atau stok. Jika data tidak tersedia, katakan bahwa informasi perlu dikonfirmasi admin.
Jangan memberikan diagnosis atau dosis medis bayi. Arahkan pertanyaan medis kepada tenaga kesehatan.
Jangan mengaku telah membuat pesanan atau pembayaran.";

        $history = DB::table('ai_chat_messages')
            ->where('session_id', $sessionId)
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $contents = [];

        foreach ($history as $item) {
            $contents[] = [
                'role' => $item->sender === 'user' ? 'user' : 'model',
                'parts' => [['text' => $item->message]],
            ];
        }

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/'
                    . config('services.gemini.model')
                    . ':generateContent?key=' . $key,
                    [
                        'system_instruction' => [
                            'parts' => [['text' => $system]],
                        ],
                        'contents' => $contents,
                        'generationConfig' => [
                            'temperature' => 0.6,
                            'maxOutputTokens' => 700,
                        ],
                    ]
                );

            if (!$response->successful()) {
                return response()->json([
                    'reply' => 'Maaf, AI sedang mengalami gangguan. Silakan coba kembali.'
                ], 502);
            }

            $reply = collect(
                $response->json('candidates.0.content.parts', [])
            )->pluck('text')->filter()->implode("\n");

            if (!$reply) {
                $reply = 'Maaf, saya belum bisa menjawab pertanyaan tersebut.';
            }

            DB::table('ai_chat_messages')->insert([
                'session_id' => $sessionId,
                'sender' => 'ai',
                'message' => $reply,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['reply' => $reply]);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'reply' => 'Koneksi AI terputus. Silakan coba beberapa saat lagi.'
            ], 502);
        }
    }
}
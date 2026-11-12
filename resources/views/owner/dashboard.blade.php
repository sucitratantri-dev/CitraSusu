@extends('layouts.app')
@section('title','Dashboard Owner')
@section('content')
<div class="pagehead"><div><h1>Dashboard Owner</h1><div class="sub">Ringkasan dan pengawasan operasional Citra Susu.</div></div><span class="pill">{{ now()->translatedFormat('d F Y') }}</span></div>
<div class="hero"><h2>Selamat datang, {{ auth()->user()->name }} 👋</h2><div>Pantau kondisi toko dan hasil pekerjaan tim dari satu tempat.</div></div>
<div class="grid">
@foreach($stats as $label=>$value)
<div class="stat"><div class="label">{{ $label }}</div><strong>{{ str_contains(strtolower($label),'penjualan') || str_contains(strtolower($label),'pendapatan') ? 'Rp '.number_format($value,0,',','.') : number_format($value,0,',','.') }}</strong></div>
@endforeach
</div>
<div class="panel" style="margin-top:18px"><div class="pagehead"><h2>Menu Pengawasan</h2></div>
<div class="row" style="flex-wrap:wrap">
<a class="btn light" href="{{ route('owner.sales') }}">Laporan Penjualan</a>
<a class="btn light" href="{{ route('owner.list','batches') }}">Monitoring Expired</a>
<a class="btn light" href="{{ route('admin.returns') }}">Persetujuan Retur</a>
<a class="btn light" href="{{ route('owner.list','payments') }}">Pembayaran</a>
<a class="btn light" href="{{ route('owner.list','deliveries') }}">Pengiriman</a>
<a class="btn light" href="{{ route('owner.list','employees') }}">Kinerja Karyawan</a>
</div></div>
<div class="panel"><h2>Penjualan 7 Hari Terakhir</h2><div style="display:flex;gap:10px;align-items:end;height:180px;padding:12px 4px">
@php($max=max(1,(float)$sales->max('total')))
@foreach($sales as $s)<div style="flex:1;text-align:center"><div title="Rp {{ number_format($s->total,0,',','.') }}" style="height:{{ max(5,($s->total/$max)*135) }}px;background:linear-gradient(180deg,#f661ae,#af5aa7);border-radius:9px 9px 3px 3px"></div><small>{{ \Carbon\Carbon::parse($s->day)->format('d/m') }}</small></div>@endforeach
@if($sales->isEmpty())<div class="empty" style="width:100%">Belum ada data penjualan.</div>@endif
</div></div>
<div class="panel"><div class="pagehead"><h2>Pesanan Terbaru</h2><a class="btn sm light" href="{{ route('owner.list','orders') }}">Lihat semua</a></div><div class="tablewrap"><table><thead><tr><th>Nomor</th><th>Pembeli</th><th>Total</th><th>Status</th><th>Pembayaran</th><th>Tanggal</th></tr></thead><tbody>
@forelse($latestOrders as $o)<tr><td>{{ $o->order_number }}</td><td>{{ $o->buyer }}</td><td>Rp {{ number_format($o->total,0,',','.') }}</td><td>{{ $o->status }}</td><td>{{ $o->payment_status }}</td><td>{{ \Carbon\Carbon::parse($o->created_at)->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="6" class="empty">Belum ada pesanan.</td></tr>@endforelse
</tbody></table></div></div>
<div class="panel"><div class="pagehead"><h2>Perhatian Kedaluwarsa</h2><a class="btn sm light" href="{{ route('owner.list','batches') }}">Lihat semua</a></div><div class="tablewrap"><table><thead><tr><th>Produk</th><th>Batch</th><th>Stok</th><th>Kedaluwarsa</th><th>Sisa hari</th></tr></thead><tbody>
@forelse($expiry as $b)<tr><td>{{ $b->product_name }}</td><td>{{ $b->batch_code }}</td><td>{{ $b->quantity }}</td><td>{{ $b->expiry_date }}</td><td>{{ now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($b->expiry_date),false) }}</td></tr>@empty<tr><td colspan="5" class="empty">Tidak ada batch yang perlu perhatian.</td></tr>@endforelse
</tbody></table></div></div>
<div class="panel"><h2>Aktivitas Karyawan Terbaru</h2><div class="tablewrap"><table><thead><tr><th>Karyawan</th><th>Aktivitas</th><th>Keterangan</th><th>Waktu</th></tr></thead><tbody>
@forelse($activity as $a)<tr><td>{{ $a->actor ?? 'Pengguna' }}</td><td>{{ $a->action }}</td><td>{{ $a->description }}</td><td>{{ \Carbon\Carbon::parse($a->created_at)->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="4" class="empty">Belum ada aktivitas tercatat.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
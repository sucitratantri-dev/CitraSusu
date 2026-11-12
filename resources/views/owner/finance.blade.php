@extends('layouts.app')
@section('title','Pendapatan & Keuntungan')
@section('content')
<div class="pagehead"><div><h1>Laporan Pendapatan & Keuntungan</h1><div class="sub">Ringkasan berdasarkan transaksi berstatus dibayar.</div></div></div>
<div class="panel"><form method="GET" class="row" style="align-items:end"><div class="field"><label>Dari tanggal</label><input type="date" name="from" value="{{ $from }}"></div><div class="field"><label>Sampai tanggal</label><input type="date" name="to" value="{{ $to }}"></div><div style="flex:0 0 auto"><button class="btn">Tampilkan</button></div></form></div>
<div class="grid">
<div class="stat"><div class="label">Pendapatan dibayar</div><strong>Rp {{ number_format($revenue,0,',','.') }}</strong></div>
<div class="stat"><div class="label">Estimasi modal produk terjual</div><strong>Rp {{ number_format($cost,0,',','.') }}</strong></div>
<div class="stat"><div class="label">Estimasi laba kotor</div><strong>Rp {{ number_format($grossProfit,0,',','.') }}</strong></div>
</div>
<div class="panel" style="margin-top:18px"><p class="muted">Catatan: estimasi laba kotor dihitung dari pendapatan pesanan yang sudah dibayar dikurangi harga modal produk berdasarkan data produk. Biaya operasional, pajak, dan biaya lain tidak dihitung karena belum tersedia dalam skema database.</p></div>
@endsection
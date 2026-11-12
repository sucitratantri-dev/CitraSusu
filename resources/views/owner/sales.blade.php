@extends('layouts.app')
@section('title','Laporan Penjualan')
@section('content')
<div class="pagehead"><div><h1>Laporan Penjualan</h1><div class="sub">Pantau transaksi berdasarkan periode.</div></div></div>
<div class="grid">@foreach($totals as $label=>$value)<div class="stat"><div class="label">{{ $label }}</div><strong>{{ $label==='Transaksi' ? number_format($value) : 'Rp '.number_format($value,0,',','.') }}</strong></div>@endforeach</div>
<div class="panel" style="margin-top:18px">
<form method="GET" class="row" style="align-items:end"><div class="field"><label>Dari tanggal</label><input type="date" name="from" value="{{ $from }}"></div><div class="field"><label>Sampai tanggal</label><input type="date" name="to" value="{{ $to }}"></div><div style="flex:0 0 auto"><button class="btn">Tampilkan</button></div></form>
<div class="tablewrap"><table><thead><tr><th>Nomor</th><th>Pembeli</th><th>Total</th><th>Status</th><th>Pembayaran</th><th>Tanggal</th></tr></thead><tbody>
@forelse($rows as $o)<tr><td>{{ $o->order_number }}</td><td>{{ $o->buyer }}</td><td>Rp {{ number_format($o->total,0,',','.') }}</td><td>{{ $o->status }}</td><td>{{ $o->payment_status }}</td><td>{{ $o->created_at }}</td></tr>@empty<tr><td colspan="6" class="empty">Tidak ada transaksi pada periode ini.</td></tr>@endforelse
</tbody></table></div>{{ $rows->links() }}</div>
@endsection
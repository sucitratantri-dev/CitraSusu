@extends('layouts.app')
@section('title',$title)
@section('content')
<div class="pagehead"><div><h1>{{ $title }}</h1><div class="sub">Informasi untuk pemantauan Owner.</div></div></div>
<div class="panel">
<form method="GET" class="row" style="align-items:end;margin-bottom:12px"><div class="field"><label>Pencarian</label><input name="q" value="{{ request('q') }}" placeholder="Cari data..."></div><div style="flex:0 0 auto"><button class="btn">Cari</button></div></form>
<div class="tablewrap"><table><thead><tr>@foreach($columns as $key=>$label)<th>{{ $label }}</th>@endforeach</tr></thead><tbody>
@forelse($rows as $row)<tr>@foreach($columns as $key=>$label)<td>
@php($v=$row->$key ?? '-')
@if(in_array($key,['price','purchase_price','total','amount']))Rp {{ number_format((float)$v,0,',','.') }}
@elseif($key==='is_active'){{ $v ? 'Aktif' : 'Nonaktif' }}
@else{{ $v ?: '-' }}@endif
</td>@endforeach</tr>@empty<tr><td colspan="{{ count($columns) }}" class="empty">Belum ada data.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $rows->links() }}</div></div>
@endsection
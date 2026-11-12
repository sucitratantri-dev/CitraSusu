@extends('layouts.app')

@section('content')
<style>
.shop-wrap{max-width:1200px;margin:auto;padding:30px 18px}
.shop-title{color:#ec4899;font-size:28px;font-weight:800}
.shop-categories{display:flex;gap:10px;flex-wrap:wrap;margin:22px 0}
.shop-cat{padding:11px 19px;border-radius:30px;background:#fff;
border:1px solid #f3b4d5;color:#9d4775;text-decoration:none;font-weight:600}
.shop-cat.active,.shop-cat:hover{background:#ec4899;color:white}
.shop-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:20px}
.shop-card{background:white;border-radius:17px;padding:15px;
border:1px solid #f1e4eb;box-shadow:0 4px 15px #00000009}
.shop-card img{width:100%;height:160px;object-fit:contain;background:#fff8fb;border-radius:12px}
.shop-card h3{font-size:16px;color:#333;margin:12px 0 5px}
.shop-price{font-size:19px;color:#ec4899;font-weight:800}
.shop-stock{font-size:13px;color:#777}
.shop-button{display:block;text-align:center;background:#ec4899;color:white;
padding:10px;border-radius:10px;text-decoration:none;margin-top:12px}
.shop-search{width:100%;padding:13px;border:1px solid #f1b5d3;
border-radius:12px;margin-top:15px}
</style>

<div class="shop-wrap">
    <h1 class="shop-title">Belanja Kebutuhan Si Kecil 💗</h1>
    <p>Temukan berbagai kebutuhan ibu dan bayi di Citra Susu.</p>

    <form action="{{ route('shop') }}" method="GET">
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        <input class="shop-search" type="text" name="q"
            value="{{ request('q') }}"
            placeholder="Cari susu, popok, perlengkapan bayi...">
    </form>

    <h3 style="margin-top:25px;font-weight:700">Kategori Produk</h3>

    <div class="shop-categories">
        <a class="shop-cat {{ !request('category') ? 'active' : '' }}"
           href="{{ route('shop') }}">Semua Produk</a>

        @foreach($categories as $category)
            <a class="shop-cat {{ request('category') == $category->slug ? 'active' : '' }}"
               href="{{ route('shop', ['category'=>$category->slug]) }}">
                {{ $category->icon ?: '🍼' }}
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    <h3 style="margin:25px 0 18px;font-weight:700">
        {{ request('category') ? $categories->firstWhere('slug',request('category'))?->name : 'Semua Produk' }}
    </h3>

    <div class="shop-grid">
        @forelse($products as $product)
            <div class="shop-card">
                @if($product->image)
                    <img src="{{ asset('storage/'.ltrim($product->image,'/')) }}"
                         alt="{{ $product->name }}">
                @else
                    <div style="height:160px;background:#fff8fb;border-radius:12px;
                    display:flex;align-items:center;justify-content:center;font-size:55px">
                        🍼
                    </div>
                @endif

                <div style="font-size:12px;color:#ec4899;margin-top:12px">
                    {{ $product->category }}
                </div>

                <h3>{{ $product->name }}</h3>

                <div class="shop-price">
                    Rp {{ number_format($product->price,0,',','.') }}
                </div>

                <div class="shop-stock">
                    Stok: {{ $product->stock }}
                </div>

                <a class="shop-button"
                   href="{{ url('/product/'.$product->id) }}">
                    Lihat Produk
                </a>
            </div>
        @empty
            <p>Produk belum tersedia pada kategori ini.</p>
        @endforelse
    </div>

    <div style="margin-top:25px">
        {{ $products->links() }}
    </div>
</div>
@endsection
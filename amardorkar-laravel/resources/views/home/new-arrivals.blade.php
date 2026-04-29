@extends('layouts.app')
@section('title', (app()->getLocale()==='bn' ? 'নতুন আগমন' : 'New Arrivals') . ' — ' . __('messages.site_name'))

@section('content')
<div class="fw-wrap" style="margin-top:28px;margin-bottom:48px;">

    {{-- Page Header --}}
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;flex-wrap:wrap;">
        <div style="background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;padding:8px 20px;border-radius:24px;font-size:15px;font-weight:700;letter-spacing:.3px;">
            ✨ {{ app()->getLocale()==='bn' ? 'নতুন' : 'NEW' }}
        </div>
        <h1 class="bengali-text mb-0" style="font-size:24px;font-weight:700;color:var(--text);">
            {{ app()->getLocale()==='bn' ? 'নতুন আগমন' : 'New Arrivals' }}
        </h1>
        <span style="color:var(--muted);font-size:14px;">
            ({{ $products->total() }} {{ app()->getLocale()==='bn' ? 'টি পণ্য' : 'products' }})
        </span>
    </div>

    {{-- Products Grid --}}
    @if($products->isEmpty())
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <h3 class="bengali-text">{{ app()->getLocale()==='bn' ? 'এখনো কোনো নতুন আগমন নেই' : 'No new arrivals yet' }}</h3>
        <a href="{{ route('home') }}" class="btn btn-success mt-3">{{ app()->getLocale()==='bn' ? 'হোমে ফিরুন' : 'Back to Home' }}</a>
    </div>
    @else
    <div class="product-grid">
        @foreach($products as $product)
        <div class="product-col">
            <div class="product-card" style="border:2px solid #e0f2fe;position:relative;">
                <span style="position:absolute;top:10px;left:10px;background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;z-index:1;">✨ NEW</span>
                <div class="product-image" style="cursor:pointer;" onclick="window.location='{{ route('product.show', $product->id) }}'">
                    @if($product->primaryImage)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                    @elseif($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                    @else
                        <i class="fas fa-image fa-3x text-muted"></i>
                    @endif
                    @if($product->activeDiscount)
                        <span class="product-badge">{{ number_format($product->activeDiscount->discount_percentage,0) }}% OFF</span>
                    @endif
                </div>
                <div class="product-info">
                    <h6 class="product-name bengali-text" style="cursor:pointer;" onclick="window.location='{{ route('product.show', $product->id) }}'">{{ $product->name }}</h6>
                    @if($product->description)
                        <p class="product-details-text bengali-text">{{ $product->description }}</p>
                    @endif
                    <div class="product-price">
                        @if($product->activeDiscount)
                            <span class="original-price-text">৳{{ number_format($product->price,0) }}</span>
                            ৳{{ number_format($product->activeDiscount->discount_price,0) }}
                        @else
                            ৳{{ number_format($product->price,0) }}
                        @endif
                    </div>
                    <div class="product-actions">
                        <button class="btn btn-success btn-sm" onclick="addToCart({{ $product->id }})">
                            <i class="fas fa-plus"></i> {{ __('messages.add_to_cart') }}
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="window.location='{{ route('product.show', $product->id) }}'">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div style="margin-top:32px;display:flex;justify-content:center;">
        {{ $products->links() }}
    </div>
    @endif
    @endif

</div>
@endsection

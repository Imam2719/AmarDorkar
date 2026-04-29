@extends('layouts.app')
@section('title', (app()->getLocale()==='bn' ? 'অফার' : 'Offers') . ' — ' . __('messages.site_name'))

@section('content')
<div class="fw-wrap" style="margin-top:28px;margin-bottom:48px;">

    {{-- Page Header --}}
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;flex-wrap:wrap;">
        <div style="background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;padding:8px 20px;border-radius:24px;font-size:15px;font-weight:700;">
            🏷️ {{ app()->getLocale()==='bn' ? 'বিশেষ অফার' : 'Special Offers' }}
        </div>
        <h1 class="bengali-text mb-0" style="font-size:24px;font-weight:700;color:var(--text);">
            {{ app()->getLocale()==='bn' ? 'অফার পণ্য' : 'Offer Products' }}
        </h1>
        <span style="color:var(--muted);font-size:14px;">
            ({{ $offerProducts->total() }} {{ app()->getLocale()==='bn' ? 'টি পণ্য' : 'products' }})
        </span>
    </div>

    {{-- Products Grid --}}
    @if($offerProducts->isEmpty())
    <div class="empty-state">
        <i class="fas fa-tag"></i>
        <h3 class="bengali-text">{{ app()->getLocale()==='bn' ? 'বর্তমানে কোনো অফার নেই' : 'No active offers right now' }}</h3>
        <a href="{{ route('home') }}" class="btn btn-success mt-3">{{ app()->getLocale()==='bn' ? 'হোমে ফিরুন' : 'Back to Home' }}</a>
    </div>
    @else
    <div class="product-grid">
        @foreach($offerProducts as $offer)
        @if($offer->product)
        <div class="product-col">
            <div class="product-card" style="border:2px solid #fde68a;position:relative;">
                <div class="product-image" style="cursor:pointer;" onclick="window.location='{{ route('product.show', $offer->product_id) }}'">
                    @if($offer->product->primaryImage)
                        <img src="{{ $offer->product->primaryImage->url }}" alt="{{ $offer->product->name }}" loading="lazy">
                    @elseif($offer->product->image_url)
                        <img src="{{ $offer->product->image_url }}" alt="{{ $offer->product->name }}" loading="lazy">
                    @else
                        <div style="width:100%;height:100%;background:#fef9c3;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-tag fa-3x" style="color:#f59e0b;"></i>
                        </div>
                    @endif
                    <span class="product-badge" style="background:#f59e0b;">
                        {{ number_format($offer->discount_percentage,0) }}% OFF
                    </span>
                </div>
                <div class="product-info">
                    <h6 class="product-name bengali-text" style="cursor:pointer;" onclick="window.location='{{ route('product.show', $offer->product_id) }}'">
                        {{ $offer->product->name }}
                    </h6>
                    <div class="product-price">
                        <span class="original-price-text">৳{{ number_format($offer->original_price,0) }}</span>
                        ৳{{ number_format($offer->discount_price,0) }}
                    </div>
                    <div style="font-size:11px;color:#10b981;font-weight:600;margin-bottom:8px;">
                        <i class="fas fa-clock me-1"></i>
                        {{ app()->getLocale()==='bn' ? 'শেষ হবে:' : 'Ends:' }}
                        {{ $offer->end_date->format('d M Y') }}
                    </div>
                    <div class="product-actions">
                        <button class="btn btn-success btn-sm" onclick="addToCart({{ $offer->product_id }})">
                            <i class="fas fa-cart-plus"></i> {{ __('messages.add_to_cart') }}
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="window.location='{{ route('product.show', $offer->product_id) }}'">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($offerProducts->hasPages())
    <div style="margin-top:32px;display:flex;justify-content:center;">
        {{ $offerProducts->links() }}
    </div>
    @endif
    @endif

</div>
@endsection
@extends('layouts.app')
@section('title', (app()->getLocale()==='bn' ? 'নতুন আগমন' : 'New Arrivals') . ' — ' . __('messages.site_name'))

@push('styles')
<style>
#is-scroll-loader { display:none; text-align:center; padding:24px 0; }
#is-scroll-loader .is-spinner { display:inline-block; width:28px; height:28px; border:3px solid var(--border); border-top-color:var(--primary); border-radius:50%; animation:is-spin .7s linear infinite; }
@keyframes is-spin { to { transform:rotate(360deg) } }
#is-end-marker { display:none; text-align:center; padding:16px 0; font-size:13px; color:var(--muted); }
</style>
@endpush

@section('content')
@php $isBn = app()->getLocale() === 'bn'; @endphp
<div class="fw-wrap" style="margin-top:28px;margin-bottom:48px;">

    {{-- পেজ হেডার --}}
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;flex-wrap:wrap;">
        <div style="background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;padding:8px 20px;border-radius:24px;font-size:15px;font-weight:700;letter-spacing:.3px;">
            ✨ {{ $isBn ? 'নতুন' : 'NEW' }}
        </div>
        <h1 class="bengali-text mb-0" style="font-size:24px;font-weight:700;color:var(--text);">
            {{ $isBn ? 'নতুন আগমন' : 'New Arrivals' }}
        </h1>
        <span style="color:var(--muted);font-size:14px;">
            (<span id="is-total-count">{{ $total }}</span> {{ $isBn ? 'টি পণ্য' : 'products' }})
        </span>
    </div>

    {{-- Products Grid --}}
    @if($products->isEmpty())
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <h3 class="bengali-text">{{ $isBn ? 'এখনো কোনো নতুন আগমন নেই' : 'No new arrivals yet' }}</h3>
        <a href="{{ route('home') }}" class="btn btn-success mt-3">{{ $isBn ? 'হোমে ফিরুন' : 'Back to Home' }}</a>
    </div>
    @else

    <div class="product-grid" id="is-product-grid">
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
                            <i class="fas fa-cart-plus me-1"></i> {{ __('messages.add_to_cart') }}
                        </button>
                        <button class="btn btn-outline-success btn-sm btn-view" onclick="window.location='{{ route('product.show', $product->id) }}'">
                            <i class="fas fa-magnifying-glass-plus"></i>
                            <span class="view-label">{{ $isBn ? 'বিস্তারিত' : 'Details' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- লোডিং স্পিনার --}}
    <div id="is-scroll-loader">
        <span class="is-spinner"></span>
        <span style="margin-left:10px;font-size:13px;color:var(--muted);">
            {{ $isBn ? 'আরও পণ্য লোড হচ্ছে...' : 'Loading more products...' }}
        </span>
    </div>
    {{-- শেষ চিহ্ন --}}
    <div id="is-end-marker">
        <i class="fas fa-check-circle" style="color:var(--primary);margin-right:6px;"></i>
        {{ $isBn ? 'সব পণ্য দেখা হয়েছে' : 'All products loaded' }}
    </div>
    {{-- Sentinel div — IntersectionObserver এটি ট্র্যাক করে --}}
    <div id="is-sentinel" style="height:1px;"></div>

    @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    const LOAD_URL = "{{ route('new-arrivals') }}";
    const IS_BN    = {{ $isBn ? 'true' : 'false' }};
    const ADD_CART_LABEL = IS_BN ? 'কার্টে যোগ করুন' : 'Add to Cart';
    const DETAILS_LABEL  = IS_BN ? 'বিস্তারিত' : 'Details';

    let currentPage = 2;
    let isLoading   = false;
    let hasMore     = {{ $hasMore ? 'true' : 'false' }};

    const grid      = document.getElementById('is-product-grid');
    const loader    = document.getElementById('is-scroll-loader');
    const endMarker = document.getElementById('is-end-marker');
    const sentinel  = document.getElementById('is-sentinel');

    if (!hasMore && endMarker) endMarker.style.display = 'block';

    async function loadMore() {
        if (isLoading || !hasMore || !grid) return;
        isLoading = true;
        if (loader) loader.style.display = 'block';

        try {
            const res  = await fetch(`${LOAD_URL}?page=${currentPage}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (data.products && data.products.length) {
                data.products.forEach(p => grid.insertAdjacentHTML('beforeend', buildCard(p)));
            }

            hasMore     = data.has_more;
            currentPage = data.next_page;

            if (!hasMore) {
                if (endMarker) endMarker.style.display = 'block';
                observer.disconnect();
            }
        } catch (e) {
            console.error('নতুন আগমন লোড ব্যর্থ:', e);
        } finally {
            isLoading = false;
            if (loader) loader.style.display = 'none';
        }
    }

    function buildCard(p) {
        const imgHtml = p.img
            ? `<img src="${p.img}" alt="${escHtml(p.name)}" loading="lazy">`
            : `<i class="fas fa-image fa-3x text-muted"></i>`;
        const badgeHtml = p.discount_pct
            ? `<span class="product-badge">${p.discount_pct}% OFF</span>`
            : '';
        const priceHtml = p.discount_price
            ? `<span class="original-price-text">৳${p.price}</span> ৳${p.discount_price}`
            : `৳${p.price}`;
        const descHtml = p.description
            ? `<p class="product-details-text bengali-text">${escHtml(p.description)}</p>`
            : '';
        return `
        <div class="product-col">
            <div class="product-card" style="border:2px solid #e0f2fe;position:relative;">
                <span style="position:absolute;top:10px;left:10px;background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;z-index:1;">✨ NEW</span>
                <div class="product-image" style="cursor:pointer;" onclick="window.location='${p.product_url}'">
                    ${imgHtml}${badgeHtml}
                </div>
                <div class="product-info">
                    <h6 class="product-name bengali-text" style="cursor:pointer;" onclick="window.location='${p.product_url}'">${escHtml(p.name)}</h6>
                    ${descHtml}
                    <div class="product-price">${priceHtml}</div>
                    <div class="product-actions">
                        <button class="btn btn-success btn-sm" onclick="addToCart(${p.id})">
                            <i class="fas fa-cart-plus me-1"></i> ${ADD_CART_LABEL}
                        </button>
                        <button class="btn btn-outline-success btn-sm btn-view" onclick="window.location='${p.product_url}'">
                            <i class="fas fa-magnifying-glass-plus"></i>
                            <span class="view-label">${DETAILS_LABEL}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // IntersectionObserver — sentinel দেখা গেলে নতুন পণ্য লোড করে
    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) loadMore();
    }, { rootMargin: '200px' });

    if (sentinel && hasMore) observer.observe(sentinel);
})();
</script>
@endpush

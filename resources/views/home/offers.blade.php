@extends('layouts.app')
@section('title', (app()->getLocale()==='bn' ? 'অফার' : 'Offers') . ' — ' . __('messages.site_name'))

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
        <div style="background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;padding:8px 20px;border-radius:24px;font-size:15px;font-weight:700;">
            🏷️ {{ $isBn ? 'বিশেষ অফার' : 'Special Offers' }}
        </div>
        <h1 class="bengali-text mb-0" style="font-size:24px;font-weight:700;color:var(--text);">
            {{ $isBn ? 'অফার পণ্য' : 'Offer Products' }}
        </h1>
        <span style="color:var(--muted);font-size:14px;">
            (<span id="is-total-count">{{ $total }}</span> {{ $isBn ? 'টি পণ্য' : 'products' }})
        </span>
    </div>

    {{-- Products Grid --}}
    @if($offerProducts->isEmpty())
    <div class="empty-state">
        <i class="fas fa-tag"></i>
        <h3 class="bengali-text">{{ $isBn ? 'বর্তমানে কোনো অফার নেই' : 'No active offers right now' }}</h3>
        <a href="{{ route('home') }}" class="btn btn-success mt-3">{{ $isBn ? 'হোমে ফিরুন' : 'Back to Home' }}</a>
    </div>
    @else

    <div class="product-grid" id="is-product-grid">
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
                        {{ $isBn ? 'শেষ হবে:' : 'Ends:' }} {{ $offer->end_date->format('d M Y') }}
                    </div>
                    <div class="product-actions">
                        <button class="btn btn-success btn-sm" onclick="addToCart({{ $offer->product_id }})">
                            <i class="fas fa-cart-plus me-1"></i> {{ __('messages.add_to_cart') }}
                        </button>
                        <button class="btn btn-outline-success btn-sm btn-view" onclick="window.location='{{ route('product.show', $offer->product_id) }}'">
                            <i class="fas fa-magnifying-glass-plus"></i>
                            <span class="view-label">{{ $isBn ? 'বিস্তারিত' : 'Details' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>

    {{-- লোডিং স্পিনার --}}
    <div id="is-scroll-loader">
        <span class="is-spinner"></span>
        <span style="margin-left:10px;font-size:13px;color:var(--muted);">
            {{ $isBn ? 'আরও অফার লোড হচ্ছে...' : 'Loading more offers...' }}
        </span>
    </div>
    {{-- শেষ চিহ্ন --}}
    <div id="is-end-marker">
        <i class="fas fa-check-circle" style="color:var(--primary);margin-right:6px;"></i>
        {{ $isBn ? 'সব অফার দেখা হয়েছে' : 'All offers loaded' }}
    </div>
    {{-- Sentinel --}}
    <div id="is-sentinel" style="height:1px;"></div>

    @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    const LOAD_URL       = "{{ route('offers') }}";
    const IS_BN          = {{ $isBn ? 'true' : 'false' }};
    const ADD_CART_LABEL = IS_BN ? 'কার্টে যোগ করুন' : 'Add to Cart';
    const DETAILS_LABEL  = IS_BN ? 'বিস্তারিত' : 'Details';
    const ENDS_LABEL     = IS_BN ? 'শেষ হবে:' : 'Ends:';

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

            if (data.offers && data.offers.length) {
                data.offers.forEach(o => grid.insertAdjacentHTML('beforeend', buildCard(o)));
            }

            hasMore     = data.has_more;
            currentPage = data.next_page;

            if (!hasMore) {
                if (endMarker) endMarker.style.display = 'block';
                observer.disconnect();
            }
        } catch (e) {
            console.error('অফার লোড ব্যর্থ:', e);
        } finally {
            isLoading = false;
            if (loader) loader.style.display = 'none';
        }
    }

    function buildCard(o) {
        const imgHtml = o.img
            ? `<img src="${o.img}" alt="${escHtml(o.name)}" loading="lazy">`
            : `<div style="width:100%;height:100%;background:#fef9c3;display:flex;align-items:center;justify-content:center;"><i class="fas fa-tag fa-3x" style="color:#f59e0b;"></i></div>`;
        return `
        <div class="product-col">
            <div class="product-card" style="border:2px solid #fde68a;position:relative;">
                <div class="product-image" style="cursor:pointer;" onclick="window.location='${o.product_url}'">
                    ${imgHtml}
                    <span class="product-badge" style="background:#f59e0b;">${o.discount_percentage}% OFF</span>
                </div>
                <div class="product-info">
                    <h6 class="product-name bengali-text" style="cursor:pointer;" onclick="window.location='${o.product_url}'">${escHtml(o.name)}</h6>
                    <div class="product-price">
                        <span class="original-price-text">৳${o.original_price}</span>
                        ৳${o.discount_price}
                    </div>
                    <div style="font-size:11px;color:#10b981;font-weight:600;margin-bottom:8px;">
                        <i class="fas fa-clock me-1"></i>${ENDS_LABEL} ${o.end_date}
                    </div>
                    <div class="product-actions">
                        <button class="btn btn-success btn-sm" onclick="addToCart(${o.product_id})">
                            <i class="fas fa-cart-plus me-1"></i> ${ADD_CART_LABEL}
                        </button>
                        <button class="btn btn-outline-success btn-sm btn-view" onclick="window.location='${o.product_url}'">
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

    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) loadMore();
    }, { rootMargin: '200px' });

    if (sentinel && hasMore) observer.observe(sentinel);
})();
</script>
@endpush

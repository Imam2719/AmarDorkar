@extends('layouts.app')
@section('title', __('messages.site_name'))

@section('content')

{{-- ══ HERO BANNER SLIDER ══ --}}
@if($banners->count())
<section style="margin:0;padding:0;background:var(--surface-2);">
    <div style="position:relative;overflow:hidden;width:100%;user-select:none;" id="heroBannerWrap">
        {{-- Slides --}}
        <div id="heroBannerTrack" style="display:flex;transition:transform .5s cubic-bezier(.4,0,.2,1);">
            @foreach($banners as $banner)
            <div style="min-width:100%;position:relative;">
                <img src="{{ $banner->image_url }}"
                     alt="{{ $banner->title ?? 'Banner' }}"
                     style="width:100%;height:auto;display:block;">
                @if($banner->title || $banner->button_text)
                <div style="position:absolute;inset:0;display:flex;align-items:center;padding:0 clamp(20px,5vw,80px);">
                    <div style="max-width:520px;">
                        @if($banner->title)
                        <h2 class="bengali-text" style="font-size:clamp(22px,3.5vw,40px);font-weight:800;color:#fff;text-shadow:0 2px 12px rgba(0,0,0,.5);margin-bottom:8px;line-height:1.2;">{{ $banner->title }}</h2>
                        @endif
                        @if($banner->subtitle)
                        <p class="bengali-text" style="font-size:clamp(13px,1.8vw,18px);color:rgba(255,255,255,.9);text-shadow:0 1px 6px rgba(0,0,0,.5);margin-bottom:18px;">{{ $banner->subtitle }}</p>
                        @endif
                        @if($banner->button_text)
                        <a href="{{ $banner->button_link ?? '#' }}" style="display:inline-block;background:var(--primary);color:#fff;padding:11px 28px;border-radius:8px;font-weight:700;text-decoration:none;font-size:14px;transition:background .2s,transform .2s;" class="bengali-text">
                            {{ $banner->button_text }}
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Prev/Next arrows --}}
        @if($banners->count() > 1)
        <button onclick="heroBannerMove(-1)" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:40px;height:40px;background:rgba(255,255,255,.85);border:none;border-radius:50%;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 10px rgba(0,0,0,.2);transition:background .2s;z-index:5;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255,255,255,.85)'">
            <i class="fas fa-chevron-left" style="color:#333;"></i>
        </button>
        <button onclick="heroBannerMove(1)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);width:40px;height:40px;background:rgba(255,255,255,.85);border:none;border-radius:50%;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 10px rgba(0,0,0,.2);transition:background .2s;z-index:5;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255,255,255,.85)'">
            <i class="fas fa-chevron-right" style="color:#333;"></i>
        </button>

        {{-- Dots --}}
        <div id="heroBannerDots" style="position:absolute;bottom:12px;left:50%;transform:translateX(-50%);display:flex;gap:7px;z-index:5;">
            @foreach($banners as $i => $banner)
            <button onclick="heroBannerGoTo({{ $i }})" data-dot="{{ $i }}" style="width:{{ $i===0 ? '22px' : '8px' }};height:8px;border-radius:4px;background:{{ $i===0 ? 'var(--primary)' : 'rgba(255,255,255,.6)' }};border:none;cursor:pointer;transition:all .3s;padding:0;"></button>
            @endforeach
        </div>
        @endif
    </div>
</section>
@push('scripts')
<script>
(function(){
    const total = {{ $banners->count() }};
    if(total <= 1) return;
    let cur = 0, timer;
    const track = document.getElementById('heroBannerTrack');
    const dots  = document.querySelectorAll('#heroBannerDots button');

    function goTo(n){
        cur = (n + total) % total;
        track.style.transform = 'translateX(-' + (cur * 100) + '%)';
        dots.forEach((d, i) => {
            d.style.width     = i === cur ? '22px' : '8px';
            d.style.background = i === cur ? 'var(--primary)' : 'rgba(255,255,255,.6)';
        });
    }
    function autoPlay(){ timer = setInterval(()=>goTo(cur+1), 4500); }
    function resetTimer(){ clearInterval(timer); autoPlay(); }

    window.heroBannerMove = function(dir){ goTo(cur+dir); resetTimer(); };
    window.heroBannerGoTo = function(n){ goTo(n); resetTimer(); };

    // touch / swipe
    let sx = 0;
    const wrap = document.getElementById('heroBannerWrap');
    wrap.addEventListener('touchstart', e=>{ sx = e.touches[0].clientX; }, {passive:true});
    wrap.addEventListener('touchend', e=>{
        const dx = e.changedTouches[0].clientX - sx;
        if(Math.abs(dx) > 40){ heroBannerMove(dx < 0 ? 1 : -1); }
    });

    autoPlay();
})();
</script>
@endpush
@endif

{{-- FLASH SALE --}}
<span id="section-flash-sale" style="display:block;margin-top:-80px;padding-top:80px;"></span>
@if($activeFlashSale && $activeFlashSale->products->count())
<section style="margin-top:20px;">
    <div class="fw-wrap">
        <div style="background:linear-gradient(135deg,#dc3545,#ff6b35);border-radius:16px;padding:18px 24px 0;color:#fff;overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="font-size:28px;">⚡</span>
                    <div>
                        <h2 style="margin:0;font-size:22px;font-weight:800;" class="bengali-text">{{ $activeFlashSale->title }}</h2>
                        <p style="margin:0;font-size:13px;opacity:.85;" class="bengali-text">
                            {{ app()->getLocale()==="bn" ? "সীমিত সময়ের অফার! তাড়াতাড়ি করুন" : "Limited time deals — grab before it's gone!" }}
                        </p>
                    </div>
                </div>
                <div style="background:rgba(0,0,0,.25);border-radius:12px;padding:10px 18px;text-align:center;">
                    <div style="font-size:11px;opacity:.8;margin-bottom:4px;" class="bengali-text">
                        {{ app()->getLocale()==="bn" ? "বাকি সময়" : "Ends in" }}
                    </div>
                    <div id="flashCountdown" style="font-size:22px;font-weight:800;letter-spacing:2px;font-family:monospace;">00:00:00</div>
                </div>
            </div>
            <div style="display:flex;gap:14px;overflow-x:auto;padding-bottom:18px;scrollbar-width:thin;">
                @foreach($activeFlashSale->products as $fsp)
                @if($fsp->product && $fsp->remaining > 0)
                @php
                    $discPct    = $fsp->product->price > 0
                        ? round((($fsp->product->price - $fsp->flash_price) / $fsp->product->price) * 100)
                        : 0;
                                    // Bug 1 Fixed: $MATCH → $fsp->product->primaryImage->url
                    $fspImgUrl  = $fsp->product->primaryImage?->url;
                @endphp
                <div style="min-width:180px;max-width:180px;background:#fff;border-radius:14px;overflow:hidden;flex-shrink:0;box-shadow:0 4px 20px rgba(0,0,0,.15);">
                    <div style="position:relative;height:140px;background:#f8f9fa;">
                        {{-- ✅ Bug 1 & 2 Fixed: proper null check, no broken URL fallback --}}
                        @if($fspImgUrl)
                            <img src="{{ $fspImgUrl }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image fa-2x text-muted"></i></div>
                        @endif
                        <span style="position:absolute;top:8px;left:8px;background:#dc3545;color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;">{{ $discPct }}% OFF</span>
                        <span style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,.6);color:#fff;font-size:10px;padding:2px 7px;border-radius:20px;"><i class="fas fa-fire-alt"></i> {{ $fsp->remaining }} left</span>
                    </div>
                    <div style="padding:10px 12px;">
                        <div style="font-size:12px;font-weight:600;color:#222;margin-bottom:4px;line-height:1.3;" class="bengali-text">{{ Str::limit($fsp->product->name, 40) }}</div>
                        <div style="margin-bottom:6px;"><del style="font-size:11px;color:#999;">৳{{ number_format($fsp->product->price, 0) }}</del><strong style="font-size:15px;color:#dc3545;margin-left:4px;">৳{{ number_format($fsp->flash_price, 0) }}</strong></div>
                        <div style="background:#f0f0f0;border-radius:4px;height:5px;margin-bottom:4px;overflow:hidden;">
                            <div style="background:linear-gradient(90deg,#ff6b35,#dc3545);height:100%;width:{{ $fsp->sold_percent }}%;border-radius:4px;"></div>
                        </div>
                        <div style="font-size:10px;color:#888;margin-bottom:8px;">{{ $fsp->sold_quantity }}/{{ $fsp->max_quantity }} {{ app()->getLocale()==="bn" ? "বিক্রি" : "sold" }}</div>
                        <div style="display:flex;gap:6px;">
                            <button onclick="addToCart({{ $fsp->product_id }})" style="flex:1;background:#dc3545;color:#fff;border:none;border-radius:8px;padding:7px;font-size:12px;font-weight:600;cursor:pointer;">
                                <i class="fas fa-bolt me-1"></i>{{ app()->getLocale()==="bn" ? "এখনই নিন" : "Grab Now" }}
                            </button>
                            <a href="{{ route('product.show', $fsp->product_id) }}" style="background:rgba(220,53,69,.15);color:#dc3545;border:1px solid #dc3545;border-radius:8px;padding:7px 10px;font-size:12px;display:flex;align-items:center;">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif



{{-- NEW ARRIVALS --}}
<span id="section-new-arrivals" style="display:block;margin-top:-80px;padding-top:80px;"></span>
@if($newArrivals->count())
<section style="margin:32px 0;">
    <div class="fw-wrap">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <span style="background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;padding:6px 16px;border-radius:20px;font-size:14px;font-weight:700;">✨ {{ app()->getLocale()==="bn" ? "নতুন" : "NEW" }}</span>
            <h2 class="bengali-text mb-0">{{ app()->getLocale()==="bn" ? "নতুন আগমন" : "New Arrivals" }}</h2>
        </div>
        <div class="product-grid">
            @foreach($newArrivals as $product)
            <div class="product-col">
                <div class="product-card" style="border:2px solid #e0f2fe;position:relative;">
                    <span style="position:absolute;top:10px;left:10px;background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;z-index:1;">✨ NEW</span>
                    <div class="product-image">
                        {{-- ✅ Bug 2 & 3 Fixed: primaryImage->url দিয়ে check, broken no-image fallback বাদ --}}
                        @php $imgUrl = $product->primaryImage?->url; @endphp
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" loading="lazy">
                        @else
                            <i class="fas fa-image fa-3x text-muted"></i>
                        @endif
                        @if($product->activeDiscount)
                            <span class="product-badge">{{ number_format($product->activeDiscount->discount_percentage,0) }}% OFF</span>
                        @endif
                    </div>
                    <div class="product-info">
                        <h6 class="product-name bengali-text">{{ $product->name }}</h6>
                        <div class="product-price">
                            @if($product->activeDiscount)
                                <span class="original-price-text">৳{{ number_format($product->price,0) }}</span> ৳{{ number_format($product->activeDiscount->discount_price,0) }}
                            @else {{ __('messages.price') }}: ৳{{ number_format($product->price,0) }} @endif
                        </div>
                        <div class="product-actions">
                            <button class="btn btn-success btn-sm" onclick="addToCart({{ $product->id }})"><i class="fas fa-plus"></i> {{ __('messages.add_to_cart') }}</button>
                            <a href="{{ route('product.show', $product->id) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-eye"></i> {{ __('messages.view_details') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- POPULAR PRODUCTS --}}
<span id="section-products" style="display:block;margin-top:-80px;padding-top:80px;"></span>
<section style="margin:32px 0 48px;">
    <div class="fw-wrap">
        <h2 class="bengali-text mb-4">{{ __('messages.popular_products') }}</h2>
        @if($products->isEmpty())
        <div class="empty-state"><i class="fas fa-box-open"></i><h3 class="bengali-text">{{ __('messages.no_products') }}</h3></div>
        @else
        <div class="product-grid">
            @foreach($products as $product)
            <div class="product-col">
                <div class="product-card">
                    {{-- Image wrapper with hover overlay --}}
                    <div class="product-img-wrap">
                        @php $imgUrl = $product->primaryImage?->url; @endphp
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" loading="lazy">
                        @else
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--surface-2);"><i class="fas fa-image fa-3x" style="color:var(--border);"></i></div>
                        @endif
                        
                        {{-- Badges --}}
                        @if($product->is_new_arrival)
                            <span style="position:absolute;top:9px;left:9px;background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;font-size:10px;font-weight:800;padding:4px 10px;border-radius:20px;z-index:2;box-shadow:0 2px 8px rgba(99,102,241,.4);">✨ NEW</span>
                        @endif
                        @if($product->activeDiscount)
                            <span class="product-badge" style="z-index:2;">{{ number_format($product->activeDiscount->discount_percentage,0) }}% OFF</span>
                        @endif
                    </div>
                    <div class="product-info">
                        {{-- Rating stars placeholder (visual) --}}
                        <div style="display:flex;align-items:center;gap:4px;margin-bottom:5px;">
                            <span style="color:#f59e0b;font-size:11px;">★★★★</span><span style="color:#d1d5db;font-size:11px;">★</span>
                            <span style="font-size:10px;color:var(--muted);margin-left:2px;">({{ rand(4,99) }})</span>
                        </div>
                        <h6 class="product-name bengali-text">{{ $product->name }}</h6>
                        @if($product->description)<p class="product-details-text bengali-text">{{ $product->description }}</p>@endif
                        <div class="product-price">
                            @if($product->activeDiscount)
                                <span class="original-price-text">৳{{ number_format($product->price,0) }}</span>
                                <span style="color:var(--success);font-size:19px;font-weight:800;">৳{{ number_format($product->activeDiscount->discount_price,0) }}</span>
                            @else
                                <span style="color:var(--success);font-size:18px;font-weight:800;">৳{{ number_format($product->price,0) }}</span>
                            @endif
                        </div>
                        <div class="product-actions">
                            <button class="btn btn-success btn-sm" style="flex:1;" onclick="addToCart({{ $product->id }})">
                                <i class="fas fa-cart-plus me-1"></i>{{ __('messages.add_to_cart') }}
                            </button>
                            <a href="{{ route('product.show', $product->id) }}" class="btn btn-outline-success btn-sm" style="padding:6px 10px;">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
@if($activeFlashSale)
(function() {
    const endTime = new Date('{{ $activeFlashSale->end_time->toIso8601String() }}').getTime();
    const el = document.getElementById('flashCountdown');
    if (!el) return;
    function tick() {
        const diff = endTime - Date.now();
        if (diff <= 0) { el.textContent = '00:00:00'; return; }
        const h = String(Math.floor(diff/3600000)).padStart(2,'0');
        const m = String(Math.floor((diff%3600000)/60000)).padStart(2,'0');
        const s = String(Math.floor((diff%60000)/1000)).padStart(2,'0');
        el.textContent = h+':'+m+':'+s;
    }
    tick(); setInterval(tick, 1000);
})();
@endif
</script>
@endpush
@extends('layouts.app')
@section('title', $product->name . ' — ' . __('messages.site_name'))
@section('meta_desc', Str::limit($product->description, 150))

@section('content')
<div class="fw-wrap" style="margin-top:24px;margin-bottom:48px;">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="background:transparent;padding:0;font-size:13px;">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none">{{ __('messages.home') }}</a></li>
            @if($product->category)
            <li class="breadcrumb-item"><a href="#" class="text-success text-decoration-none">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" style="color:var(--muted);">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Product Images --}}
        <div class="col-lg-5">
            <div style="background:var(--surface);border-radius:14px;padding:20px;border:1px solid var(--border);">
                {{-- Main Image --}}
                <div style="width:100%;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;background:var(--surface-2);border-radius:10px;overflow:hidden;margin-bottom:12px;" id="mainImgWrap">
                    @php
                        $allImages = $product->images;
                        $primaryImg = $product->primaryImage;
                        $mainImgUrl = $primaryImg
                            ? $primaryImg->url
                            : ($product->image_path ? $product->image_url : null);
                    @endphp

                    @if($mainImgUrl)
                        <img src="{{ $mainImgUrl }}"
                             alt="{{ $product->name }}"
                             style="width:100%;height:100%;object-fit:contain;transition:opacity .25s;"
                             id="mainProductImg">
                    @else
                        <i class="fas fa-image fa-5x text-muted"></i>
                    @endif
                </div>

                {{-- Thumbnail Strip (multiple images) --}}
                @if($allImages->count() > 1)
                <div style="display:flex;gap:8px;flex-wrap:wrap;" id="thumbStrip">
                    @foreach($allImages as $img)
                    @php $imgUrl = $img->url; @endphp
                    <div onclick="setMainImg('{{ $imgUrl }}', this)"
                         style="width:64px;height:64px;border-radius:8px;border:2px solid {{ $img->is_primary ? 'var(--primary)' : 'var(--border)' }};overflow:hidden;cursor:pointer;flex-shrink:0;transition:border-color .2s;"
                         class="thumb-item {{ $img->is_primary ? 'active-thumb' : '' }}">
                        <img src="{{ $imgUrl }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:contain;">
                    </div>
                    @endforeach
                </div>
                @elseif($product->image_url)
                <div style="display:flex;gap:8px;">
                    <div style="width:64px;height:64px;border-radius:8px;border:2px solid var(--primary);overflow:hidden;">
                        <img src="{{ $product->image_url }}" style="width:100%;height:100%;object-fit:contain;">
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Product Info --}}
        <div class="col-lg-7">
            <div style="background:var(--surface);border-radius:14px;padding:24px;border:1px solid var(--border);">
                {{-- Name --}}
                <h1 class="bengali-text" style="font-size:22px;font-weight:700;color:var(--text);margin-bottom:12px;">
                    {{ $product->name }}
                </h1>

                {{-- Price --}}
                <div style="margin-bottom:16px;">
                    @if($product->activeDiscount)
                        <span style="font-size:28px;font-weight:700;color:var(--success);">
                            ৳{{ number_format($product->activeDiscount->discount_price, 0) }}
                        </span>
                        <span style="text-decoration:line-through;color:var(--muted);font-size:18px;margin-left:10px;">
                            ৳{{ number_format($product->price, 0) }}
                        </span>
                        <span class="badge bg-danger ms-2" style="font-size:13px;">
                            {{ number_format($product->activeDiscount->discount_percentage, 0) }}% {{ __('messages.off') }}
                        </span>
                    @else
                        <span style="font-size:28px;font-weight:700;color:var(--success);">
                            ৳{{ number_format($product->price, 0) }}
                        </span>
                    @endif
                </div>

                {{-- Stock --}}
                <div style="margin-bottom:16px;">
                    @if($product->stock_quantity > 0)
                        <span style="background:#d4edda;color:#155724;padding:4px 12px;border-radius:20px;font-size:13px;font-weight:500;">
                            <i class="fas fa-check-circle me-1"></i>{{ __('messages.in_stock') }}
                            ({{ $product->stock_quantity }} {{ __('messages.left_in_stock') }})
                        </span>
                    @else
                        <span style="background:#f8d7da;color:#721c24;padding:4px 12px;border-radius:20px;font-size:13px;font-weight:500;">
                            <i class="fas fa-times-circle me-1"></i>{{ __('messages.out_of_stock') }}
                        </span>
                    @endif
                </div>

                {{-- Meta info --}}
                @if($product->sku || $product->product_type || $product->product_size)
                <div style="background:var(--surface-2);border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;">
                    @if($product->sku)
                    <div style="display:flex;gap:8px;margin-bottom:4px;">
                        <span style="color:var(--muted);min-width:60px;">{{ __('messages.sku') }}:</span>
                        <span style="color:var(--text);font-weight:500;">{{ $product->sku }}</span>
                    </div>
                    @endif
                    @if($product->product_type)
                    <div style="display:flex;gap:8px;margin-bottom:4px;">
                        <span style="color:var(--muted);min-width:60px;">{{ __('messages.type') }}:</span>
                        <span style="color:var(--text);">{{ $product->product_type }}</span>
                    </div>
                    @endif
                    @if($product->product_size)
                    <div style="display:flex;gap:8px;">
                        <span style="color:var(--muted);min-width:60px;">{{ __('messages.size') }}:</span>
                        <span style="color:var(--text);">{{ $product->product_size }}</span>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Size Selector --}}
                @if($product->product_size)
                @php
                    $sizes = array_filter(array_map('trim', explode(',', $product->product_size)));
                @endphp
                @if(count($sizes) > 0)
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;" class="bengali-text">
                        {{ app()->getLocale()==='bn' ? 'সাইজ বেছে নিন *' : 'Select Size *' }}
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;" id="sizeOptions">
                        @foreach($sizes as $size)
                        <button type="button"
                            onclick="selectSize(this, '{{ trim($size) }}')"
                            data-size="{{ trim($size) }}"
                            style="padding:6px 16px;border:1.5px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;">
                            {{ trim($size) }}
                        </button>
                        @endforeach
                    </div>
                    <div id="sizeError" style="display:none;color:#ef4444;font-size:12px;margin-top:6px;" class="bengali-text">
                        {{ app()->getLocale()==='bn' ? '⚠ সাইজ বেছে নিন' : '⚠ Please select a size' }}
                    </div>
                    <input type="hidden" id="selectedSize" value="">
                </div>
                @endif
                @endif

                {{-- Quantity + Add to Cart --}}
                <div style="display:flex;gap:12px;align-items:center;margin-bottom:16px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <button onclick="changeQty(-1)" style="width:36px;height:36px;border:1.5px solid var(--border);background:var(--bg);color:var(--text);border-radius:8px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;">−</button>
                        <input type="number" id="qtyInput" value="1" min="1" max="{{ $product->stock_quantity ?: 99 }}"
                               style="width:60px;text-align:center;border:1.5px solid var(--border);border-radius:8px;padding:6px;background:var(--bg);color:var(--text);font-size:15px;font-weight:600;">
                        <button onclick="changeQty(1)" style="width:36px;height:36px;border:1.5px solid var(--border);background:var(--bg);color:var(--text);border-radius:8px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;">+</button>
                    </div>
                    <button class="btn btn-success btn-lg flex-grow-1"
                            onclick="addToCartQty({{ $product->id }})"
                            @if($product->stock_quantity == 0) disabled @endif>
                        <i class="fas fa-cart-plus me-2"></i>{{ __('messages.add_to_cart') }}
                    </button>
                </div>

                {{-- Delivery info --}}
                <div style="background:var(--surface-2);border-radius:8px;padding:12px 14px;font-size:12.5px;color:var(--muted);">
                    <div><i class="fas fa-truck me-2" style="color:var(--primary);"></i>{{ __('messages.inside_dhaka_charge') }}</div>
                    <div class="mt-1"><i class="fas fa-shield-alt me-2" style="color:var(--primary);"></i>{{ __('messages.pay_on_delivery') }}</div>
                    <div class="mt-1"><i class="fas fa-undo me-2" style="color:var(--primary);"></i>{{ app()->getLocale()==='bn'?'৭ দিনের রিটার্ন পলিসি':'7-day return policy' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if($product->description)
    <div style="background:var(--surface);border-radius:14px;padding:24px;border:1px solid var(--border);margin-top:24px;">
        <h4 style="font-size:16px;font-weight:600;margin-bottom:14px;color:var(--text);">{{ __('messages.description') }}</h4>
        <p class="bengali-text" style="color:var(--muted);line-height:1.8;font-size:14px;">{{ $product->description }}</p>
    </div>
    @endif

    {{-- ══════════ CUSTOMER REVIEWS ══════════ --}}
    <div style="background:var(--surface);border-radius:14px;padding:24px;border:1px solid var(--border);margin-top:24px;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <h4 style="font-size:16px;font-weight:700;margin:0;color:var(--text);" class="bengali-text">
                <i class="fas fa-star me-2" style="color:#f59e0b;"></i>
                {{ app()->getLocale()==='bn' ? 'ক্রেতার রিভিউ' : 'Customer Reviews' }}
                @if($reviewCount > 0)
                    <span style="font-size:13px;color:var(--muted);font-weight:400;">({{ $reviewCount }})</span>
                @endif
            </h4>
            @if($reviewCount > 0)
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="font-size:32px;font-weight:800;color:var(--text);">{{ number_format($avgRating, 1) }}</div>
                <div>
                    <div style="color:#f59e0b;font-size:18px;">
                        @for($i=1;$i<=5;$i++)
                            @if($i <= round($avgRating))★@else☆@endif
                        @endfor
                    </div>
                    <div style="font-size:11px;color:var(--muted);">{{ $reviewCount }} {{ app()->getLocale()==='bn' ? 'টি রিভিউ' : 'reviews' }}</div>
                </div>
            </div>
            @endif
        </div>

        {{-- Rating Distribution --}}
        @if($reviewCount > 0)
        <div style="margin-bottom:24px;">
            @for($i=5;$i>=1;$i--)
            @php $cnt = $ratingDist[$i] ?? 0; $pct = $reviewCount > 0 ? round($cnt/$reviewCount*100) : 0; @endphp
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                <span style="font-size:12px;color:var(--muted);width:12px;text-align:right;">{{ $i }}</span>
                <span style="color:#f59e0b;font-size:12px;">★</span>
                <div style="flex:1;height:7px;background:var(--border);border-radius:4px;overflow:hidden;">
                    <div style="width:{{ $pct }}%;background:#f59e0b;height:100%;border-radius:4px;"></div>
                </div>
                <span style="font-size:11px;color:var(--muted);width:24px;">{{ $cnt }}</span>
            </div>
            @endfor
        </div>
        @endif



        {{-- All Reviews List --}}
        @if($product->reviews->count())
        <div style="display:flex;flex-direction:column;gap:16px;">
            @foreach($product->reviews as $review)
            <div style="border-bottom:1px solid var(--border);padding-bottom:16px;">
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    {{-- Avatar --}}
                    <div style="width:40px;height:40px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;flex-shrink:0;">
                        {{ strtoupper(substr($review->user?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;margin-bottom:4px;">
                            <div>
                                <span style="font-size:13px;font-weight:600;color:var(--text);">{{ $review->user?->name ?? 'Anonymous' }}</span>
                                {{-- Stars --}}
                                <span style="color:#f59e0b;font-size:13px;margin-left:8px;">
                                    {{ str_repeat('★', $review->rating) }}<span style="color:#d1d5db;">{{ str_repeat('★', 5 - $review->rating) }}</span>
                                </span>
                            </div>
                            <span style="font-size:11px;color:var(--muted);">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        <p style="font-size:13px;color:var(--muted);margin:0 0 8px;line-height:1.6;" class="bengali-text">{{ $review->comment }}</p>
                        {{-- Review Image --}}
                        @if($review->review_image)
                        <div style="margin-top:6px;">
                            <img src="{{ asset('storage/'.$review->review_image) }}"
                                 style="max-width:120px;border-radius:8px;border:1px solid var(--border);cursor:pointer;"
                                 onclick="window.open(this.src)"
                                 alt="Review photo">
                        </div>
                        @endif
                        {{-- Delete own review --}}
                        @auth
                        @if($review->user_id === auth()->id())
                        <form method="POST" action="{{ route('reviews.destroy', $review) }}" style="margin-top:6px;" onsubmit="return confirm('Delete this review?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none;border:none;color:#ef4444;font-size:11px;cursor:pointer;padding:0;">
                                <i class="fas fa-trash-alt me-1"></i>{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}
                            </button>
                        </form>
                        @endif
                        @endauth
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:32px 20px;color:var(--muted);">
            <i class="fas fa-star fa-2x mb-2 d-block" style="opacity:.2;color:#f59e0b;"></i>
            <p style="font-size:13px;margin:0;" class="bengali-text">
                {{ app()->getLocale()==='bn' ? 'এখনো কোনো রিভিউ নেই। প্রথম রিভিউ দিন!' : 'No reviews yet. Be the first to review!' }}
            </p>
        </div>
        @endif
    </div>

    {{-- Related Products --}}
    @if($related->count())
    <div style="margin-top:36px;">
        <h3 class="bengali-text mb-4">{{ __('messages.related_products') }}</h3>
        <div class="product-grid">
            @foreach($related as $r)
            <div class="product-col">
                <div class="product-card">
                    <div class="product-image">
                        @php
                            $rPrimary = $r->primaryImage;
                            $rImg = $rPrimary ? $rPrimary->url
                                   : ($r->image_path ? $r->image_url : null);
                        @endphp
                        @if($rImg)
                            <img src="{{ $rImg }}" alt="{{ $r->name }}" loading="lazy">
                        @else
                            <i class="fas fa-image fa-3x text-muted"></i>
                        @endif
                    </div>
                    <div class="product-info">
                        <h6 class="product-name bengali-text">{{ $r->name }}</h6>
                        <div class="product-price">৳{{ number_format($r->price, 0) }}</div>
                        <div class="product-actions">
                            <button class="btn btn-success btn-sm" onclick="addToCart({{ $r->id }})">
                                <i class="fas fa-plus"></i> {{ __('messages.add_to_cart') }}
                            </button>
                            <a href="{{ route('product.show', $r->id) }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.thumb-item:hover { border-color: var(--primary) !important; }
.active-thumb { border-color: var(--primary) !important; }
</style>
@endpush

@push('scripts')
<script>
function changeQty(dir){
    const inp = document.getElementById('qtyInput');
    const max = parseInt(inp.max) || 99;
    let val = parseInt(inp.value) + dir;
    if(val < 1) val = 1;
    if(val > max) val = max;
    inp.value = val;
}
function selectSize(btn, size) {
    document.querySelectorAll('#sizeOptions button').forEach(b => {
        b.style.background = 'var(--bg)';
        b.style.color = 'var(--text)';
        b.style.borderColor = 'var(--border)';
    });
    btn.style.background = 'var(--primary)';
    btn.style.color = '#fff';
    btn.style.borderColor = 'var(--primary)';
    const inp = document.getElementById('selectedSize');
    if (inp) inp.value = size;
    const err = document.getElementById('sizeError');
    if (err) err.style.display = 'none';
}
function addToCartQty(productId){
    const qty = parseInt(document.getElementById('qtyInput').value) || 1;
    const sizeInput = document.getElementById('selectedSize');
    const sizeOptions = document.getElementById('sizeOptions');
    // If product has sizes, require selection
    if (sizeOptions && (!sizeInput || !sizeInput.value)) {
        const err = document.getElementById('sizeError');
        if (err) err.style.display = 'block';
        return;
    }
    const selectedSize = sizeInput ? sizeInput.value : null;

    // Save pending state BEFORE the request (in case 401 fires)
    localStorage.setItem('pendingCartItem', JSON.stringify({product_id:productId, quantity:qty, selected_size:selectedSize||null}));

    fetch('{{ route("cart.add") }}',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
        body:JSON.stringify({product_id:productId, quantity:qty, selected_size: selectedSize || undefined})
    }).then(r=>{
        if(r.status === 401 || r.status === 419){
            // Guest — pending already saved, open login
            openLoginModal('login');
            toast('{{ app()->getLocale()==="bn" ? "পণ্য যোগ করতে লগইন করুন" : "Please login to add to cart" }}','error');
            return null;
        }
        localStorage.removeItem('pendingCartItem'); // logged in, no need to keep
        return r.json();
    }).then(d=>{
        if(!d) return;
        if(d.success){
            document.getElementById('cartBadge').textContent=d.cart_count;
            toast(d.message||'{{ __("messages.added_to_cart") }}','success');
        } else { toast(d.message||'Error','error'); }
    });
}
function setMainImg(src, el){
    const img = document.getElementById('mainProductImg');
    if(img){ img.style.opacity='0'; setTimeout(()=>{img.src=src;img.style.opacity='1';},200); }
    document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active-thumb'));
    el.classList.add('active-thumb');
}

// Review form removed from product page — submit from Dashboard
</script>
@endpush
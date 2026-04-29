@extends('layouts.app')
@section('title',
    $currentCategory
        ? $currentCategory->name . ' — ' . __('messages.site_name')
        : __('messages.search_results') . ($query ? ': ' . $query : '') . ' — ' . __('messages.site_name')
)

@section('content')
<div class="fw-wrap" style="margin-top:24px;margin-bottom:48px;">

    {{-- Search bar + filters --}}
    <div style="background:var(--surface);border-radius:14px;padding:20px 24px;margin-bottom:24px;border:1px solid var(--border);">
        <form action="{{ route('search') }}" method="GET">
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <input type="text" name="q" class="form-control"
                       value="{{ $query }}"
                       placeholder="{{ __('messages.search_placeholder') }}"
                       style="flex:1;min-width:200px;border-radius:10px;border:1.5px solid var(--border);background:var(--bg);color:var(--text);padding:10px 16px;">
                {{-- Category dropdown --}}
                <select name="category" class="form-select"
                        style="width:auto;min-width:160px;border-radius:10px;border:1.5px solid var(--border);background:var(--bg);color:var(--text);"
                        onchange="this.form.submit()">
                    <option value="">{{ app()->getLocale()==='bn' ? 'সব ক্যাটাগরি' : 'All Categories' }}</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                        {{ app()->getLocale()==='bn' ? $cat->name_bn : $cat->name_en }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-success" style="padding:10px 22px;border-radius:10px;font-weight:600;white-space:nowrap;">
                    <i class="fas fa-search me-1"></i>{{ app()->getLocale()==='bn' ? 'খুঁজুন' : 'Search' }}
                </button>
            </div>
        </form>

        {{-- Result count / active filter label --}}
        <div style="margin-top:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            @if($currentCategory)
            <span style="background:var(--primary);color:#fff;padding:4px 14px;border-radius:20px;font-size:13px;font-weight:600;" class="bengali-text">
                <i class="fas fa-tag me-1"></i>{{ app()->getLocale()==='bn' ? $currentCategory->name_bn : $currentCategory->name_en }}
                <a href="{{ route('search', ['q' => $query]) }}" style="color:rgba(255,255,255,.7);margin-left:6px;text-decoration:none;" title="Clear">×</a>
            </span>
            @endif
            @if($query)
            <span style="font-size:13px;color:var(--muted);" class="bengali-text">
                "<strong style="color:var(--text);">{{ $query }}</strong>" —
            </span>
            @endif
            <span style="font-size:13px;color:var(--muted);" class="bengali-text">
                {{ $products->total() }} {{ app()->getLocale()==='bn' ? 'টি পণ্য পাওয়া গেছে' : 'products found' }}
            </span>
        </div>
    </div>

    {{-- Category Quick Filters --}}
    @if(!$currentCategory && $categories->count())
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
        @foreach($categories as $cat)
        <a href="{{ route('search', ['category' => $cat->id, 'q' => $query]) }}"
           style="display:flex;align-items:center;gap:7px;padding:7px 14px;border-radius:20px;background:var(--surface);border:1.5px solid var(--border);text-decoration:none;font-size:13px;color:var(--text);transition:all .2s;white-space:nowrap;"
           class="bengali-text">
            <i class="{{ $cat->icon ?? 'fas fa-tag' }}" style="color:{{ $cat->color ?? 'var(--primary)' }};font-size:12px;"></i>
            {{ app()->getLocale()==='bn' ? $cat->name_bn : $cat->name_en }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Empty / no query --}}
    @if(!$query && !$categoryId)
    <div style="text-align:center;padding:60px 20px;color:var(--muted);">
        <i class="fas fa-search" style="font-size:56px;margin-bottom:16px;display:block;opacity:.25;"></i>
        <h3 class="bengali-text" style="font-size:18px;margin-bottom:8px;">{{ app()->getLocale()==='bn' ? 'কিছু খুঁজুন' : 'Search for something' }}</h3>
        <p class="bengali-text" style="font-size:14px;">{{ __('messages.search_placeholder') }}</p>
    </div>

    @elseif($products->isEmpty())
    <div style="text-align:center;padding:60px 20px;color:var(--muted);">
        <i class="fas fa-box-open" style="font-size:56px;margin-bottom:16px;display:block;opacity:.25;"></i>
        <h3 class="bengali-text" style="font-size:18px;margin-bottom:8px;">{{ __('messages.no_results') }}</h3>
        <p class="text-muted bengali-text" style="font-size:14px;">
            {{ $query ? '"'.$query.'" — ' : '' }}{{ __('messages.try_different') }}
        </p>
        <a href="{{ route('home') }}" class="btn btn-success mt-3">
            <i class="fas fa-home me-1"></i>{{ __('messages.home') }}
        </a>
    </div>

    @else
    {{-- Product Grid --}}
    <div class="product-grid">
        @foreach($products as $product)
        <div class="product-col">
            <div class="product-card">
                <a href="{{ route('product.show', $product->id) }}" style="display:block;text-decoration:none;">
                    <div class="product-image">
                        @php
                            $img = $product->primaryImage
                                ? $product->image_url
                                : ($product->image_url ?: null);
                        @endphp
                        @if($img)
                            <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">
                        @else
                            <i class="fas fa-image fa-3x text-muted"></i>
                        @endif
                        @if($product->activeDiscount)
                            <span class="product-badge">
                                {{ number_format($product->activeDiscount->discount_percentage, 0) }}% OFF
                            </span>
                        @endif
                    </div>
                </a>
                <div class="product-info">
                    <h6 class="product-name bengali-text">{{ $product->name }}</h6>
                    @if($product->description)
                    <p class="product-details-text bengali-text">{{ $product->description }}</p>
                    @endif
                    <div class="product-price">
                        @if($product->activeDiscount)
                            <span style="color:var(--success);">৳{{ number_format($product->activeDiscount->discount_price, 0) }}</span>
                            <span style="text-decoration:line-through;color:var(--muted);font-size:13px;margin-left:5px;">৳{{ number_format($product->price, 0) }}</span>
                        @else
                            ৳{{ number_format($product->price, 0) }}
                        @endif
                    </div>
                    <div class="product-actions">
                        <button class="btn btn-success btn-sm" onclick="addToCart({{ $product->id }})">
                            <i class="fas fa-plus"></i> {{ __('messages.add_to_cart') }}
                        </button>
                        <a href="{{ route('product.show', $product->id) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="d-flex justify-content-center mt-5">
        {{ $products->appends(['q' => $query, 'category' => $categoryId])->links() }}
    </div>
    @endif
    @endif

</div>
@endsection

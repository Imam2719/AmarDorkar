@extends('layouts.app')
@section('title',
    $currentCategory
        ? $currentCategory->name . ' — ' . __('messages.site_name')
        : __('messages.search_results') . ($query ? ': ' . $query : '') . ' — ' . __('messages.site_name')
)

@push('styles')
<style>
@keyframes is-spin { to { transform:rotate(360deg) } }

/* ── Layout ──────────────────────────────────────────────────────────── */
.search-layout{display:flex;gap:20px;align-items:flex-start;margin-top:24px;margin-bottom:48px;}
.filter-sidebar{width:260px;flex-shrink:0;position:sticky;top:130px;}
.search-main{flex:1;min-width:0;}

@media(max-width:900px){
  .filter-sidebar{display:none;}
  .filter-sidebar.mobile-open{
    display:block;
    position:fixed;
    top:var(--navbar-height,60px);
    bottom:0;left:0;
    z-index:1100;
    overflow-y:auto;
    width:285px;
    padding:0;
    background:var(--surface);
    box-shadow:4px 0 24px rgba(0,0,0,.2);
    transform:translateX(-100%);
    transition:transform .28s cubic-bezier(.4,0,.2,1);
  }
  .filter-sidebar.mobile-open.sidebar-visible{transform:translateX(0);}
}

/* ── Filter card ─────────────────────────────────────────────────────── */
.filter-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px;}
@media(max-width:900px){.filter-card{border-radius:0;border:none;padding:16px;}}
.filter-section{margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border);}
.filter-section:last-child{margin-bottom:0;padding-bottom:0;border-bottom:none;}
.filter-title{font-size:13px;font-weight:700;color:var(--text);margin-bottom:12px;display:flex;align-items:center;gap:7px;}
.filter-title i{color:var(--primary);font-size:12px;}

/* ── Mobile filter header ─────────────────────────────────────────────── */
.mobile-filter-header{
  display:none;align-items:center;justify-content:space-between;
  padding:14px 16px;border-bottom:1px solid var(--border);
  background:var(--surface);position:sticky;top:0;z-index:10;
}
@media(max-width:900px){.mobile-filter-header{display:flex;}}
.mobile-filter-header-title{font-weight:700;font-size:15px;color:var(--text);display:flex;align-items:center;gap:8px;}
.mobile-filter-header-title i{color:var(--primary);}
.mobile-filter-close-btn{
  display:flex;align-items:center;justify-content:center;
  width:34px;height:34px;background:var(--surface-2,rgba(0,0,0,.06));
  border:none;border-radius:50%;font-size:20px;color:var(--text);
  cursor:pointer;transition:background .15s;line-height:1;
}
.mobile-filter-close-btn:hover{background:var(--border);}

/* ── Price slider ────────────────────────────────────────────────────── */
.price-range-wrap{position:relative;height:6px;background:var(--border);border-radius:3px;margin:16px 6px 20px;}
.price-range-track{position:absolute;height:100%;background:var(--primary);border-radius:3px;}
.range-input{position:absolute;width:100%;height:6px;background:transparent;appearance:none;-webkit-appearance:none;pointer-events:none;top:0;left:0;}
.range-input::-webkit-slider-thumb{width:18px;height:18px;border-radius:50%;background:var(--primary);border:2px solid var(--surface);box-shadow:0 2px 6px rgba(0,0,0,.25);appearance:none;-webkit-appearance:none;pointer-events:all;cursor:pointer;}
.range-input::-moz-range-thumb{width:18px;height:18px;border-radius:50%;background:var(--primary);border:2px solid var(--surface);box-shadow:0 2px 6px rgba(0,0,0,.25);pointer-events:all;cursor:pointer;}
.price-labels{display:flex;justify-content:space-between;font-size:12px;font-weight:600;color:var(--primary);}

/* ── Rating option ───────────────────────────────────────────────────── */
.rating-option{display:flex;align-items:center;gap:8px;padding:5px 8px;border-radius:8px;cursor:pointer;transition:background .15s;font-size:13px;color:var(--text);}
.rating-option:hover{background:var(--primary-light);}
.rating-option input{accent-color:var(--primary);}
.stars-display{color:#f59e0b;font-size:12px;letter-spacing:1px;}

/* ── Sort bar ────────────────────────────────────────────────────────── */
.sort-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;background:var(--surface);border:1px solid var(--border);border-radius:12px;margin-bottom:16px;flex-wrap:wrap;}
.sort-select{border:1.5px solid var(--border);border-radius:8px;padding:6px 12px;font-size:13px;background:var(--bg);color:var(--text);cursor:pointer;transition:border-color .2s;}
.sort-select:focus{outline:none;border-color:var(--primary);}
.result-count{font-size:13px;color:var(--muted);}

/* ── Active filter chips ─────────────────────────────────────────────── */
.active-filters{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:14px;}
.filter-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;background:var(--primary-light);color:var(--primary);font-size:12px;font-weight:600;text-decoration:none;}
.filter-chip .chip-remove{cursor:pointer;font-size:11px;opacity:.7;transition:opacity .15s;}
.filter-chip .chip-remove:hover{opacity:1;}

/* ── Mobile filter toggle button ─────────────────────────────────────── */
.mobile-filter-btn{display:none;align-items:center;gap:7px;padding:8px 16px;background:var(--surface);border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-weight:600;color:var(--text);cursor:pointer;}
@media(max-width:900px){.mobile-filter-btn{display:flex;}}

/* ── Autocomplete ────────────────────────────────────────────────────── */
.autocomplete-wrap{position:relative;}
.autocomplete-dropdown{position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--surface);border:1.5px solid var(--border);border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.15);z-index:500;overflow:hidden;display:none;}
.autocomplete-dropdown.show{display:block;}
.ac-section-title{font-size:11px;font-weight:700;color:var(--muted);padding:8px 14px 4px;text-transform:uppercase;letter-spacing:.5px;}
.ac-item{display:flex;align-items:center;gap:10px;padding:8px 14px;cursor:pointer;transition:background .15s;text-decoration:none;color:var(--text);}
.ac-item:hover{background:var(--primary-light);}
.ac-item-img{width:38px;height:38px;object-fit:cover;border-radius:7px;background:var(--surface-2);flex-shrink:0;}
.ac-item-name{font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;}
.ac-item-price{font-size:12px;font-weight:700;color:var(--primary);flex-shrink:0;}
.ac-cat-icon{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}
.ac-view-all{display:block;text-align:center;padding:10px;font-size:12px;font-weight:600;color:var(--primary);border-top:1px solid var(--border);cursor:pointer;transition:background .15s;}
.ac-view-all:hover{background:var(--primary-light);}

/* ── Mobile overlay ──────────────────────────────────────────────────── */
.filter-overlay{display:none;position:fixed;top:var(--navbar-height,60px);left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:1099;}
.filter-overlay.show{display:block;}

/* ── Infinite scroll states ──────────────────────────────────────────── */
#is-scroll-loader{text-align:center;padding:24px 0;display:none;}
#is-end-marker{text-align:center;padding:16px 0;font-size:13px;color:var(--muted);display:none;}
</style>
@endpush

@section('content')
@php $locale = app()->getLocale(); $isBn = $locale === 'bn'; @endphp

<div class="fw-wrap">
<div class="search-layout">

    {{-- ══ FILTER SIDEBAR ══════════════════════════════════════════════ --}}
    <div class="filter-sidebar" id="filterSidebar">

        {{-- Mobile header --}}
        <div class="mobile-filter-header">
            <span class="mobile-filter-header-title">
                <i class="fas fa-sliders-h"></i>
                {{ $isBn ? 'ফিল্টার' : 'Filters' }}
            </span>
            <button class="mobile-filter-close-btn" onclick="closeMobileFilter()" type="button" aria-label="Close filters">×</button>
        </div>

        <form method="GET" action="{{ route('search') }}" id="filterForm" style="padding:0 0 16px;">
            <input type="hidden" name="q"    value="{{ $query }}">
            <input type="hidden" name="sort" value="{{ $sortBy }}">

            <div class="filter-card">

                {{-- Desktop header + reset --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;" class="d-none d-md-flex">
                    <span style="font-weight:700;font-size:15px;color:var(--text);">
                        <i class="fas fa-sliders-h" style="color:var(--primary);margin-right:6px;"></i>
                        {{ $isBn ? 'ফিল্টার' : 'Filters' }}
                    </span>
                    @if($priceMin || $priceMax || $categoryId || $productType || $minRating || $inStock)
                    <a href="{{ route('search', ['q' => $query]) }}" style="font-size:12px;color:var(--danger);text-decoration:none;font-weight:600;">
                        {{ $isBn ? 'রিসেট' : 'Reset' }}
                    </a>
                    @endif
                </div>

                {{-- Mobile reset --}}
                @if($priceMin || $priceMax || $categoryId || $productType || $minRating || $inStock)
                <div style="margin-bottom:12px;" class="d-flex d-md-none">
                    <a href="{{ route('search', ['q' => $query]) }}" style="font-size:12px;color:var(--danger);text-decoration:none;font-weight:600;">
                        <i class="fas fa-redo" style="font-size:10px;margin-right:4px;"></i>{{ $isBn ? 'সব ফিল্টার রিসেট' : 'Reset All Filters' }}
                    </a>
                </div>
                @endif

                {{-- Category ──────────────────────────────────────────── --}}
                <div class="filter-section">
                    <div class="filter-title"><i class="fas fa-tag"></i>{{ $isBn ? 'ক্যাটাগরি' : 'Category' }}</div>
                    <div style="display:flex;flex-direction:column;gap:4px;max-height:200px;overflow-y:auto;">
                        <label class="rating-option" style="font-weight:{{ !$categoryId ? '700' : '400' }}">
                            <input type="radio" name="category" value=""
                                {{ !$categoryId ? 'checked' : '' }}
                                onchange="document.getElementById('filterForm').submit()">
                            {{ $isBn ? 'সব ক্যাটাগরি' : 'All Categories' }}
                        </label>
                        @foreach($categories as $cat)
                        <label class="rating-option" style="font-weight:{{ $categoryId == $cat->id ? '700' : '400' }}">
                            <input type="radio" name="category" value="{{ $cat->id }}"
                                {{ $categoryId == $cat->id ? 'checked' : '' }}
                                onchange="document.getElementById('filterForm').submit()">
                            <i class="{{ $cat->icon ?? 'fas fa-tag' }}" style="color:{{ $cat->color ?? 'var(--primary)' }};font-size:11px;"></i>
                            {{ $isBn ? $cat->name_bn : $cat->name_en }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Price Range ───────────────────────────────────────── --}}
                <div class="filter-section">
                    <div class="filter-title"><i class="fas fa-money-bill-wave"></i>{{ $isBn ? 'মূল্য পরিসীমা' : 'Price Range' }}</div>
                    <div class="price-labels">
                        <span>৳<span id="priceMinLabel">{{ $priceMin ?? $globalMin }}</span></span>
                        <span>৳<span id="priceMaxLabel">{{ $priceMax ?? $globalMax }}</span></span>
                    </div>
                    <div class="price-range-wrap" id="priceTrackWrap">
                        <div class="price-range-track" id="priceTrack"></div>
                        <input type="range" class="range-input" id="rangeMin"
                               min="{{ $globalMin }}" max="{{ $globalMax }}"
                               value="{{ $priceMin ?? $globalMin }}"
                               oninput="updateRange()">
                        <input type="range" class="range-input" id="rangeMax"
                               min="{{ $globalMin }}" max="{{ $globalMax }}"
                               value="{{ $priceMax ?? $globalMax }}"
                               oninput="updateRange()">
                    </div>
                    <input type="hidden" name="price_min" id="hiddenPriceMin" value="{{ $priceMin }}">
                    <input type="hidden" name="price_max" id="hiddenPriceMax" value="{{ $priceMax }}">
                    <button type="submit" class="btn btn-success btn-sm w-100" style="border-radius:8px;font-size:12px;margin-top:4px;">
                        {{ $isBn ? 'প্রয়োগ করুন' : 'Apply' }}
                    </button>
                </div>

                {{-- Product Type / Brand ──────────────────────────────── --}}
                @if($productTypes->count())
                <div class="filter-section">
                    <div class="filter-title"><i class="fas fa-layer-group"></i>{{ $isBn ? 'ধরন / ব্র্যান্ড' : 'Type / Brand' }}</div>
                    <div style="display:flex;flex-direction:column;gap:4px;max-height:150px;overflow-y:auto;">
                        <label class="rating-option" style="font-weight:{{ !$productType ? '700' : '400' }}">
                            <input type="radio" name="product_type" value=""
                                {{ !$productType ? 'checked' : '' }}
                                onchange="document.getElementById('filterForm').submit()">
                            {{ $isBn ? 'সব ধরন' : 'All Types' }}
                        </label>
                        @foreach($productTypes as $type)
                        <label class="rating-option" style="font-weight:{{ $productType == $type ? '700' : '400' }}">
                            <input type="radio" name="product_type" value="{{ $type }}"
                                {{ $productType == $type ? 'checked' : '' }}
                                onchange="document.getElementById('filterForm').submit()">
                            {{ $type }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Rating ───────────────────────────────────────────── --}}
                <div class="filter-section">
                    <div class="filter-title"><i class="fas fa-star"></i>{{ $isBn ? 'রেটিং' : 'Rating' }}</div>
                    <div style="display:flex;flex-direction:column;gap:3px;">
                        @foreach([4,3,2,1] as $r)
                        <label class="rating-option">
                            <input type="radio" name="rating" value="{{ $r }}"
                                {{ $minRating == $r ? 'checked' : '' }}
                                onchange="document.getElementById('filterForm').submit()">
                            <span class="stars-display">{{ str_repeat('★',$r) }}{{ str_repeat('☆',5-$r) }}</span>
                            <span style="font-size:12px;color:var(--muted);">{{ $isBn ? '& উপরে' : '& up' }}</span>
                        </label>
                        @endforeach
                        @if($minRating)
                        <label class="rating-option">
                            <input type="radio" name="rating" value="" onchange="document.getElementById('filterForm').submit()">
                            <span style="font-size:12px;color:var(--danger);">{{ $isBn ? 'রেটিং ফিল্টার বাদ দিন' : 'Clear rating filter' }}</span>
                        </label>
                        @endif
                    </div>
                </div>

                {{-- In-Stock ──────────────────────────────────────────── --}}
                <div class="filter-section">
                    <div class="filter-title"><i class="fas fa-box"></i>{{ $isBn ? 'স্টক' : 'Stock' }}</div>
                    <label class="rating-option" style="cursor:pointer;">
                        <input type="checkbox" name="in_stock" value="1"
                            {{ $inStock ? 'checked' : '' }}
                            onchange="document.getElementById('filterForm').submit()"
                            style="accent-color:var(--primary);width:15px;height:15px;">
                        {{ $isBn ? 'শুধু স্টকে আছে' : 'In Stock Only' }}
                    </label>
                </div>

            </div>
        </form>
    </div>{{-- /filter-sidebar --}}

    {{-- ══ MAIN CONTENT ════════════════════════════════════════════════ --}}
    <div class="search-main">

        {{-- Search bar + autocomplete ──────────────────────────────── --}}
        <div style="background:var(--surface);border-radius:14px;padding:18px 20px;margin-bottom:16px;border:1px solid var(--border);">
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">

                {{-- Mobile filter button --}}
                <button class="mobile-filter-btn" onclick="openMobileFilter()" type="button">
                    <i class="fas fa-sliders-h"></i> {{ $isBn ? 'ফিল্টার' : 'Filters' }}
                    @if($priceMin || $priceMax || $categoryId || $productType || $minRating || $inStock)
                    <span style="background:var(--primary);color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;">✓</span>
                    @endif
                </button>

                {{-- Autocomplete search input --}}
                <div class="autocomplete-wrap" style="flex:1;min-width:200px;">
                    <div style="position:relative;">
                        <input type="text" id="searchInputMain" class="form-control"
                               value="{{ $query }}"
                               placeholder="{{ __('messages.search_placeholder') }}"
                               autocomplete="off"
                               style="border-radius:10px;border:1.5px solid var(--border);background:var(--bg);color:var(--text);padding:10px 44px 10px 16px;font-size:14px;width:100%;"
                               oninput="onSearchInput(this.value)"
                               onkeydown="onSearchKeyDown(event)"
                               onfocus="if(this.value.length>=2) showDropdown()">
                        <button onclick="doSearch()" type="button"
                                style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:34px;height:34px;background:var(--primary);border:none;border-radius:8px;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-search" style="font-size:13px;"></i>
                        </button>
                    </div>
                    <div class="autocomplete-dropdown" id="acDropdown">
                        <div id="acContent"></div>
                        <a class="ac-view-all" id="acViewAll" onclick="doSearch()">
                            {{ $isBn ? 'সব ফলাফল দেখুন →' : 'View all results →' }}
                        </a>
                    </div>
                </div>

                {{-- Sort dropdown --}}
                <select class="sort-select" id="sortSelectTop" onchange="applySort(this.value)" style="min-width:170px;">
                    <option value="newest"    {{ $sortBy=='newest'    ? 'selected':'' }}>{{ $isBn ? '🕐 নতুন প্রথমে'      : '🕐 Newest First'       }}</option>
                    <option value="price_low" {{ $sortBy=='price_low' ? 'selected':'' }}>{{ $isBn ? '💲 দাম: কম → বেশি'   : '💲 Price: Low → High'  }}</option>
                    <option value="price_high"{{ $sortBy=='price_high'? 'selected':'' }}>{{ $isBn ? '💲 দাম: বেশি → কম'   : '💲 Price: High → Low'  }}</option>
                    <option value="rating"    {{ $sortBy=='rating'    ? 'selected':'' }}>{{ $isBn ? '⭐ সেরা রেটিং'       : '⭐ Best Rated'          }}</option>
                    <option value="popular"   {{ $sortBy=='popular'   ? 'selected':'' }}>{{ $isBn ? '🔥 সবচেয়ে জনপ্রিয়' : '🔥 Most Popular'        }}</option>
                </select>
            </div>
        </div>

        {{-- Active filter chips ────────────────────────────────────── --}}
        @php $hasFilters = $priceMin || $priceMax || $categoryId || $productType || $minRating || $inStock; @endphp
        @if($hasFilters)
        <div class="active-filters">
            @if($categoryId && $currentCategory)
            <a href="{{ route('search', array_merge(request()->except('category'), ['q' => $query])) }}" class="filter-chip">
                <i class="fas fa-tag" style="font-size:10px;"></i>
                {{ $isBn ? $currentCategory->name_bn : $currentCategory->name_en }}
                <span class="chip-remove">×</span>
            </a>
            @endif
            @if($priceMin || $priceMax)
            <a href="{{ route('search', array_merge(request()->except(['price_min','price_max']), ['q' => $query])) }}" class="filter-chip">
                ৳{{ $priceMin ?? $globalMin }} — ৳{{ $priceMax ?? $globalMax }}
                <span class="chip-remove">×</span>
            </a>
            @endif
            @if($productType)
            <a href="{{ route('search', array_merge(request()->except('product_type'), ['q' => $query])) }}" class="filter-chip">
                {{ $productType }} <span class="chip-remove">×</span>
            </a>
            @endif
            @if($minRating)
            <a href="{{ route('search', array_merge(request()->except('rating'), ['q' => $query])) }}" class="filter-chip">
                {{ str_repeat('★', $minRating) }}+ <span class="chip-remove">×</span>
            </a>
            @endif
            @if($inStock)
            <a href="{{ route('search', array_merge(request()->except('in_stock'), ['q' => $query])) }}" class="filter-chip">
                {{ $isBn ? 'স্টকে আছে' : 'In Stock' }} <span class="chip-remove">×</span>
            </a>
            @endif
        </div>
        @endif

        {{-- Result count + sort label ──────────────────────────────── --}}
        <div class="sort-bar">
            <span class="result-count bengali-text">
                @if($query)
                    "<strong style="color:var(--text);">{{ $query }}</strong>" —
                @endif
                <strong>{{ $total }}</strong>
                {{ $isBn ? 'টি পণ্য পাওয়া গেছে' : 'products found' }}
            </span>
            <span style="font-size:12px;color:var(--muted);">
                @php
                $sortLabels = [
                    'newest'     => $isBn ? 'নতুন প্রথমে'      : 'Newest First',
                    'price_low'  => $isBn ? 'দাম: কম → বেশি'   : 'Price: Low → High',
                    'price_high' => $isBn ? 'দাম: বেশি → কম'   : 'Price: High → Low',
                    'rating'     => $isBn ? 'সেরা রেটিং'       : 'Best Rated',
                    'popular'    => $isBn ? 'সবচেয়ে জনপ্রিয়' : 'Most Popular',
                ];
                @endphp
                {{ $isBn ? 'সাজানো:' : 'Sort:' }} <strong>{{ $sortLabels[$sortBy] ?? '' }}</strong>
            </span>
        </div>

        {{-- ── Empty state (no search yet) ───────────────────────────── --}}
        @if(!$query && !$categoryId && !$hasFilters)
        <div style="text-align:center;padding:60px 20px;color:var(--muted);">
            <i class="fas fa-search" style="font-size:56px;margin-bottom:16px;display:block;opacity:.25;"></i>
            <h3 class="bengali-text" style="font-size:18px;margin-bottom:8px;">{{ $isBn ? 'কিছু খুঁজুন' : 'Search for something' }}</h3>
            <p class="bengali-text" style="font-size:14px;">{{ __('messages.search_placeholder') }}</p>
            @if($categories->count())
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:24px;justify-content:center;">
                @foreach($categories as $cat)
                <a href="{{ route('search', ['category' => $cat->id]) }}"
                   style="display:inline-flex;align-items:center;gap:7px;padding:7px 14px;border-radius:20px;background:var(--surface);border:1.5px solid var(--border);text-decoration:none;font-size:13px;color:var(--text);transition:all .2s;"
                   class="bengali-text">
                    <i class="{{ $cat->icon ?? 'fas fa-tag' }}" style="color:{{ $cat->color ?? 'var(--primary)' }};font-size:12px;"></i>
                    {{ $isBn ? $cat->name_bn : $cat->name_en }}
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ── No results ─────────────────────────────────────────────── --}}
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

        {{-- ── Product grid ────────────────────────────────────────────── --}}
        @else
        <div class="product-grid" id="productGrid">
            @foreach($products as $product)
            <div class="product-col">
                <div class="product-card">
                    <a href="{{ route('product.show', $product->id) }}" style="display:block;text-decoration:none;">
                        <div class="product-image">
                            @php $img = $product->image_url; @endphp
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
                            @if($product->stock_quantity <= 0)
                            <span style="position:absolute;bottom:8px;left:8px;background:rgba(0,0,0,.7);color:#fff;padding:3px 9px;border-radius:10px;font-size:11px;">
                                {{ $isBn ? 'স্টকে নেই' : 'Out of Stock' }}
                            </span>
                            @endif
                        </div>
                    </a>
                    <div class="product-info">
                        <h6 class="product-name bengali-text">{{ $product->name }}</h6>
                        @if($product->avg_rating > 0)
                        <div style="display:flex;align-items:center;gap:4px;margin-bottom:5px;">
                            @php $stars = round($product->avg_rating); @endphp
                            <span style="color:#f59e0b;font-size:11px;">
                                @for($i=1;$i<=5;$i++){{ $i<=$stars ? '★' : '☆' }}@endfor
                            </span>
                            <span style="font-size:11px;color:var(--muted);">({{ $product->review_count }})</span>
                        </div>
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
                            <button class="btn btn-success btn-sm" onclick="addToCart({{ $product->id }})"
                                {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
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

        {{-- Infinite scroll loader --}}
        <div id="is-scroll-loader">
            <span style="display:inline-block;width:28px;height:28px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:is-spin .7s linear infinite;vertical-align:middle;"></span>
            <span style="margin-left:10px;font-size:13px;color:var(--muted);" class="bengali-text">
                {{ $isBn ? 'আরও পণ্য লোড হচ্ছে...' : 'Loading more products...' }}
            </span>
        </div>

        {{-- All loaded marker --}}
        <div id="is-end-marker">
            <i class="fas fa-check-circle" style="color:var(--primary);margin-right:6px;"></i>
            <span class="bengali-text">{{ $isBn ? 'সব পণ্য দেখা হয়েছে' : 'All products loaded' }}</span>
        </div>

        {{-- Sentinel (IntersectionObserver target) --}}
        <div id="is-sentinel" style="height:1px;"></div>
        @endif

    </div>{{-- /search-main --}}
</div>{{-- /search-layout --}}
</div>{{-- /fw-wrap --}}

{{-- Mobile filter overlay --}}
<div class="filter-overlay" id="filterOverlay" onclick="closeMobileFilter()"></div>

@push('scripts')
<script>
// ─── Config ──────────────────────────────────────────────────────────────────
const AUTOCOMPLETE_URL = "{{ route('search.autocomplete') }}";
const SEARCH_URL       = "{{ route('search') }}";
const IS_BN            = {{ $isBn ? 'true' : 'false' }};
const GLOBAL_MIN       = {{ $globalMin }};
const GLOBAL_MAX       = {{ $globalMax }};

// ─── Infinite Scroll ──────────────────────────────────────────────────────────
(function initInfiniteScroll() {
    let page     = 2;                      // first page already rendered server-side
    let hasMore  = {{ $hasMore ? 'true' : 'false' }};
    let loading  = false;

    const grid    = document.getElementById('productGrid');
    const loader  = document.getElementById('is-scroll-loader');
    const endMark = document.getElementById('is-end-marker');
    const sentinel= document.getElementById('is-sentinel');

    // Nothing to observe if grid doesn't exist (empty/no-query state)
    if (!grid || !sentinel) return;

    // Show end marker immediately if no more pages
    if (!hasMore) { endMark.style.display = 'block'; return; }

    async function loadMore() {
        if (loading || !hasMore) return;
        loading = true;
        loader.style.display = 'block';

        try {
            const params = new URLSearchParams(window.location.search);
            params.set('page', page);

            const res  = await fetch(`${SEARCH_URL}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (data.products && data.products.length) {
                data.products.forEach(p => {
                    const col  = document.createElement('div');
                    col.className = 'product-col';

                    const inStockLabel    = IS_BN ? 'স্টকে নেই'       : 'Out of Stock';
                    const addCartLabel    = IS_BN ? 'কার্টে যোগ করুন' : 'Add to Cart';
                    const outStockLabel   = IS_BN ? 'স্টকে নেই'       : 'Out of Stock';

                    const badgeHtml  = p.discount_pct
                        ? `<span class="product-badge">${p.discount_pct}% OFF</span>`
                        : '';
                    const imgHtml    = p.img
                        ? `<img src="${p.img}" alt="${p.name}" loading="lazy">`
                        : `<i class="fas fa-image fa-3x text-muted"></i>`;
                    const stockBadge = !p.in_stock
                        ? `<span style="position:absolute;bottom:8px;left:8px;background:rgba(0,0,0,.7);color:#fff;padding:3px 9px;border-radius:10px;font-size:11px;">${inStockLabel}</span>`
                        : '';
                    const priceHtml  = p.has_discount
                        ? `<span style="color:var(--success);">৳${p.effective_price}</span>
                           <span style="text-decoration:line-through;color:var(--muted);font-size:13px;margin-left:5px;">৳${p.price}</span>`
                        : `৳${p.price}`;
                    const btnLabel   = p.in_stock ? addCartLabel : outStockLabel;
                    const btnDisabled= p.in_stock ? '' : 'disabled';

                    col.innerHTML = `
                        <div class="product-card">
                            <a href="${p.product_url}" style="display:block;text-decoration:none;">
                                <div class="product-image">
                                    ${imgHtml}
                                    ${badgeHtml}
                                    ${stockBadge}
                                </div>
                            </a>
                            <div class="product-info">
                                <h6 class="product-name bengali-text">${p.name}</h6>
                                <div class="product-price">${priceHtml}</div>
                                <div class="product-actions">
                                    <button class="btn btn-success btn-sm" onclick="addToCart(${p.id})" ${btnDisabled}>
                                        <i class="fas fa-plus"></i> ${btnLabel}
                                    </button>
                                    <a href="${p.product_url}" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>`;
                    grid.appendChild(col);
                });
                page++;
            }

            hasMore = data.has_more;
            if (!hasMore) {
                endMark.style.display = 'block';
                observer.disconnect();
            }
        } catch (err) {
            console.error('Infinite scroll error:', err);
        } finally {
            loading = false;
            loader.style.display = 'none';
        }
    }

    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) loadMore();
    }, { rootMargin: '200px' });

    observer.observe(sentinel);
})();

// ─── Autocomplete ─────────────────────────────────────────────────────────────
let acTimer = null, acIndex = -1, acItems = [];

function onSearchInput(val) {
    clearTimeout(acTimer);
    if (val.length < 2) { hideDropdown(); return; }
    acTimer = setTimeout(() => fetchSuggestions(val), 220);
}

async function fetchSuggestions(q) {
    try {
        const res  = await fetch(`${AUTOCOMPLETE_URL}?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        renderDropdown(data, q);
    } catch(e) { hideDropdown(); }
}

function renderDropdown(data, q) {
    const box = document.getElementById('acContent');
    if (!data.products.length && !data.categories.length) { hideDropdown(); return; }
    let html = '';

    if (data.categories.length) {
        html += `<div class="ac-section-title">${IS_BN ? 'ক্যাটাগরি' : 'Categories'}</div>`;
        data.categories.forEach(c => {
            html += `<a class="ac-item" href="${c.url}">
                <span class="ac-cat-icon" style="background:rgba(26,158,74,.12);">
                    <i class="${c.icon}" style="color:${c.color};"></i>
                </span>
                <span class="ac-item-name">${c.name}</span>
                <i class="fas fa-chevron-right" style="font-size:10px;color:var(--muted);"></i>
            </a>`;
        });
    }
    if (data.products.length) {
        html += `<div class="ac-section-title">${IS_BN ? 'পণ্য' : 'Products'}</div>`;
        data.products.forEach(p => {
            const priceHtml = p.has_discount
                ? `<span class="ac-item-price" style="color:var(--success);">৳${p.effective_price}</span>
                   <span style="font-size:11px;color:var(--muted);text-decoration:line-through;margin-left:3px;">৳${p.price}</span>`
                : `<span class="ac-item-price">৳${p.price}</span>`;
            html += `<a class="ac-item" href="${p.url}">
                <img class="ac-item-img" src="${p.img || ''}" onerror="this.style.display='none'">
                <span class="ac-item-name">${p.name}</span>
                ${priceHtml}
            </a>`;
        });
    }

    box.innerHTML = html;
    acItems = box.querySelectorAll('.ac-item');
    acIndex = -1;
    showDropdown();
}

function showDropdown() { document.getElementById('acDropdown').classList.add('show'); }
function hideDropdown() { document.getElementById('acDropdown').classList.remove('show'); acIndex = -1; }

function onSearchKeyDown(e) {
    if (e.key === 'Enter')     { hideDropdown(); doSearch(); return; }
    if (e.key === 'Escape')    { hideDropdown(); return; }
    if (e.key === 'ArrowDown') { e.preventDefault(); acIndex = Math.min(acIndex + 1, acItems.length - 1); highlightAcItem(); }
    if (e.key === 'ArrowUp')   { e.preventDefault(); acIndex = Math.max(acIndex - 1, -1); highlightAcItem(); }
}

function highlightAcItem() {
    acItems.forEach((el, i) => { el.style.background = i === acIndex ? 'var(--primary-light)' : ''; });
    if (acIndex >= 0) acItems[acIndex].scrollIntoView({ block: 'nearest' });
}

function doSearch() {
    const q = document.getElementById('searchInputMain').value;
    const params = new URLSearchParams(window.location.search);
    params.set('q', q);
    params.delete('page');
    window.location.href = `${SEARCH_URL}?${params.toString()}`;
}

document.addEventListener('click', e => {
    if (!e.target.closest('.autocomplete-wrap')) hideDropdown();
});

// ─── Price Range Slider ───────────────────────────────────────────────────────
function updateRange() {
    const rMin = document.getElementById('rangeMin');
    const rMax = document.getElementById('rangeMax');
    if (!rMin || !rMax) return;
    let lo = parseInt(rMin.value), hi = parseInt(rMax.value);
    if (lo > hi) { [lo, hi] = [hi, lo]; }
    document.getElementById('priceMinLabel').textContent = lo;
    document.getElementById('priceMaxLabel').textContent = hi;
    document.getElementById('hiddenPriceMin').value = lo > GLOBAL_MIN ? lo : '';
    document.getElementById('hiddenPriceMax').value = hi < GLOBAL_MAX ? hi : '';
    const pct1 = ((lo - GLOBAL_MIN) / (GLOBAL_MAX - GLOBAL_MIN)) * 100;
    const pct2 = ((hi - GLOBAL_MIN) / (GLOBAL_MAX - GLOBAL_MIN)) * 100;
    document.getElementById('priceTrack').style.left  = pct1 + '%';
    document.getElementById('priceTrack').style.right = (100 - pct2) + '%';
}
updateRange();

// ─── Sort ─────────────────────────────────────────────────────────────────────
function applySort(val) {
    const params = new URLSearchParams(window.location.search);
    params.set('sort', val);
    params.delete('page');
    window.location.href = `${SEARCH_URL}?${params.toString()}`;
}

// ─── Mobile filter sidebar ────────────────────────────────────────────────────
(function setNavbarHeight() {
    const navbar = document.querySelector('nav, header, .navbar, #navbar, #topbar');
    if (navbar) {
        const h = navbar.getBoundingClientRect().bottom;
        document.documentElement.style.setProperty('--navbar-height', h + 'px');
    }
})();

function openMobileFilter() {
    const sidebar = document.getElementById('filterSidebar');
    const overlay = document.getElementById('filterOverlay');
    sidebar.classList.add('mobile-open');
    overlay.classList.add('show');
    requestAnimationFrame(() => requestAnimationFrame(() => sidebar.classList.add('sidebar-visible')));
    document.body.style.overflow = 'hidden';
}

function closeMobileFilter() {
    const sidebar = document.getElementById('filterSidebar');
    const overlay = document.getElementById('filterOverlay');
    sidebar.classList.remove('sidebar-visible');
    overlay.classList.remove('show');
    setTimeout(() => {
        sidebar.classList.remove('mobile-open');
        document.body.style.overflow = '';
    }, 280);
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMobileFilter(); });
</script>
@endpush
@endsection
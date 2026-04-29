@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'পণ্যের বিবরণ' : 'Product Details')
@section('page-title', app()->getLocale()==='bn' ? 'পণ্যের বিবরণ' : 'Product Details')

@section('content')
<div class="row">
  {{-- Left: Image + actions --}}
  <div class="col-md-4 mb-4">
    <div class="card animate-fadeInUp">
      <div class="card-body text-center">
        @if($product->image_url)
          <img src="{{ $product->image_url }}"
            class="img-fluid rounded mb-3"
            style="max-height:280px;object-fit:contain;border:1px solid var(--border)">
        @else
          <div class="d-flex align-items-center justify-content-center rounded mb-3"
            style="height:280px;background:var(--bg);border:1px solid var(--border)">
            <i class="fas fa-image fa-4x text-muted"></i>
          </div>
        @endif

        <div class="d-grid gap-2">
          <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>{{ app()->getLocale()==='bn' ? 'সম্পাদনা' : 'Edit Product' }}
          </a>
          @if($activeOrders == 0)
          <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
            onsubmit="return confirm('{{ app()->getLocale()==='bn' ? 'নিশ্চিতভাবে মুছবেন?' : 'Confirm delete?' }}')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger w-100">
              <i class="fas fa-trash me-2"></i>{{ app()->getLocale()==='bn' ? 'মুছে ফেলুন' : 'Delete' }}
            </button>
          </form>
          @else
          <button class="btn btn-outline-secondary" disabled>
            <i class="fas fa-lock me-2"></i>{{ $activeOrders }} {{ app()->getLocale()==='bn' ? 'সক্রিয় অর্ডার' : 'Active Orders' }}
          </button>
          @endif
          <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>{{ app()->getLocale()==='bn' ? 'ফিরে যান' : 'Back' }}
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- Right: Details --}}
  <div class="col-md-8 mb-4">
    <div class="card animate-fadeInUp">
      <div class="card-header">
        <h5 class="card-title bengali-text">
          {{ $product->name }}
          <span class="badge {{ $product->status ? 'bg-success' : 'bg-secondary' }} ms-1">
            {{ $product->status ? (app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active') : (app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Inactive') }}
          </span>
        </h5>
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tr>
            <th class="bengali-text" style="width:35%">{{ app()->getLocale()==='bn' ? 'বাংলা নাম:' : 'Bengali Name:' }}</th>
            <td class="bengali-text">{{ $product->name_bn }}</td>
          </tr>
          <tr>
            <th>English Name:</th>
            <td>{{ $product->name_en }}</td>
          </tr>
          <tr>
            <th class="bengali-text">{{ app()->getLocale()==='bn' ? 'ক্যাটাগরি:' : 'Category:' }}</th>
            <td>{{ $product->category?->name ?? '-' }}</td>
          </tr>
          <tr>
            <th class="bengali-text">{{ app()->getLocale()==='bn' ? 'ধরন:' : 'Type:' }}</th>
            <td><span class="badge bg-secondary">{{ $product->product_type ?? '-' }}</span></td>
          </tr>
          <tr>
            <th class="bengali-text">{{ app()->getLocale()==='bn' ? 'সাইজ:' : 'Size:' }}</th>
            <td><span class="badge bg-info">{{ $product->product_size ?? '-' }}</span></td>
          </tr>
          <tr>
            <th class="bengali-text">SKU:</th>
            <td><code>{{ $product->sku ?? '-' }}</code></td>
          </tr>
          <tr>
            <th class="bengali-text">{{ app()->getLocale()==='bn' ? 'নিয়মিত দাম:' : 'Regular Price:' }}</th>
            <td><strong class="fs-5">৳{{ number_format($product->price, 2) }}</strong></td>
          </tr>
          <tr>
            <th class="bengali-text">{{ app()->getLocale()==='bn' ? 'চূড়ান্ত দাম:' : 'Final Price:' }}</th>
            <td>
              <strong class="text-success fs-5">৳{{ number_format($product->final_price, 2) }}</strong>
              @if($product->activeDiscount)
                <span class="discount-badge ms-2">{{ number_format($product->activeDiscount->discount_percentage, 1) }}% OFF</span>
              @endif
            </td>
          </tr>
          <tr>
            <th class="bengali-text">{{ app()->getLocale()==='bn' ? 'স্টক:' : 'Stock:' }}</th>
            <td>
              <span class="{{ $product->stock_quantity > 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                {{ $product->stock_quantity }}
              </span>
            </td>
          </tr>
          <tr>
            <th class="bengali-text">{{ app()->getLocale()==='bn' ? 'তৈরির তারিখ:' : 'Created:' }}</th>
            <td>{{ $product->created_at->format('M d, Y H:i') }}</td>
          </tr>
          <tr>
            <th class="bengali-text">{{ app()->getLocale()==='bn' ? 'শেষ আপডেট:' : 'Updated:' }}</th>
            <td>{{ $product->updated_at->format('M d, Y H:i') }}</td>
          </tr>
        </table>
      </div>
    </div>

    {{-- Description --}}
    @if($product->description_bn || $product->description_en)
    <div class="card mt-3 animate-fadeInUp">
      <div class="card-header">
        <h6 class="card-title mb-0 bengali-text">{{ app()->getLocale()==='bn' ? 'বিবরণ' : 'Description' }}</h6>
      </div>
      <div class="card-body">
        @if($product->description_bn)
          <p class="bengali-text mb-2"><strong>বাংলা:</strong> {{ $product->description_bn }}</p>
        @endif
        @if($product->description_en)
          <p class="mb-0"><strong>English:</strong> {{ $product->description_en }}</p>
        @endif
      </div>
    </div>
    @endif

    {{-- Active Discount --}}
    @if($product->activeDiscount)
    <div class="card mt-3 animate-fadeInUp" style="border-left:4px solid #22c55e">
      <div class="card-header">
        <h6 class="card-title mb-0 bengali-text text-success">
          <i class="fas fa-tag me-2"></i>{{ app()->getLocale()==='bn' ? 'সক্রিয় ছাড়' : 'Active Discount' }}
        </h6>
      </div>
      <div class="card-body">
        <div class="row text-center">
          <div class="col-3">
            <div class="text-muted small bengali-text">{{ app()->getLocale()==='bn' ? 'মূল দাম' : 'Original' }}</div>
            <strong>৳{{ number_format($product->activeDiscount->original_price) }}</strong>
          </div>
          <div class="col-3">
            <div class="text-muted small bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড়ের দাম' : 'Discount' }}</div>
            <strong class="text-success">৳{{ number_format($product->activeDiscount->discount_price) }}</strong>
          </div>
          <div class="col-3">
            <div class="text-muted small bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড়%' : 'Percent' }}</div>
            <span class="discount-badge">{{ number_format($product->activeDiscount->discount_percentage, 1) }}%</span>
          </div>
          <div class="col-3">
            <div class="text-muted small bengali-text">{{ app()->getLocale()==='bn' ? 'শেষ তারিখ' : 'Ends' }}</div>
            <strong>{{ $product->activeDiscount->end_date->format('M d') }}</strong>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
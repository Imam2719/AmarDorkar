@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'ছাড়ের বিবরণ' : 'Discount Details')
@section('page-title', app()->getLocale()==='bn' ? 'ছাড়ের বিবরণ' : 'Discount Details')

@section('content')

<div class="mb-3">
  <a href="{{ route('admin.discounts.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="fas fa-arrow-left me-2"></i>{{ app()->getLocale()==='bn' ? 'ফিরে যান' : 'Back to Discounts' }}
  </a>
</div>

<div class="row g-4">

  {{-- Discount Info Card --}}
  <div class="col-lg-6">
    <div class="card animate-fadeInUp">
      <div class="card-header">
        <h5 class="card-title"><i class="fas fa-percent me-2"></i>{{ app()->getLocale()==='bn' ? 'ছাড়ের তথ্য' : 'Discount Info' }}</h5>
      </div>
      <div class="card-body">
        <table class="table mb-0">
          <tr>
            <th style="width:40%;color:var(--muted);font-weight:500;font-size:13px;">{{ app()->getLocale()==='bn' ? 'মূল মূল্য' : 'Original Price' }}</th>
            <td><strong>৳{{ number_format($discount->original_price, 2) }}</strong></td>
          </tr>
          <tr>
            <th style="color:var(--muted);font-weight:500;font-size:13px;">{{ app()->getLocale()==='bn' ? 'ছাড়ের মূল্য' : 'Discount Price' }}</th>
            <td><strong class="text-success">৳{{ number_format($discount->discount_price, 2) }}</strong></td>
          </tr>
          <tr>
            <th style="color:var(--muted);font-weight:500;font-size:13px;">{{ app()->getLocale()==='bn' ? 'ছাড়ের পরিমাণ' : 'You Save' }}</th>
            <td><strong class="text-danger">৳{{ number_format($discount->original_price - $discount->discount_price, 2) }}</strong></td>
          </tr>
          <tr>
            <th style="color:var(--muted);font-weight:500;font-size:13px;">{{ app()->getLocale()==='bn' ? 'ছাড়%' : 'Discount %' }}</th>
            <td><span class="badge bg-warning text-dark fs-6">{{ number_format($discount->discount_percentage, 1) }}%</span></td>
          </tr>
          <tr>
            <th style="color:var(--muted);font-weight:500;font-size:13px;">{{ app()->getLocale()==='bn' ? 'শুরুর তারিখ' : 'Start Date' }}</th>
            <td>{{ $discount->start_date->format('M d, Y H:i') }}</td>
          </tr>
          <tr>
            <th style="color:var(--muted);font-weight:500;font-size:13px;">{{ app()->getLocale()==='bn' ? 'শেষ তারিখ' : 'End Date' }}</th>
            <td>{{ $discount->end_date->format('M d, Y H:i') }}</td>
          </tr>
          <tr>
            <th style="color:var(--muted);font-weight:500;font-size:13px;">{{ app()->getLocale()==='bn' ? 'অবস্থা' : 'Status' }}</th>
            <td>
              <span class="status-badge {{ $discount->is_active ? 'status-delivered' : 'status-cancelled' }}">
                {{ $discount->is_active
                   ? (app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active')
                   : (app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Inactive') }}
              </span>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  {{-- Product Info Card --}}
  <div class="col-lg-6">
    <div class="card animate-fadeInUp">
      <div class="card-header">
        <h5 class="card-title"><i class="fas fa-box me-2"></i>{{ app()->getLocale()==='bn' ? 'পণ্যের তথ্য' : 'Product Info' }}</h5>
      </div>
      <div class="card-body">
        @if($discount->product)
          <div class="d-flex gap-3 mb-3">
            @if($discount->product->image_url)
              <img src="{{ $discount->product->image_url }}"
                   style="width:80px;height:80px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
            @else
              <div style="width:80px;height:80px;background:var(--surface-2);border-radius:10px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-image fa-2x text-muted"></i>
              </div>
            @endif
            <div>
              <h6 class="bengali-text mb-1">{{ $discount->product->name_bn }}</h6>
              <p class="text-muted mb-1" style="font-size:13px;">{{ $discount->product->name_en }}</p>
              @if($discount->product->product_type)
                <span class="badge bg-secondary">{{ $discount->product->product_type }}</span>
              @endif
              @if($discount->product->product_size)
                <span class="badge bg-info ms-1">{{ $discount->product->product_size }}</span>
              @endif
            </div>
          </div>
          <hr>
          <div class="d-flex gap-2">
            <a href="{{ route('admin.products.show', $discount->product) }}" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-eye me-1"></i>{{ app()->getLocale()==='bn' ? 'পণ্য দেখুন' : 'View Product' }}
            </a>
            <a href="{{ route('admin.products.edit', $discount->product) }}" class="btn btn-outline-warning btn-sm">
              <i class="fas fa-edit me-1"></i>{{ app()->getLocale()==='bn' ? 'সম্পাদনা' : 'Edit Product' }}
            </a>
          </div>
        @else
          <p class="text-muted bengali-text">{{ app()->getLocale()==='bn' ? 'পণ্য পাওয়া যায়নি।' : 'Product not found.' }}</p>
        @endif
      </div>
    </div>
  </div>

</div>

{{-- Actions --}}
<div class="card mt-4 animate-fadeInUp">
  <div class="card-header">
    <h5 class="card-title"><i class="fas fa-cog me-2"></i>{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</h5>
  </div>
  <div class="card-body d-flex gap-3 flex-wrap">

    {{-- Toggle Active --}}
    <form method="POST" action="{{ route('admin.discounts.toggle', $discount) }}">
      @csrf
      <button type="submit" class="btn {{ $discount->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}">
        <i class="fas {{ $discount->is_active ? 'fa-pause' : 'fa-play' }} me-2"></i>
        {{ $discount->is_active
           ? (app()->getLocale()==='bn' ? 'নিষ্ক্রিয় করুন' : 'Deactivate')
           : (app()->getLocale()==='bn' ? 'সক্রিয় করুন' : 'Activate') }}
      </button>
    </form>

    {{-- Delete --}}
    <form method="POST" action="{{ route('admin.discounts.destroy', $discount) }}"
          onsubmit="return confirm('{{ app()->getLocale()==='bn' ? 'এই ছাড় মুছে ফেলতে চান?' : 'Delete this discount?' }}')">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-outline-danger">
        <i class="fas fa-trash me-2"></i>{{ app()->getLocale()==='bn' ? 'মুছে ফেলুন' : 'Delete' }}
      </button>
    </form>

    <a href="{{ route('admin.discounts.index') }}" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-2"></i>{{ app()->getLocale()==='bn' ? 'তালিকায় ফিরুন' : 'Back to List' }}
    </a>

  </div>
</div>

@endsection
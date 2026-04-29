@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'পণ্য ব্যবস্থাপনা' : 'Product Management')
@section('page-title', app()->getLocale()==='bn' ? 'পণ্য ব্যবস্থাপনা' : 'Product Management')

@section('content')

{{-- Filters --}}
<div class="adm-card mb-4">
  <div class="adm-card-body">
    <form method="GET" class="row g-3">
      <div class="col-md-5">
        <input type="text" class="adm-input" name="search"
          placeholder="{{ app()->getLocale()==='bn' ? 'পণ্য খুঁজুন...' : 'Search products...' }}"
          value="{{ $search }}">
      </div>
      <div class="col-md-4">
        <select class="adm-select" name="type">
          <option value="">{{ app()->getLocale()==='bn' ? 'সব ধরনের' : 'All Types' }}</option>
          @foreach($productTypes as $type)
          <option value="{{ $type }}" {{ $typeFilter==$type ? 'selected' : '' }}>{{ $type }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        {{-- Only one Filter button here, Add Product button is in the card header below --}}
        <button type="submit" class="adm-btn adm-btn-primary w-100">
          <i class="fas fa-search"></i>{{ app()->getLocale()==='bn' ? 'ফিল্টার' : 'Filter' }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="adm-card fade-up">
  <div class="adm-card-head">
    <div class="adm-card-title">
      <i class="fas fa-box" style="color:var(--primary)"></i>
      {{ app()->getLocale()==='bn' ? 'পণ্যের তালিকা' : 'Products List' }}
      <span class="badge bg-secondary ms-1">{{ $products->total() }}</span>
    </div>
    {{-- Single "Add Product" button lives here only --}}
    <a href="{{ route('admin.products.create') }}" class="adm-btn adm-btn-primary adm-btn-sm">
      <i class="fas fa-plus"></i>{{ app()->getLocale()==='bn' ? 'নতুন পণ্য' : 'Add Product' }}
    </a>
  </div>
  <div class="table-responsive">
    <table class="adm-table">
      <thead>
        <tr>
          <th>{{ app()->getLocale()==='bn' ? 'ছবি' : 'Image' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'পণ্যের নাম' : 'Product Name' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'ধরন' : 'Type' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'সাইজ' : 'Size' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'দাম' : 'Price' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'ছাড়' : 'Discount' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $product)
        <tr>
          <td>
            @php $adminImg = $product->image_url; @endphp
            @if($adminImg && !str_contains($adminImg, 'no-image.png'))
             <img src="{{ $adminImg }}"
   style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:1px solid var(--border);"
   alt="{{ $product->name }}">
            @else
              <div style="width:56px;height:56px;border-radius:8px;border:1px solid var(--border);background:var(--bg);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-image" style="color:var(--muted-2)"></i>
              </div>
            @endif
          </td>
          <td>
            <strong>{{ $product->name }}</strong>
            <br><small style="color:var(--muted)">{{ Str::limit($product->description, 50) }}</small>
          </td>
          <td><span class="badge bg-secondary">{{ $product->product_type }}</span></td>
          <td><span class="badge bg-info text-dark">{{ $product->product_size }}</span></td>
          <td><strong>৳{{ number_format($product->price, 2) }}</strong></td>
          <td>
            @if($product->activeDiscount)
              <span class="adm-badge s-confirmed">
                {{ number_format($product->activeDiscount->discount_percentage, 1) }}% OFF
              </span>
              <br><small style="color:var(--muted)">৳{{ number_format($product->activeDiscount->discount_price) }}</small>
            @else
              <span style="color:var(--muted-2)">—</span>
            @endif
          </td>
          <td>
            <span class="adm-badge {{ $product->status ? 's-confirmed' : 's-cancelled' }}">
              {{ $product->status
                ? (app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active')
                : (app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Inactive') }}
            </span>
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="{{ route('admin.products.show', $product) }}"
                 class="adm-btn adm-btn-ghost adm-btn-icon adm-btn-xs"
                 title="{{ app()->getLocale()==='bn' ? 'দেখুন' : 'View' }}"
                 style="color:var(--primary);border-color:var(--primary)">
                <i class="fas fa-eye"></i>
              </a>
              <a href="{{ route('admin.products.edit', $product) }}"
                 class="adm-btn adm-btn-ghost adm-btn-icon adm-btn-xs"
                 title="{{ app()->getLocale()==='bn' ? 'সম্পাদনা' : 'Edit' }}"
                 style="color:var(--warning);border-color:var(--warning)">
                <i class="fas fa-edit"></i>
              </a>
              <button class="adm-btn adm-btn-ghost adm-btn-icon adm-btn-xs delete-product-btn"
                data-id="{{ $product->id }}"
                data-name="{{ $product->name_en }}"
                title="{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}"
                style="color:var(--danger);border-color:var(--danger)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">
            <i class="fas fa-box-open fa-2x mb-3" style="display:block;opacity:.4"></i>
            {{ app()->getLocale()==='bn' ? 'কোন পণ্য পাওয়া যায়নি' : 'No products found' }}
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($products->hasPages())
  <div class="adm-card-body border-top" style="border-color:var(--border)!important">
    {{ $products->withQueryString()->links() }}
  </div>
  @endif
</div>

{{-- Delete Confirm Modal --}}
@push('modals')
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-trash me-2" style="color:var(--danger)"></i>
          {{ app()->getLocale()==='bn' ? 'মুছে ফেলা নিশ্চিত করুন' : 'Confirm Delete' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="deleteForm">
        @csrf @method('DELETE')
        <div class="modal-body">
          <p style="color:var(--danger)">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong id="deleteProductName"></strong>
            {{ app()->getLocale()==='bn' ? ' — মুছে ফেলবেন?' : ' will be permanently deleted!' }}
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="adm-btn adm-btn-ghost" data-bs-dismiss="modal">
            {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
          </button>
          <button type="submit" class="adm-btn adm-btn-danger">
            <i class="fas fa-trash"></i>{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endpush

@endsection

@push('scripts')
<script>
document.querySelectorAll('.delete-product-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('deleteProductName').textContent = btn.dataset.name;
    document.getElementById('deleteForm').action = `/admin/products/${btn.dataset.id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
  });
});
</script>
@endpush

@extends('layouts.admin')
@section('title', 'Flash Sale: ' . $flashSale->title_en)
@section('page-title', 'Flash Sale: ' . $flashSale->title_en)

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-3"><i class="fas fa-times-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Flash Sale Info Card --}}
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>{{ $flashSale->title_en }}</h5>
                <div>
                    <form method="POST" action="{{ route('admin.flash-sales.toggle', $flashSale) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $flashSale->is_active ? 'btn-light' : 'btn-outline-light' }}">
                            <i class="fas {{ $flashSale->is_active ? 'fa-pause' : 'fa-play' }} me-1"></i>
                            {{ $flashSale->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.flash-sales.index') }}" class="btn btn-sm btn-outline-light ms-2">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="text-muted small">{{ app()->getLocale()==='bn' ? 'শুরু' : 'Start Time' }}</div>
                        <strong>{{ $flashSale->start_time->format('d M Y') }}</strong><br>
                        <span class="badge bg-secondary">{{ $flashSale->start_time->format('h:i A') }}</span>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">{{ app()->getLocale()==='bn' ? 'শেষ' : 'End Time' }}</div>
                        <strong>{{ $flashSale->end_time->format('d M Y') }}</strong><br>
                        <span class="badge bg-danger">{{ $flashSale->end_time->format('h:i A') }}</span>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</div>
                        @if($flashSale->is_active && $flashSale->end_time > now() && $flashSale->start_time <= now())
                            <span class="badge bg-success fs-6"><i class="fas fa-bolt me-1"></i>LIVE</span>
                        @elseif($flashSale->is_active)
                            <span class="badge bg-warning text-dark fs-6">Active</span>
                        @else
                            <span class="badge bg-secondary fs-6">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <div style="font-size:40px;font-weight:800;color:#dc3545;">{{ $flashSale->products->count() }}</div>
                <div class="text-muted">{{ app()->getLocale()==='bn' ? 'মোট পণ্য' : 'Total Products' }}</div>
                <div class="mt-2 text-success fw-bold">{{ $flashSale->products->sum('sold_quantity') }} {{ app()->getLocale()==='bn' ? 'টি বিক্রি' : 'sold' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Flash Sale --}}
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i>{{ app()->getLocale()==='bn' ? 'Flash Sale সম্পাদনা করুন' : 'Edit Flash Sale' }}</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.flash-sales.update', $flashSale) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Title (EN) *</label>
                    <input type="text" class="form-control" name="title_en" value="{{ old('title_en', $flashSale->title_en) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">শিরোনাম (বাং) *</label>
                    <input type="text" class="form-control" name="title_bn" value="{{ old('title_bn', $flashSale->title_bn) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ app()->getLocale()==='bn' ? 'শুরুর সময়' : 'Start Time' }} *</label>
                    <input type="datetime-local" class="form-control" name="start_time"
                           value="{{ old('start_time', $flashSale->start_time->format('Y-m-d\TH:i')) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ app()->getLocale()==='bn' ? 'শেষের সময়' : 'End Time' }} *</label>
                    <input type="datetime-local" class="form-control" name="end_time"
                           value="{{ old('end_time', $flashSale->end_time->format('Y-m-d\TH:i')) }}" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i>{{ app()->getLocale()==='bn' ? 'আপডেট করুন' : 'Update' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Add Product Form --}}
@if($products->count())
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>{{ app()->getLocale()==='bn' ? 'পণ্য যোগ করুন' : 'Add Product to Flash Sale' }}</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.flash-sales.add-product', $flashSale) }}">
            @csrf
            <div class="row align-items-end g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ app()->getLocale()==='bn' ? 'পণ্য নির্বাচন' : 'Select Product' }} *</label>
                    <select class="form-select" name="product_id" required>
                        <option value="">-- Select --</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name_en }} — ৳{{ number_format($p->price) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ app()->getLocale()==='bn' ? 'ফ্ল্যাশ মূল্য' : 'Flash Price (৳)' }} *</label>
                    <input type="number" class="form-control" name="flash_price" id="flashPriceInput" step="0.01" min="1" required placeholder="0.00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ app()->getLocale()==='bn' ? 'সর্বোচ্চ পরিমাণ' : 'Max Quantity' }} *</label>
                    <input type="number" class="form-control" name="max_quantity" min="1" value="50" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-danger w-100"><i class="fas fa-bolt me-1"></i>Add</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Flash Sale Products --}}
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-list me-2"></i>{{ app()->getLocale()==='bn' ? 'ফ্ল্যাশ সেলের পণ্য' : 'Flash Sale Products' }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Product' }}</th>
                        <th>{{ app()->getLocale()==='bn' ? 'মূল মূল্য' : 'Original' }}</th>
                        <th>{{ app()->getLocale()==='bn' ? 'ফ্ল্যাশ মূল্য' : 'Flash Price' }}</th>
                        <th>{{ app()->getLocale()==='bn' ? 'ছাড়' : 'Discount' }}</th>
                        <th>{{ app()->getLocale()==='bn' ? 'বিক্রি / মোট' : 'Sold / Total' }}</th>
                        <th>{{ app()->getLocale()==='bn' ? 'অগ্রগতি' : 'Progress' }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($flashSale->products as $fsp)
                @php
                    $discountPct = $fsp->product ? round((($fsp->product->price - $fsp->flash_price) / $fsp->product->price) * 100) : 0;
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($fsp->product?->primaryImage)
                                <img src="{{ $fsp->product->image_url }}" style="width:42px;height:42px;object-fit:cover;border-radius:8px;">
                            @elseif($fsp->product?->image_path)
                                <img src="{{ $fsp->product->image_url }}" style="width:42px;height:42px;object-fit:cover;border-radius:8px;">
                            @endif
                            <div>
                                <strong class="bengali-text">{{ $fsp->product?->name_en }}</strong><br>
                                <small class="text-muted">{{ $fsp->product?->product_type }}</small>
                            </div>
                        </div>
                    </td>
                    <td><del class="text-muted">৳{{ number_format($fsp->product?->price ?? 0) }}</del></td>
                    <td><strong class="text-danger">৳{{ number_format($fsp->flash_price) }}</strong></td>
                    <td><span class="badge bg-warning text-dark">{{ $discountPct }}% OFF</span></td>
                    <td>
                        <span class="text-success fw-bold">{{ $fsp->sold_quantity }}</span>
                        <span class="text-muted"> / {{ $fsp->max_quantity }}</span>
                    </td>
                    <td style="min-width:120px;">
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar bg-danger" style="width:{{ $fsp->sold_percent }}%"></div>
                        </div>
                        <small class="text-muted">{{ $fsp->sold_percent }}% sold</small>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.flash-sales.remove-product', [$flashSale, $fsp]) }}" onsubmit="return confirm('Remove this product?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">
                    <i class="fas fa-bolt fa-2x mb-2 d-block opacity-25"></i>
                    {{ app()->getLocale()==='bn' ? 'এখনো কোন পণ্য যোগ করা হয়নি' : 'No products added yet' }}
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('select[name="product_id"]')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const price = opt.dataset.price;
    if (price) {
        const suggested = Math.round(price * 0.7);
        document.getElementById('flashPriceInput').value = suggested;
        document.getElementById('flashPriceInput').placeholder = 'Suggested: ৳' + suggested;
    }
});
</script>
@endpush

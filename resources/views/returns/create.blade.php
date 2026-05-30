@extends('layouts.app')
@section('title', __('messages.submit_return'))

@section('content')
<div class="container py-4" style="max-width:750px;">

  {{-- Flash messages --}}
  @if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px;">
    <ul class="mb-0 ps-3">
      @foreach($errors->all() as $err)
      <li>{{ $err }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  {{-- Header --}}
  <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>{{ __('messages.back_to_orders') }}
    </a>
    <div>
      <h5 class="fw-bold mb-0"><i class="fas fa-undo-alt me-2 text-warning"></i>{{ __('messages.submit_return') }}</h5>
      <small class="text-muted">{{ __('messages.return_policy_summary') }}</small>
    </div>
  </div>

  {{-- Policy notice --}}
  <div class="alert alert-info d-flex gap-2 align-items-start" style="border-radius:10px; font-size:.88rem;">
    <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
    <div>
      <strong>{{ __('messages.return_policy_title') }}</strong><br>
      {{ __('messages.return_policy_desc') }}
    </div>
  </div>

  @if($order)
  {{-- Order Summary Card --}}
  <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
    <div class="card-header bg-white fw-semibold border-bottom py-3" style="border-radius:12px 12px 0 0;">
      <i class="fas fa-receipt me-2 text-success"></i>{{ __('messages.order_details') }}
    </div>
    <div class="card-body">
      <div class="row g-2" style="font-size:.9rem;">
        <div class="col-6 col-md-4 text-muted">{{ __('messages.order_number') }}</div>
        <div class="col-6 col-md-8 fw-semibold">{{ $order->order_number }}</div>
        <div class="col-6 col-md-4 text-muted">{{ __('messages.order_date') }}</div>
        <div class="col-6 col-md-8">{{ $order->created_at->format('d M Y') }}</div>
        {{-- FIX: delivered_at দেখাও, updated_at নয় --}}
        @if($order->delivered_at)
        <div class="col-6 col-md-4 text-muted">{{ __('messages.delivered_on') }}</div>
        <div class="col-6 col-md-8">{{ $order->delivered_at->format('d M Y') }}</div>
        <div class="col-6 col-md-4 text-muted">{{ __('messages.return_deadline') }}</div>
        <div class="col-6 col-md-8">
          <span class="badge bg-{{ $order->canRequestReturn(7) ? 'success' : 'danger' }}">
            {{ $order->returnDeadline(7)?->format('d M Y') }}
          </span>
          @if(!$order->canRequestReturn(7))
            <small class="text-danger ms-1">{{ __('messages.return_window_expired') }}</small>
          @endif
        </div>
        @endif
        <div class="col-6 col-md-4 text-muted">{{ __('messages.total_amount') }}</div>
        <div class="col-6 col-md-8 text-success fw-bold">৳{{ number_format($order->total_amount, 2) }}</div>
      </div>
      {{-- Items list --}}
      <div class="mt-3">
        @foreach($order->items as $item)
        <div class="d-flex align-items-center gap-2 mb-2">
          @php $img = $item->product?->images->first(); @endphp
          @if($img)
          <img src="{{ route('image.product', $img->id) }}" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:8px;">
          @else
          <div style="width:42px;height:42px;background:#f3f4f6;border-radius:8px;display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-box text-muted" style="font-size:14px;"></i>
          </div>
          @endif
          <div style="font-size:.87rem;">
            <div class="fw-semibold">{{ $item->product_name }}</div>
            <div class="text-muted">৳{{ number_format($item->price, 2) }} × {{ $item->quantity }}</div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif

  {{-- Return Form --}}
  <div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white fw-semibold border-bottom py-3" style="border-radius:12px 12px 0 0;">
      <i class="fas fa-clipboard-list me-2 text-warning"></i>{{ __('messages.return_form_title') }}
    </div>
    <div class="card-body p-3 p-md-4">
      <form method="POST" action="{{ route('returns.store') }}" enctype="multipart/form-data" id="returnForm">
        @csrf

        {{-- Order selection (if not pre-selected) --}}
        @if(!$order)
        <div class="mb-4">
          <label class="form-label fw-semibold" for="order_id">{{ __('messages.select_order') }} <span class="text-danger">*</span></label>
          <select name="order_id" id="order_id" class="form-select @error('order_id') is-invalid @enderror" required>
            <option value="">— {{ __('messages.choose_order') }} —</option>
            @foreach(Auth::user()->orders()->where('status','delivered')->get() as $o)
            @php
              {{--
                FIX: delivered_at থেকে deadline হিসাব করো।
                delivered_at না থাকলে (পুরনো order) valid = false।
              --}}
              $valid    = $o->canRequestReturn(7);
              $existing = \App\Models\ReturnRequest::where('order_id',$o->id)
                            ->where('user_id',Auth::id())
                            ->whereNotIn('status',['rejected'])
                            ->exists();
              $deadline = $o->returnDeadline(7);
            @endphp
            @if($valid && !$existing)
            <option value="{{ $o->id }}" {{ old('order_id') == $o->id ? 'selected' : '' }}>
              {{ $o->order_number }} — ৳{{ number_format($o->total_amount,2) }}
              ({{ __('messages.delivered') }}: {{ $o->delivered_at?->format('d M Y') ?? $o->created_at->format('d M Y') }})
              @if($deadline)
                — {{ __('messages.deadline') }}: {{ $deadline->format('d M') }}
              @endif
            </option>
            @endif
            @endforeach
          </select>
          @error('order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        @else
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        @endif

        {{-- Reason --}}
        <div class="mb-4">
          <label class="form-label fw-semibold" for="reason">{{ __('messages.return_reason') }} <span class="text-danger">*</span></label>
          <select name="reason" id="reason" class="form-select @error('reason') is-invalid @enderror" required>
            <option value="">— {{ __('messages.select_reason') }} —</option>
            @php
              $reasons = [
                'defective_product'   => [app()->getLocale()==='bn' ? 'ত্রুটিপূর্ণ পণ্য' : 'Defective Product', '🔧'],
                'wrong_item'          => [app()->getLocale()==='bn' ? 'ভুল পণ্য পেয়েছি' : 'Wrong Item Received', '📦'],
                'not_as_described'    => [app()->getLocale()==='bn' ? 'বিবরণ অনুযায়ী নয়' : 'Not as Described', '❗'],
                'damaged_in_shipping' => [app()->getLocale()==='bn' ? 'ডেলিভারিতে ক্ষতিগ্রস্ত' : 'Damaged in Shipping', '💔'],
                'changed_mind'        => [app()->getLocale()==='bn' ? 'মন পরিবর্তন' : 'Changed My Mind', '🔄'],
                'missing_parts'       => [app()->getLocale()==='bn' ? 'যন্ত্রাংশ অনুপস্থিত' : 'Missing Parts/Accessories', '🔩'],
                'other'               => [app()->getLocale()==='bn' ? 'অন্যান্য' : 'Other', '📝'],
              ];
            @endphp
            @foreach($reasons as $val => [$label, $icon])
            <option value="{{ $val }}" {{ old('reason') == $val ? 'selected' : '' }}>{{ $icon }} {{ $label }}</option>
            @endforeach
          </select>
          @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Description --}}
        <div class="mb-4">
          <label class="form-label fw-semibold" for="description">{{ __('messages.return_description') }} <span class="text-danger">*</span></label>
          <textarea name="description" id="description" rows="5"
            class="form-control @error('description') is-invalid @enderror"
            placeholder="{{ __('messages.return_description_ph') }}"
            minlength="20" maxlength="2000" required>{{ old('description') }}</textarea>
          <div class="form-text">{{ __('messages.return_desc_hint') }}</div>
          @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Image upload (max 5) --}}
        <div class="mb-4">
          <label class="form-label fw-semibold">{{ __('messages.return_images') }} <span class="text-muted fw-normal">({{ __('messages.optional') }})</span></label>
          <div id="imageDropZone" class="border border-dashed rounded p-4 text-center"
               style="border-style:dashed!important;border-color:#d1d5db!important;cursor:pointer;background:#fafafa;transition:background .2s;"
               onclick="document.getElementById('imageInput').click();"
               ondragover="this.style.background='#eff6ff'; event.preventDefault();"
               ondragleave="this.style.background='#fafafa';"
               ondrop="handleDrop(event)">
            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
            <p class="mb-0" style="font-size:.88rem;">{{ __('messages.drag_drop_images') }}</p>
            <p class="text-muted mb-0" style="font-size:.8rem;">{{ __('messages.image_format_hint') }}</p>
          </div>
          <input type="file" id="imageInput" name="images[]" accept="image/*" multiple class="d-none" onchange="previewImages(this)">
          <div id="imagePreviewGrid" class="d-flex flex-wrap gap-2 mt-2"></div>
          <div class="form-text">{{ __('messages.max_images_hint') }}</div>
          @error('images')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          @error('images.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Submit --}}
        <div class="d-flex gap-2 flex-wrap">
          <button type="submit" class="btn btn-warning fw-semibold px-4" id="submitBtn">
            <i class="fas fa-paper-plane me-2"></i>{{ __('messages.submit_return_btn') }}
          </button>
          <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">{{ __('messages.cancel') }}</a>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
const MAX_IMAGES = 5;
let selectedFiles = [];

function previewImages(input) {
  addFiles(Array.from(input.files));
}

function handleDrop(e) {
  e.preventDefault();
  document.getElementById('imageDropZone').style.background = '#fafafa';
  addFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')));
}

function addFiles(newFiles) {
  newFiles.forEach(f => {
    if (selectedFiles.length < MAX_IMAGES) selectedFiles.push(f);
  });
  renderPreviews();
  syncInputFiles();
}

function removeFile(idx) {
  selectedFiles.splice(idx, 1);
  renderPreviews();
  syncInputFiles();
}

function renderPreviews() {
  const grid = document.getElementById('imagePreviewGrid');
  grid.innerHTML = '';
  selectedFiles.forEach((file, idx) => {
    const reader = new FileReader();
    reader.onload = e => {
      const wrap = document.createElement('div');
      wrap.style.cssText = 'position:relative;width:80px;height:80px;';
      wrap.innerHTML = `
        <img src="${e.target.result}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid #e5e7eb;">
        <button type="button" onclick="removeFile(${idx})"
          style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:#ef4444;border:none;color:#fff;font-size:11px;line-height:1;cursor:pointer;">
          ✕
        </button>`;
      grid.appendChild(wrap);
    };
    reader.readAsDataURL(file);
  });
}

function syncInputFiles() {
  const dt = new DataTransfer();
  selectedFiles.forEach(f => dt.items.add(f));
  document.getElementById('imageInput').files = dt.files;
}
</script>
@endsection

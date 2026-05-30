@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'পণ্য সম্পাদনা' : 'Edit Product')
@section('page-title', app()->getLocale()==='bn' ? 'পণ্য সম্পাদনা' : 'Edit Product')

@section('content')
<div class="card animate-fadeInUp">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title bengali-text"><i class="fas fa-edit me-2"></i>{{ app()->getLocale()==='bn' ? 'পণ্য সম্পাদনা' : 'Edit Product' }}</h5>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>{{ app()->getLocale()==='bn' ? 'ফিরে যান' : 'Back' }}
    </a>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
      @csrf @method('PUT')

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'পণ্যের নাম (বাংলা) *' : 'Product Name (Bengali) *' }}</label>
            <input type="text" class="form-control @error('name_bn') is-invalid @enderror"
              name="name_bn" value="{{ old('name_bn', $product->name_bn) }}" required>
            @error('name_bn')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Product Name (English) *</label>
            <input type="text" class="form-control @error('name_en') is-invalid @enderror"
              name="name_en" value="{{ old('name_en', $product->name_en) }}" required>
            @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">{{ app()->getLocale()==='bn' ? 'ক্যাটাগরি' : 'Category' }}</label>
            <select class="form-select" name="category_id">
              <option value="">{{ app()->getLocale()==='bn' ? 'নির্বাচন করুন' : 'Select' }}</option>
              @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id)==$cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
              </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'পণ্যের ধরন' : 'Product Type' }}</label>
            <input type="text" class="form-control" name="product_type" value="{{ old('product_type', $product->product_type) }}">
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'সাইজ' : 'Size' }}</label>
            <input type="hidden" name="product_size" id="productSizeInput" value="{{ old('product_size', $product->product_size) }}">
            <div id="sizeTagBox" style="min-height:38px;border:1px solid var(--border);border-radius:.375rem;padding:4px 8px;display:flex;flex-wrap:wrap;gap:5px;align-items:center;cursor:text;background:var(--surface);" onclick="document.getElementById('sizeTypingInput').focus()">
                <input type="text" id="sizeTypingInput" placeholder="{{ app()->getLocale()==='bn' ? 'সাইজ লিখে Enter চাপুন' : 'Type size & press Enter' }}"
                    style="border:none;outline:none;font-size:13px;min-width:120px;background:transparent;padding:2px 4px;"
                    onkeydown="handleSizeKey(event)">
            </div>
            <small style="color:var(--muted);font-size:11px;">{{ app()->getLocale()==='bn' ? 'Enter বা কমা দিয়ে আলাদা করুন। সাইজ না থাকলে ফাঁকা রাখুন।' : 'Press Enter or comma to add. Leave empty if no sizes.' }}</small>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'দাম (৳) *' : 'Price (৳) *' }}</label>
            <input type="number" class="form-control @error('price') is-invalid @enderror"
              name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'স্টক পরিমাণ' : 'Stock Quantity' }}</label>
            <input type="number" class="form-control" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0">
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">SKU</label>
            <input type="text" class="form-control" name="sku" value="{{ old('sku', $product->sku) }}">
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'বিবরণ (বাংলা)' : 'Description (Bengali)' }}</label>
        <textarea class="form-control" name="description_bn" rows="3">{{ old('description_bn', $product->description_bn) }}</textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Description (English)</label>
        <textarea class="form-control" name="description_en" rows="3">{{ old('description_en', $product->description_en) }}</textarea>
      </div>

      {{-- ══ IMAGE MANAGEMENT ══ --}}
      <div class="mb-4">
        <label class="form-label bengali-text fw-semibold">
          <i class="fas fa-images me-1 text-primary"></i>
          {{ app()->getLocale()==='bn' ? 'পণ্যের ছবি' : 'Product Images' }}
        </label>

        {{-- EXISTING IMAGES --}}
        @if($product->images->count())
        <div class="mb-3">
          <p class="form-text mb-2">
            {{ app()->getLocale()==='bn'
              ? 'বর্তমান ছবি — ক্লিক করে Thumbnail সেট করুন, ☑ দিয়ে ডিলিট করুন'
              : 'Current images — click to set as Thumbnail, check ☑ to delete' }}
          </p>
          <div style="display:flex;flex-wrap:wrap;gap:12px;" id="existingImagesGrid">
            @foreach($product->images as $img)
            <div style="position:relative;width:110px;flex-shrink:0;" id="imgCard{{ $img->id }}">
              {{-- Thumbnail radio --}}
              <input type="radio" name="primary_image_id" value="{{ $img->id }}" id="pri{{ $img->id }}"
                     {{ $img->is_primary ? 'checked' : '' }} style="display:none;">
              <label for="pri{{ $img->id }}" style="cursor:pointer;display:block;">
                <div id="imgBorder{{ $img->id }}"
                     style="width:110px;height:110px;border-radius:10px;overflow:hidden;
                            border:3px solid {{ $img->is_primary ? '#0d6efd' : '#dee2e6' }};transition:border-color .2s;">
                  <img src="{{ $img->url }}"
                       style="width:100%;height:100%;object-fit:cover;">
                </div>
                @if($img->is_primary)
                <div id="badge{{ $img->id }}"
                     style="background:#0d6efd;color:#fff;font-size:10px;font-weight:700;text-align:center;padding:3px;border-radius:0 0 7px 7px;margin-top:-3px;">
                  <i class="fas fa-star"></i> {{ app()->getLocale()==='bn' ? 'Thumbnail' : 'Thumbnail' }}
                </div>
                @else
                <div id="badge{{ $img->id }}" style="display:none;background:#0d6efd;color:#fff;font-size:10px;font-weight:700;text-align:center;padding:3px;border-radius:0 0 7px 7px;margin-top:-3px;">
                  <i class="fas fa-star"></i> Thumbnail
                </div>
                @endif
              </label>

              {{-- Delete checkbox --}}
              <div style="margin-top:5px;display:flex;align-items:center;gap:4px;">
                <input type="checkbox" name="delete_images[]" value="{{ $img->id }}"
                       id="del{{ $img->id }}" class="delete-check"
                       style="width:14px;height:14px;cursor:pointer;"
                       onchange="toggleDeleteMark({{ $img->id }}, this.checked)">
                <label for="del{{ $img->id }}" style="font-size:11px;color:var(--danger);cursor:pointer;margin:0;">
                  {{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}
                </label>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @else
        <p class="text-muted small mb-2">{{ app()->getLocale()==='bn' ? 'কোনো ছবি নেই।' : 'No images yet.' }}</p>
        @endif

        {{-- ADD NEW IMAGES --}}
        <div class="mt-2">
          <p class="form-text mb-2">
            {{ app()->getLocale()==='bn' ? 'নতুন ছবি যোগ করুন (সর্বোচ্চ ৮টি মোট)' : 'Add new images (max 8 total)' }}
          </p>
          <div id="dropZone"
               style="border:2px dashed var(--border);border-radius:12px;padding:24px;text-align:center;cursor:pointer;background:var(--bg);"
               onclick="document.getElementById('imageInput').click()"
               ondragover="event.preventDefault();this.style.borderColor='#0d6efd';"
               ondragleave="this.style.borderColor='';"
               ondrop="handleDrop(event)">
            <i class="fas fa-plus-circle fa-xl mb-2" style="color:var(--muted);"></i>
            <p class="mb-0" style="color:var(--muted);font-size:13px;">
              {{ app()->getLocale()==='bn' ? 'ছবি টেনে আনুন বা ক্লিক করুন' : 'Drag & drop or click to add images' }}
            </p>
          </div>
          <input type="file" id="imageInput" name="product_images[]" accept="image/*" multiple class="d-none">
          <input type="hidden" name="primary_image_index" id="primaryNewIndexInput" value="-1">
          <div id="newImageGrid" class="mt-3" style="display:flex;flex-wrap:wrap;gap:12px;"></div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_new_arrival" value="1" id="isNewArrival"
              {{ old('is_new_arrival', $product->is_new_arrival) ? 'checked' : '' }}>
            <label class="form-check-label bengali-text" for="isNewArrival">
              <span class="badge bg-info text-dark me-1"><i class="fas fa-star"></i></span>
              {{ app()->getLocale()==='bn' ? 'নতুন আগমন' : 'New Arrival' }}
            </label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="status" value="1" id="statusCheck"
              {{ old('status', $product->status) ? 'checked' : '' }}>
            <label class="form-check-label bengali-text" for="statusCheck">{{ app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active' }}</label>
          </div>
        </div>
      </div>

      {{-- Discount Section --}}
      <hr>
      <h6 class="bengali-text mb-3">
        {{ app()->getLocale()==='bn' ? 'ছাড়ের তথ্য' : 'Discount Information' }}
        @if($product->activeDiscount)
          <span class="badge bg-success ms-2">
            {{ number_format($product->activeDiscount->discount_percentage, 1) }}% {{ app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active' }}
          </span>
        @endif
      </h6>
      <div class="row">
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড়ের দাম (৳)' : 'Discount Price (৳)' }}</label>
            <input type="number" class="form-control" name="discount_price"
              value="{{ old('discount_price', $product->activeDiscount?->discount_price) }}" step="0.01" min="0">
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড় শুরু' : 'Discount Start' }}</label>
            <input type="datetime-local" class="form-control" name="discount_start"
              value="{{ old('discount_start', $product->activeDiscount?->start_date?->format('Y-m-d\TH:i')) }}">
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড় শেষ' : 'Discount End' }}</label>
            <input type="datetime-local" class="form-control" name="discount_end"
              value="{{ old('discount_end', $product->activeDiscount?->end_date?->format('Y-m-d\TH:i')) }}">
          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save me-2"></i>{{ app()->getLocale()==='bn' ? 'আপডেট করুন' : 'Update Product' }}
        </button>
        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-secondary">
          {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
const IS_BN = {{ app()->getLocale() === 'bn' ? 'true' : 'false' }};
const MAX_IMAGES = 8;

// newFiles = array of {file, base64, mimeType}
let newFiles = [];

const input      = document.getElementById('imageInput');
const newGrid    = document.getElementById('newImageGrid');
const primaryIdx = document.getElementById('primaryNewIndexInput');
const theForm    = input.closest('form');

// ── Existing image — thumbnail radio visual update ─────────────────
document.querySelectorAll('input[name="primary_image_id"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="primary_image_id"]').forEach(r => {
            const border = document.getElementById('imgBorder' + r.value);
            const badge  = document.getElementById('badge'    + r.value);
            if (border) border.style.borderColor = '#dee2e6';
            if (badge)  badge.style.display = 'none';
        });
        const border = document.getElementById('imgBorder' + this.value);
        const badge  = document.getElementById('badge'    + this.value);
        if (border) border.style.borderColor = '#0d6efd';
        if (badge)  badge.style.display = 'block';
        primaryIdx.value = -1;
        renderNewGrid();
    });
});

// ── Mark deleted images visually ────────────────────────────────────
function toggleDeleteMark(id, checked) {
    const card = document.getElementById('imgCard' + id);
    if (card) card.style.opacity = checked ? '.35' : '1';
}

// ── File input change & drag-drop ────────────────────────────────────
input.addEventListener('change', function() {
    addFiles(Array.from(this.files));
    this.value = '';
});

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.borderColor = '';
    addFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')));
}

function addFiles(files) {
    let added = 0;
    for (const file of files) {
        if (newFiles.length >= MAX_IMAGES) break;
        if (file.size > 5 * 1024 * 1024) {
            alert((IS_BN ? 'সর্বোচ্চ ৫MB: ' : 'Max 5MB: ') + file.name);
            continue;
        }
        // Read as base64 immediately
        const reader = new FileReader();
        reader.onload = (e) => {
            const dataUrl  = e.target.result;                    // data:image/jpeg;base64,/9j/...
            const b64      = dataUrl.split(',')[1];              // pure base64
            const mimeType = file.type;
            newFiles.push({ file, base64: b64, mimeType, dataUrl });
            renderNewGrid();
        };
        reader.readAsDataURL(file);
        added++;
    }
}

function removeNewFile(idx) {
    newFiles.splice(idx, 1);
    let pi = parseInt(primaryIdx.value);
    if (pi >= newFiles.length) primaryIdx.value = -1;
    renderNewGrid();
}

function setNewPrimary(idx) {
    primaryIdx.value = idx;
    document.querySelectorAll('input[name="primary_image_id"]').forEach(r => {
        r.checked = false;
        const border = document.getElementById('imgBorder' + r.value);
        const badge  = document.getElementById('badge'    + r.value);
        if (border) border.style.borderColor = '#dee2e6';
        if (badge)  badge.style.display = 'none';
    });
    renderNewGrid();
}

function renderNewGrid() {
    newGrid.innerHTML = '';
    const pi = parseInt(primaryIdx.value);
    newFiles.forEach((item, idx) => {
        const isPrimary = idx === pi;
        const card = document.createElement('div');
        card.style.cssText = 'position:relative;width:110px;flex-shrink:0;';
        card.innerHTML = `
            <div style="width:110px;height:110px;border-radius:10px;overflow:hidden;
                 border:3px solid ${isPrimary ? '#0d6efd' : '#dee2e6'};cursor:pointer;"
                 onclick="setNewPrimary(${idx})">
                <img src="${item.dataUrl}" style="width:100%;height:100%;object-fit:cover;">
                ${isPrimary ? `<div style="position:absolute;bottom:0;left:0;right:0;background:rgba(13,110,253,.85);color:#fff;font-size:10px;font-weight:700;text-align:center;padding:3px;">
                    <i class="fas fa-star"></i> Thumbnail</div>` : ''}
            </div>
            <button type="button" onclick="removeNewFile(${idx})"
                style="position:absolute;top:-8px;right:-8px;width:22px;height:22px;border-radius:50%;background:#dc3545;border:none;color:#fff;font-size:11px;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:2;">
                <i class="fas fa-times"></i>
            </button>
            <div style="font-size:10px;color:var(--muted);margin-top:4px;text-align:center;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:110px;">
                ${item.file.name}
            </div>`;
        newGrid.appendChild(card);
    });
}

// ── On form submit: inject base64 hidden inputs ──────────────────────
// This replaces the unreliable DataTransfer / input.files approach.
// We send images as hidden fields: new_image_b64[], new_image_mime[]
theForm.addEventListener('submit', function(e) {
    if (newFiles.length === 0) return; // nothing to inject

    // Remove any previously injected hidden inputs (if user submitted twice)
    theForm.querySelectorAll('.js-b64-img').forEach(el => el.remove());

    newFiles.forEach((item, idx) => {
        const b64Input = document.createElement('input');
        b64Input.type  = 'hidden';
        b64Input.name  = 'new_image_b64[]';
        b64Input.value = item.base64;
        b64Input.className = 'js-b64-img';

        const mimeInput = document.createElement('input');
        mimeInput.type  = 'hidden';
        mimeInput.name  = 'new_image_mime[]';
        mimeInput.value = item.mimeType;
        mimeInput.className = 'js-b64-img';

        theForm.appendChild(b64Input);
        theForm.appendChild(mimeInput);
    });
});

// ── Size Tag Input ──────────────────────────────────────────
(function(){
    const hiddenInput = document.getElementById('productSizeInput');
    const tagBox      = document.getElementById('sizeTagBox');
    const typingInput = document.getElementById('sizeTypingInput');
    if (!hiddenInput || !tagBox || !typingInput) return;
    function getSizes() {
        return hiddenInput.value ? hiddenInput.value.split(',').map(s=>s.trim()).filter(Boolean) : [];
    }
    function renderTags() {
        tagBox.querySelectorAll('.size-tag').forEach(t => t.remove());
        getSizes().forEach(sz => {
            const tag = document.createElement('span');
            tag.className = 'size-tag';
            tag.style.cssText = 'display:inline-flex;align-items:center;gap:4px;background:#0d6efd;color:#fff;padding:2px 8px 2px 10px;border-radius:20px;font-size:12px;font-weight:600;';
            tag.innerHTML = `${sz} <button type="button" onclick="removeSize('${sz}')" style="background:none;border:none;color:#fff;font-size:14px;cursor:pointer;padding:0;line-height:1;">&times;</button>`;
            tagBox.insertBefore(tag, typingInput);
        });
    }
    function addSize(val) {
        const cleaned = val.trim().toUpperCase();
        if (!cleaned) return;
        const sizes = getSizes();
        if (!sizes.includes(cleaned)) { sizes.push(cleaned); hiddenInput.value = sizes.join(', '); renderTags(); }
        typingInput.value = '';
    }
    window.removeSize = function(sz) {
        const sizes = getSizes().filter(s => s !== sz);
        hiddenInput.value = sizes.join(', ');
        renderTags();
    };
    window.handleSizeKey = function(e) {
        if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addSize(typingInput.value.replace(',','')); }
        else if (e.key === 'Backspace' && typingInput.value === '') {
            const sizes = getSizes();
            if (sizes.length) { sizes.pop(); hiddenInput.value = sizes.join(', '); renderTags(); }
        }
    };
    renderTags(); // init from existing value
})();
</script>
@endpush
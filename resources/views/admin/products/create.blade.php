@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'নতুন পণ্য যোগ করুন' : 'Add New Product')
@section('page-title', app()->getLocale()==='bn' ? 'নতুন পণ্য যোগ করুন' : 'Add New Product')

@section('content')
<div class="card animate-fadeInUp">
  <div class="card-header">
    <h5 class="card-title bengali-text"><i class="fas fa-plus me-2"></i>{{ app()->getLocale()==='bn' ? 'নতুন পণ্য' : 'New Product' }}</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
      @csrf

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'পণ্যের নাম (বাংলা) *' : 'Product Name (Bengali) *' }}</label>
            <input type="text" class="form-control @error('name_bn') is-invalid @enderror" name="name_bn" value="{{ old('name_bn') }}" required>
            @error('name_bn')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Product Name (English) *</label>
            <input type="text" class="form-control @error('name_en') is-invalid @enderror" name="name_en" value="{{ old('name_en') }}" required>
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
              <option value="{{ $cat->id }}" {{ old('category_id')==$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'পণ্যের ধরন' : 'Product Type' }}</label>
            <input type="text" class="form-control" name="product_type" value="{{ old('product_type') }}"
              placeholder="{{ app()->getLocale()==='bn' ? 'যেমন: পোশাক, ইলেক্ট্রনিক্স' : 'e.g: Clothing, Electronics' }}">
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'সাইজ' : 'Size' }}</label>
            <input type="hidden" name="product_size" id="productSizeInput" value="{{ old('product_size') }}">
            <div id="sizeTagBox" style="min-height:38px;border:1px solid var(--border);border-radius:.375rem;padding:4px 8px;display:flex;flex-wrap:wrap;gap:5px;align-items:center;cursor:text;background:var(--surface);" onclick="document.getElementById('sizeTypingInput').focus()">
                <input type="text" id="sizeTypingInput" placeholder="{{ app()->getLocale()==='bn' ? 'সাইজ লিখে Enter চাপুন (যেমন S, M, L)' : 'Type size & press Enter (e.g. S, M, L)' }}"
                    style="border:none;outline:none;font-size:13px;min-width:140px;background:transparent;padding:2px 4px;"
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
            <input type="number" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price') }}" step="0.01" min="0" required>
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'স্টক পরিমাণ' : 'Stock Quantity' }}</label>
            <input type="number" class="form-control" name="stock_quantity" value="{{ old('stock_quantity',0) }}" min="0">
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">SKU</label>
            <input type="text" class="form-control" name="sku" value="{{ old('sku') }}" placeholder="Auto if empty">
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'বিবরণ (বাংলা)' : 'Description (Bengali)' }}</label>
        <textarea class="form-control" name="description_bn" rows="3">{{ old('description_bn') }}</textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Description (English)</label>
        <textarea class="form-control" name="description_en" rows="3">{{ old('description_en') }}</textarea>
      </div>

            {{-- ══ IMAGE UPLOAD ══ --}}
      <div class="mb-4">
        <label class="form-label bengali-text fw-semibold">
          <i class="fas fa-images me-1 text-primary"></i>
          {{ app()->getLocale()==='bn' ? 'পণ্যের ছবি (সর্বোচ্চ ৮টি)' : 'Product Images (Max 8)' }}
        </label>

        <input type="file"
               id="imageInput"
               name="product_images[]"
               accept="image/*"
               multiple
               class="form-control mb-3"
               onchange="previewImages(this)">

        <input type="hidden" name="primary_image_index" id="primaryIndexInput" value="0">

        <div id="imagePreviewGrid" style="display:flex;flex-wrap:wrap;gap:12px;"></div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_new_arrival" value="1" id="isNewArrival" {{ old('is_new_arrival') ? 'checked' : '' }}>
            <label class="form-check-label bengali-text" for="isNewArrival">
              <span class="badge bg-info text-dark me-1"><i class="fas fa-star"></i></span>
              {{ app()->getLocale()==='bn' ? 'নতুন আগমন' : 'New Arrival' }}
            </label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="status" value="1" id="statusCheck" {{ old('status',1) ? 'checked' : '' }}>
            <label class="form-check-label bengali-text" for="statusCheck">{{ app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active' }}</label>
          </div>
        </div>
      </div>

      {{-- Discount Section --}}
      <hr>
      <h6 class="bengali-text mb-3">{{ app()->getLocale()==='bn' ? 'ছাড়ের তথ্য (ঐচ্ছিক)' : 'Discount Information (Optional)' }}</h6>
      <div class="row">
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড়ের দাম (৳)' : 'Discount Price (৳)' }}</label>
            <input type="number" class="form-control" name="discount_price" value="{{ old('discount_price') }}" step="0.01" min="0">
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড় শুরু' : 'Discount Start' }}</label>
            <input type="datetime-local" class="form-control" name="discount_start" value="{{ old('discount_start', now()->format('Y-m-d\TH:i')) }}">
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড় শেষ' : 'Discount End' }}</label>
            <input type="datetime-local" class="form-control" name="discount_end" value="{{ old('discount_end', now()->addDays(30)->format('Y-m-d\TH:i')) }}">
          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-plus me-2"></i>{{ app()->getLocale()==='bn' ? 'পণ্য যোগ করুন' : 'Add Product' }}
        </button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">{{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}</a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
const IS_BN = {{ app()->getLocale() === 'bn' ? 'true' : 'false' }};

function previewImages(input) {
    const grid = document.getElementById('imagePreviewGrid');
    grid.innerHTML = '';

    const files = Array.from(input.files);
    files.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const isPrimary = (idx === 0);
            const card = document.createElement('div');
            card.style.cssText = 'position:relative;width:110px;flex-shrink:0;cursor:pointer;';
            card.id = 'imgCard' + idx;
            card.innerHTML = `
                <div id="imgBorder${idx}" style="width:110px;height:110px;border-radius:10px;overflow:hidden;
                     border:3px solid ${isPrimary ? '#0d6efd' : '#dee2e6'};"
                     onclick="setPrimary(${idx})">
                    <img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">
                    <div id="badge${idx}" style="position:absolute;bottom:0;left:0;right:0;
                         background:rgba(13,110,253,.85);color:#fff;font-size:10px;font-weight:700;
                         text-align:center;padding:3px;display:${isPrimary ? 'block' : 'none'};">
                        <i class="fas fa-star"></i> Thumbnail
                    </div>
                </div>
                <div style="font-size:10px;color:var(--muted);margin-top:4px;text-align:center;
                     overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:110px;">
                    ${file.name}
                </div>`;
            grid.appendChild(card);
        };
        reader.readAsDataURL(file);
    });
}

function setPrimary(idx) {
    document.getElementById('primaryIndexInput').value = idx;

    // Reset all borders and badges
    document.querySelectorAll('[id^="imgBorder"]').forEach(el => {
        el.style.borderColor = '#dee2e6';
    });
    document.querySelectorAll('[id^="badge"]').forEach(el => {
        el.style.display = 'none';
    });

    // Highlight selected
    const border = document.getElementById('imgBorder' + idx);
    const badge  = document.getElementById('badge' + idx);
    if (border) border.style.borderColor = '#0d6efd';
    if (badge)  badge.style.display = 'block';
}

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
        // Remove existing tags
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
        if (!sizes.includes(cleaned)) {
            sizes.push(cleaned);
            hiddenInput.value = sizes.join(', ');
            renderTags();
        }
        typingInput.value = '';
    }
    window.removeSize = function(sz) {
        const sizes = getSizes().filter(s => s !== sz);
        hiddenInput.value = sizes.join(', ');
        renderTags();
    };
    window.handleSizeKey = function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addSize(typingInput.value.replace(',',''));
        } else if (e.key === 'Backspace' && typingInput.value === '') {
            const sizes = getSizes();
            if (sizes.length) {
                sizes.pop();
                hiddenInput.value = sizes.join(', ');
                renderTags();
            }
        }
    };
    // Init render from old() value
    renderTags();
})();
</script>
@endpush
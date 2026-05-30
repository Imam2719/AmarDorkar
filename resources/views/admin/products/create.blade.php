@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'নতুন পণ্য যোগ করুন' : 'Add New Product')
@section('page-title', app()->getLocale()==='bn' ? 'নতুন পণ্য যোগ করুন' : 'Add New Product')

@push('styles')
<style>
/* ── Create Product — Full Dark + Responsive ─── */
.cp-wrap { max-width: 860px; }

.cp-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  box-shadow: var(--sh-xs);
  overflow: hidden;
  margin-bottom: 20px;
}
.cp-card-head {
  display: flex; align-items: center; gap: 10px;
  padding: 16px 22px;
  border-bottom: 1px solid var(--border);
  background: var(--bg);
}
.cp-card-head-title {
  font-size: 14px; font-weight: 700; color: var(--text);
  display: flex; align-items: center; gap: 8px;
}
.cp-card-head-title i { color: var(--primary); }
.cp-body { padding: 22px; }

/* Form groups */
.cp-row { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 0; }
.cp-col { flex: 1 1 200px; min-width: 0; }
.cp-col-full { flex: 1 1 100%; }
.cp-col-2 { flex: 1 1 280px; min-width: 0; }

.cp-label {
  display: block;
  font-size: 12px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .5px; color: var(--muted);
  margin-bottom: 6px;
}
.cp-required { color: var(--danger); margin-left: 2px; }

.cp-input, .cp-select, .cp-textarea {
  display: block; width: 100%;
  padding: 10px 13px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: var(--r-sm);
  color: var(--text);
  font-size: 13px;
  font-family: inherit;
  transition: border-color .15s, box-shadow .15s;
  outline: none;
}
.cp-input:focus, .cp-select:focus, .cp-textarea:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37,99,235,.12);
}
.cp-input::placeholder, .cp-textarea::placeholder { color: var(--muted-2); }
.cp-select option { background: var(--white); color: var(--text); }
.cp-textarea { resize: vertical; min-height: 80px; }
.cp-invalid { border-color: var(--danger) !important; }
.cp-err { font-size: 11px; color: var(--danger); margin-top: 4px; }

/* Size tag box */
.cp-size-box {
  min-height: 42px;
  border: 1px solid var(--border);
  border-radius: var(--r-sm);
  padding: 5px 10px;
  display: flex; flex-wrap: wrap; gap: 5px; align-items: center;
  cursor: text; background: var(--bg);
  transition: border-color .15s;
}
.cp-size-box:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
.cp-size-tag {
  display: inline-flex; align-items: center; gap: 4px;
  background: var(--primary); color: #fff;
  padding: 2px 8px 2px 10px; border-radius: 20px;
  font-size: 12px; font-weight: 600;
}
.cp-size-tag button {
  background: none; border: none; color: #fff;
  font-size: 14px; cursor: pointer; padding: 0; line-height: 1;
}
.cp-size-typing {
  border: none; outline: none; font-size: 13px;
  min-width: 140px; background: transparent; padding: 2px 4px;
  color: var(--text);
}
.cp-size-typing::placeholder { color: var(--muted-2); }

/* Toggle switch */
.cp-switch-row {
  display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 4px;
}
.cp-switch-item {
  display: flex; align-items: center; gap: 10px; cursor: pointer;
}
.cp-switch-item input[type=checkbox] { width: 17px; height: 17px; accent-color: var(--primary); cursor: pointer; }
.cp-switch-label { font-size: 13px; font-weight: 500; color: var(--text); }

/* Image upload */
.cp-img-upload-btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 9px 16px; border-radius: var(--r-sm);
  border: 2px dashed var(--border);
  background: var(--bg); color: var(--muted);
  font-size: 13px; font-weight: 500; cursor: pointer;
  transition: all .15s; width: 100%; justify-content: center;
}
.cp-img-upload-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-s); }
.cp-img-grid {
  display: flex; flex-wrap: wrap; gap: 12px; margin-top: 12px;
}
.cp-img-card {
  position: relative; width: 100px; flex-shrink: 0; cursor: pointer;
}
.cp-img-border {
  width: 100px; height: 100px; border-radius: 10px; overflow: hidden;
  border: 3px solid var(--border); transition: border-color .15s;
}
.cp-img-border.is-primary { border-color: var(--primary); }
.cp-img-border img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cp-img-thumb-label {
  position: absolute; bottom: 0; left: 0; right: 0;
  background: rgba(37,99,235,.88); color: #fff;
  font-size: 10px; font-weight: 700; text-align: center; padding: 3px;
  display: none; border-radius: 0 0 7px 7px;
}
.cp-img-border.is-primary .cp-img-thumb-label { display: block; }
.cp-img-name {
  font-size: 10px; color: var(--muted); margin-top: 4px; text-align: center;
  overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 100px;
}

/* Action buttons */
.cp-actions {
  display: flex; gap: 10px; align-items: center; flex-wrap: wrap;
  padding: 18px 22px;
  border-top: 1px solid var(--border);
  background: var(--bg);
}

/* Divider */
.cp-divider { height: 1px; background: var(--border); margin: 20px 0; }

@media (max-width: 600px) {
  .cp-body { padding: 16px; }
  .cp-col { flex: 1 1 100%; }
  .cp-col-2 { flex: 1 1 100%; }
  .cp-actions { padding: 14px 16px; }
}
</style>
@endpush

@section('content')
<div class="cp-wrap">

  {{-- Back --}}
  <div class="mb-3">
    <a href="{{ route('admin.products.index') }}" class="adm-btn adm-btn-ghost adm-btn-sm">
      <i class="fas fa-arrow-left"></i>{{ app()->getLocale()==='bn' ? 'পণ্যের তালিকা' : 'Back to Products' }}
    </a>
  </div>

  <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="createForm">
    @csrf

    {{-- ── Section 1: Basic Info ── --}}
    <div class="cp-card">
      <div class="cp-card-head">
        <div class="cp-card-head-title">
          <i class="fas fa-info-circle"></i>
          {{ app()->getLocale()==='bn' ? 'মূল তথ্য' : 'Basic Information' }}
        </div>
      </div>
      <div class="cp-body">

        {{-- Names --}}
        <div class="cp-row mb-4">
          <div class="cp-col-2">
            <label class="cp-label">{{ app()->getLocale()==='bn' ? 'পণ্যের নাম (বাংলা)' : 'Product Name (Bengali)' }} <span class="cp-required">*</span></label>
            <input type="text" name="name_bn" class="cp-input @error('name_bn') cp-invalid @enderror"
              value="{{ old('name_bn') }}" required
              placeholder="{{ app()->getLocale()==='bn' ? 'বাংলায় নাম লিখুন' : 'Name in Bengali' }}">
            @error('name_bn')<div class="cp-err">{{ $message }}</div>@enderror
          </div>
          <div class="cp-col-2">
            <label class="cp-label">Product Name (English) <span class="cp-required">*</span></label>
            <input type="text" name="name_en" class="cp-input @error('name_en') cp-invalid @enderror"
              value="{{ old('name_en') }}" required placeholder="Name in English">
            @error('name_en')<div class="cp-err">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- Category / Type / Size --}}
        <div class="cp-row mb-4">
          <div class="cp-col">
            <label class="cp-label">{{ app()->getLocale()==='bn' ? 'ক্যাটাগরি' : 'Category' }}</label>
            <select name="category_id" class="cp-select">
              <option value="">{{ app()->getLocale()==='bn' ? 'নির্বাচন করুন' : 'Select Category' }}</option>
              @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ old('category_id')==$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="cp-col">
            <label class="cp-label">{{ app()->getLocale()==='bn' ? 'পণ্যের ধরন' : 'Product Type' }}</label>
            <input type="text" name="product_type" class="cp-input"
              value="{{ old('product_type') }}"
              placeholder="{{ app()->getLocale()==='bn' ? 'যেমন: পোশাক, জুতা' : 'e.g: Clothing, Shoes' }}">
          </div>
          <div class="cp-col">
            <label class="cp-label">{{ app()->getLocale()==='bn' ? 'সাইজ' : 'Size' }}</label>
            <input type="hidden" name="product_size" id="productSizeInput" value="{{ old('product_size') }}">
            <div class="cp-size-box" id="sizeTagBox" onclick="document.getElementById('sizeTypingInput').focus()">
              <input type="text" id="sizeTypingInput" class="cp-size-typing"
                placeholder="{{ app()->getLocale()==='bn' ? 'S, M, L টাইপ করে Enter দিন' : 'Type S,M,L & press Enter' }}"
                onkeydown="handleSizeKey(event)">
            </div>
            <small style="color:var(--muted-2);font-size:11px;margin-top:3px;display:block">
              {{ app()->getLocale()==='bn' ? 'Enter বা কমা দিয়ে আলাদা করুন' : 'Press Enter or comma to add' }}
            </small>
          </div>
        </div>

        {{-- Price / Stock / SKU --}}
        <div class="cp-row mb-4">
          <div class="cp-col">
            <label class="cp-label">{{ app()->getLocale()==='bn' ? 'মূল্য (৳)' : 'Price (৳)' }} <span class="cp-required">*</span></label>
            <input type="number" name="price" class="cp-input @error('price') cp-invalid @enderror"
              value="{{ old('price') }}" step="0.01" min="0" required placeholder="0.00">
            @error('price')<div class="cp-err">{{ $message }}</div>@enderror
          </div>
          <div class="cp-col">
            <label class="cp-label">{{ app()->getLocale()==='bn' ? 'স্টক পরিমাণ' : 'Stock Quantity' }}</label>
            <input type="number" name="stock_quantity" class="cp-input"
              value="{{ old('stock_quantity', 0) }}" min="0" placeholder="0">
          </div>
          <div class="cp-col">
            <label class="cp-label">SKU</label>
            <input type="text" name="sku" class="cp-input"
              value="{{ old('sku') }}" placeholder="{{ app()->getLocale()==='bn' ? 'খালি রাখলে অটো' : 'Auto if empty' }}">
          </div>
        </div>

        {{-- Descriptions --}}
        <div class="cp-row mb-4">
          <div class="cp-col-2">
            <label class="cp-label">{{ app()->getLocale()==='bn' ? 'বিবরণ (বাংলা)' : 'Description (Bengali)' }}</label>
            <textarea name="description_bn" class="cp-textarea"
              placeholder="{{ app()->getLocale()==='bn' ? 'বাংলায় বিবরণ...' : 'Description in Bengali...' }}">{{ old('description_bn') }}</textarea>
          </div>
          <div class="cp-col-2">
            <label class="cp-label">Description (English)</label>
            <textarea name="description_en" class="cp-textarea"
              placeholder="Description in English...">{{ old('description_en') }}</textarea>
          </div>
        </div>

        {{-- Switches --}}
        <div class="cp-switch-row">
          <label class="cp-switch-item">
            <input type="checkbox" name="is_new_arrival" value="1" {{ old('is_new_arrival') ? 'checked' : '' }}>
            <span class="cp-switch-label">
              <span class="pi-badge pi-badge-size me-1"><i class="fas fa-star"></i></span>
              {{ app()->getLocale()==='bn' ? 'নতুন আগমন' : 'New Arrival' }}
            </span>
          </label>
          <label class="cp-switch-item">
            <input type="checkbox" name="status" value="1" id="statusCheck" {{ old('status', 1) ? 'checked' : '' }}>
            <span class="cp-switch-label">
              <i class="fas fa-circle me-1" style="color:#16a34a;font-size:9px"></i>
              {{ app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active' }}
            </span>
          </label>
        </div>

      </div>
    </div>

    {{-- ── Section 2: Images ── --}}
    <div class="cp-card">
      <div class="cp-card-head">
        <div class="cp-card-head-title">
          <i class="fas fa-images"></i>
          {{ app()->getLocale()==='bn' ? 'পণ্যের ছবি (সর্বোচ্চ ৮টি)' : 'Product Images (Max 8)' }}
        </div>
      </div>
      <div class="cp-body">
        <input type="file" id="imageInput" name="product_images[]"
          accept="image/*" multiple style="display:none" onchange="previewImages(this)">
        <input type="hidden" name="primary_image_index" id="primaryIndexInput" value="0">

        <div class="cp-img-upload-btn" onclick="document.getElementById('imageInput').click()">
          <i class="fas fa-cloud-upload-alt fa-lg"></i>
          {{ app()->getLocale()==='bn' ? 'ছবি বেছে নিন বা এখানে ড্রপ করুন' : 'Choose images or drop here' }}
        </div>
        <small style="color:var(--muted-2);font-size:11px;display:block;margin-top:6px">
          {{ app()->getLocale()==='bn' ? 'JPG, PNG, WEBP — সর্বোচ্চ ৫০ MB প্রতিটি। প্রথমটি Thumbnail হবে।' : 'JPG, PNG, WEBP — max 50 MB each. First image = Thumbnail.' }}
        </small>

        <div class="cp-img-grid" id="imagePreviewGrid"></div>
      </div>
    </div>

    {{-- ── Form Actions ── --}}
    <div class="cp-card">
      <div class="cp-actions">
        <button type="submit" class="adm-btn adm-btn-primary" id="submitBtn">
          <i class="fas fa-plus"></i>
          {{ app()->getLocale()==='bn' ? 'পণ্য যোগ করুন' : 'Add Product' }}
        </button>
        <a href="{{ route('admin.products.index') }}" class="adm-btn adm-btn-ghost">
          <i class="fas fa-times"></i>
          {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
        </a>
      </div>
    </div>

  </form>
</div>
@endsection

@push('scripts')
<script>
const IS_BN = {{ app()->getLocale() === 'bn' ? 'true' : 'false' }};

// ── Image Preview ────────────────────────────────────────
function previewImages(input) {
  const grid = document.getElementById('imagePreviewGrid');
  grid.innerHTML = '';
  Array.from(input.files).forEach((file, idx) => {
    const reader = new FileReader();
    reader.onload = function(e) {
      const isPrimary = idx === 0;
      const card = document.createElement('div');
      card.className = 'cp-img-card';
      card.id = 'imgCard' + idx;
      card.innerHTML = `
        <div class="cp-img-border ${isPrimary ? 'is-primary' : ''}" id="imgBorder${idx}" onclick="setPrimary(${idx})">
          <img src="${e.target.result}" alt="${file.name}">
          <div class="cp-img-thumb-label"><i class="fas fa-star"></i> Thumbnail</div>
        </div>
        <div class="cp-img-name">${file.name}</div>`;
      grid.appendChild(card);
    };
    reader.readAsDataURL(file);
  });
}

function setPrimary(idx) {
  document.getElementById('primaryIndexInput').value = idx;
  document.querySelectorAll('[id^="imgBorder"]').forEach(el => el.classList.remove('is-primary'));
  const b = document.getElementById('imgBorder' + idx);
  if (b) b.classList.add('is-primary');
}

// ── Size Tags ─────────────────────────────────────────────
(function(){
  const hiddenInput = document.getElementById('productSizeInput');
  const tagBox      = document.getElementById('sizeTagBox');
  const typingInput = document.getElementById('sizeTypingInput');
  if (!hiddenInput || !tagBox || !typingInput) return;

  function getSizes() {
    return hiddenInput.value ? hiddenInput.value.split(',').map(s=>s.trim()).filter(Boolean) : [];
  }
  function renderTags() {
    tagBox.querySelectorAll('.cp-size-tag').forEach(t => t.remove());
    getSizes().forEach(sz => {
      const tag = document.createElement('span');
      tag.className = 'cp-size-tag';
      tag.innerHTML = `${sz} <button type="button" onclick="removeSize('${sz}')">&times;</button>`;
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
      if (sizes.length) { sizes.pop(); hiddenInput.value = sizes.join(', '); renderTags(); }
    }
  };
  renderTags();
})();

// ── Submit guard ─────────────────────────────────────────
document.getElementById('createForm').addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' +
    (IS_BN ? 'যোগ হচ্ছে...' : 'Adding...');
});
</script>
@endpush
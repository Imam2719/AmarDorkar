@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'ব্যানার ব্যবস্থাপনা' : 'Banner Management')
@section('page-title', app()->getLocale()==='bn' ? 'ব্যানার ব্যবস্থাপনা' : 'Banner Management')

@section('content')
<div class="mb-3 d-flex justify-content-end">
    <button class="btn btn-success" onclick="document.getElementById('addBannerModal').style.display='flex'">
        <i class="fas fa-plus me-2"></i>{{ app()->getLocale()==='bn' ? 'নতুন ব্যানার' : 'Add Banner' }}
    </button>
</div>

@if($banners->isEmpty())
<div class="card p-5 text-center">
    <i class="fas fa-images fa-3x text-muted mb-3 d-block"></i>
    <h5 class="bengali-text text-muted">{{ app()->getLocale()==='bn' ? 'কোনো ব্যানার নেই' : 'No banners yet' }}</h5>
    <p class="text-muted small">{{ app()->getLocale()==='bn' ? 'উপরে "নতুন ব্যানার" বাটনে ক্লিক করুন' : 'Click "Add Banner" to get started' }}</p>
</div>
@else
<div class="row g-3">
    @foreach($banners as $banner)
    <div class="col-md-6 col-xl-4">
        <div class="card h-100" style="border:1px solid var(--border);border-radius:14px;overflow:hidden;">
            <div style="position:relative;height:200px;background:var(--bg);">
                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                     style="width:100%;height:100%;object-fit:cover;">
                {{-- Status badge --}}
                <span style="position:absolute;top:10px;left:10px;background:{{ $banner->is_active ? '#22c55e' : '#94a3b8' }};color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;">
                    {{ $banner->is_active ? (app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active') : (app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Inactive') }}
                </span>
                {{-- Sort order --}}
                <span style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,.55);color:#fff;font-size:11px;padding:3px 9px;border-radius:20px;">
                    #{{ $banner->sort_order }}
                </span>
            </div>
            <div class="card-body p-3">
                @if($banner->title)
                <h6 class="bengali-text mb-1" style="font-size:14px;font-weight:600;">{{ $banner->title }}</h6>
                @endif
                @if($banner->subtitle)
                <p class="text-muted small bengali-text mb-2" style="font-size:12px;line-height:1.4;">{{ Str::limit($banner->subtitle, 70) }}</p>
                @endif
                @if($banner->button_text)
                <span style="background:var(--primary);color:#fff;font-size:11px;padding:2px 10px;border-radius:20px;display:inline-block;margin-bottom:8px;">
                    {{ $banner->button_text }} → {{ Str::limit($banner->button_link, 30) }}
                </span>
                @endif
            </div>
            <div class="card-footer p-2 d-flex gap-2" style="background:var(--bg);border-top:1px solid var(--border);">
                {{-- Toggle active --}}
                <form method="POST" action="{{ route('admin.banners.toggle', $banner) }}" style="flex:1;">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm w-100 {{ $banner->is_active ? 'btn-warning' : 'btn-success' }}">
                        <i class="fas fa-{{ $banner->is_active ? 'eye-slash' : 'eye' }} me-1"></i>
                        {{ $banner->is_active ? (app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Deactivate') : (app()->getLocale()==='bn' ? 'সক্রিয়' : 'Activate') }}
                    </button>
                </form>
                {{-- Edit --}}
                <button class="btn btn-sm btn-outline-primary" onclick="openEditModal({{ $banner->id }}, '{{ addslashes($banner->title) }}', '{{ addslashes($banner->subtitle) }}', '{{ addslashes($banner->button_text) }}', '{{ addslashes($banner->button_link) }}', {{ $banner->sort_order }})">
                    <i class="fas fa-pen"></i>
                </button>
                {{-- Delete --}}
                <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('{{ app()->getLocale()==='bn' ? 'ব্যানারটি মুছবেন?' : 'Delete this banner?' }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ══ ADD BANNER MODAL ══ --}}
<div id="addBannerModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:3000;align-items:center;justify-content:center;">
    <div style="background:var(--surface);border-radius:16px;width:100%;max-width:520px;margin:16px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3);">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <h5 class="mb-0 bengali-text">{{ app()->getLocale()==='bn' ? 'নতুন ব্যানার যোগ করুন' : 'Add New Banner' }}</h5>
            <button onclick="document.getElementById('addBannerModal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">×</button>
        </div>
        <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" style="padding:22px;">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-600 small bengali-text">{{ app()->getLocale()==='bn' ? 'ব্যানার ছবি *' : 'Banner Image *' }}</label>
                <input type="file" name="image" accept="image/*" required class="form-control"
                       onchange="previewBanner(this,'addPreview')">
                <div id="addPreview" style="margin-top:10px;display:none;">
                    <img src="" style="width:100%;max-height:160px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                </div>
                <small class="text-muted">{{ app()->getLocale()==='bn' ? 'jpg, png, webp — সর্বোচ্চ 4MB' : 'jpg, png, webp — max 4MB' }}</small>
            </div>
            <div class="mb-3">
                <label class="form-label fw-600 small bengali-text">{{ app()->getLocale()==='bn' ? 'শিরোনাম (ঐচ্ছিক)' : 'Title (optional)' }}</label>
                <input type="text" name="title" class="form-control" placeholder="{{ app()->getLocale()==='bn' ? 'প্রাকৃতিক উপাদান...' : 'Natural ingredients...' }}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-600 small bengali-text">{{ app()->getLocale()==='bn' ? 'সাব-টাইটেল (ঐচ্ছিক)' : 'Subtitle (optional)' }}</label>
                <textarea name="subtitle" class="form-control" rows="2" placeholder="{{ app()->getLocale()==='bn' ? 'আপনার সেরা পছন্দ...' : 'Your best choice...' }}"></textarea>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label fw-600 small bengali-text">{{ app()->getLocale()==='bn' ? 'বাটন টেক্সট' : 'Button Text' }}</label>
                    <input type="text" name="button_text" class="form-control" placeholder="{{ app()->getLocale()==='bn' ? 'অর্ডার করুন' : 'Shop Now' }}">
                </div>
                <div class="col-6">
                    <label class="form-label fw-600 small bengali-text">{{ app()->getLocale()==='bn' ? 'বাটন লিংক' : 'Button Link' }}</label>
                    <input type="text" name="button_link" class="form-control" placeholder="/products">
                </div>
            </div>
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <label class="form-label fw-600 small">{{ app()->getLocale()==='bn' ? 'সাজানোর ক্রম' : 'Sort Order' }}</label>
                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                </div>
                <div class="col-6 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_active" id="addActive" class="form-check-input" checked>
                        <label for="addActive" class="form-check-label bengali-text small">{{ app()->getLocale()==='bn' ? 'সক্রিয় রাখুন' : 'Active' }}</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success flex-fill">
                    <i class="fas fa-plus me-2"></i>{{ app()->getLocale()==='bn' ? 'ব্যানার যোগ করুন' : 'Add Banner' }}
                </button>
                <button type="button" onclick="document.getElementById('addBannerModal').style.display='none'" class="btn btn-outline-secondary">
                    {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ EDIT BANNER MODAL ══ --}}
<div id="editBannerModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:3000;align-items:center;justify-content:center;">
    <div style="background:var(--surface);border-radius:16px;width:100%;max-width:520px;margin:16px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3);">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <h5 class="mb-0 bengali-text">{{ app()->getLocale()==='bn' ? 'ব্যানার সম্পাদনা' : 'Edit Banner' }}</h5>
            <button onclick="document.getElementById('editBannerModal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">×</button>
        </div>
        <form id="editBannerForm" method="POST" action="" enctype="multipart/form-data" style="padding:22px;">
            @csrf @method('PATCH')
            <div class="mb-3">
                <label class="form-label fw-600 small bengali-text">{{ app()->getLocale()==='bn' ? 'নতুন ছবি (ঐচ্ছিক)' : 'Replace Image (optional)' }}</label>
                <input type="file" name="image" accept="image/*" class="form-control"
                       onchange="previewBanner(this,'editPreview')">
                <div id="editPreview" style="margin-top:10px;display:none;">
                    <img src="" style="width:100%;max-height:160px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-600 small bengali-text">{{ app()->getLocale()==='bn' ? 'শিরোনাম' : 'Title' }}</label>
                <input type="text" name="title" id="editTitle" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label fw-600 small bengali-text">{{ app()->getLocale()==='bn' ? 'সাব-টাইটেল' : 'Subtitle' }}</label>
                <textarea name="subtitle" id="editSubtitle" class="form-control" rows="2"></textarea>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label fw-600 small bengali-text">{{ app()->getLocale()==='bn' ? 'বাটন টেক্সট' : 'Button Text' }}</label>
                    <input type="text" name="button_text" id="editBtnText" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label fw-600 small bengali-text">{{ app()->getLocale()==='bn' ? 'বাটন লিংক' : 'Button Link' }}</label>
                    <input type="text" name="button_link" id="editBtnLink" class="form-control">
                </div>
            </div>
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <label class="form-label fw-600 small">{{ app()->getLocale()==='bn' ? 'সাজানোর ক্রম' : 'Sort Order' }}</label>
                    <input type="number" name="sort_order" id="editSortOrder" class="form-control" min="0">
                </div>
                <div class="col-6 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_active" id="editActive" class="form-check-input">
                        <label for="editActive" class="form-check-label bengali-text small">{{ app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active' }}</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-save me-2"></i>{{ app()->getLocale()==='bn' ? 'সংরক্ষণ করুন' : 'Save Changes' }}
                </button>
                <button type="button" onclick="document.getElementById('editBannerModal').style.display='none'" class="btn btn-outline-secondary">
                    {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewBanner(input, previewId) {
    const wrap = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            wrap.style.display = 'block';
            wrap.querySelector('img').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openEditModal(id, title, subtitle, btnText, btnLink, sortOrder) {
    const form = document.getElementById('editBannerForm');
    form.action = '/admin/banners/' + id;
    document.getElementById('editTitle').value      = title;
    document.getElementById('editSubtitle').value   = subtitle;
    document.getElementById('editBtnText').value    = btnText;
    document.getElementById('editBtnLink').value    = btnLink;
    document.getElementById('editSortOrder').value  = sortOrder;
    document.getElementById('editPreview').style.display = 'none';
    document.getElementById('editBannerModal').style.display = 'flex';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('addBannerModal').style.display = 'none';
        document.getElementById('editBannerModal').style.display = 'none';
    }
});
</script>
@endsection
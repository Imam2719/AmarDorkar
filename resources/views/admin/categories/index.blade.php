@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'ক্যাটাগরি ব্যবস্থাপনা' : 'Category Management')
@section('page-title', app()->getLocale()==='bn' ? 'ক্যাটাগরি ব্যবস্থাপনা' : 'Category Management')

@section('content')

<div class="row g-4">

  {{-- ── ADD NEW CATEGORY FORM ── --}}
  <div class="col-lg-4">
    <div class="card animate-fadeInUp">
      <div class="card-header">
        <h5 class="card-title bengali-text mb-0">
          <i class="fas fa-plus-circle me-2 text-success"></i>
          {{ app()->getLocale()==='bn' ? 'নতুন ক্যাটাগরি' : 'Add New Category' }}
        </h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.categories.store') }}">
          @csrf

          <div class="mb-3">
            <label class="form-label bengali-text">
              {{ app()->getLocale()==='bn' ? 'ক্যাটাগরি নাম (বাংলা) *' : 'Category Name (Bengali) *' }}
            </label>
            <input type="text"
                   class="form-control @error('name_bn') is-invalid @enderror"
                   name="name_bn"
                   value="{{ old('name_bn') }}"
                   placeholder="যেমন: পোশাক ও পরিধান"
                   required>
            @error('name_bn')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label">
              {{ app()->getLocale()==='bn' ? 'ক্যাটাগরি নাম (English) *' : 'Category Name (English) *' }}
            </label>
            <input type="text"
                   class="form-control @error('name_en') is-invalid @enderror"
                   name="name_en"
                   value="{{ old('name_en') }}"
                   placeholder="e.g. Clothing & Fashion"
                   required>
            @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label">
              {{ app()->getLocale()==='bn' ? 'আইকন (Font Awesome class)' : 'Icon (Font Awesome class)' }}
            </label>
            <div class="input-group">
              <span class="input-group-text" id="iconPreview"><i class="fas fa-tag"></i></span>
              <input type="text"
                     class="form-control"
                     name="icon"
                     id="iconInput"
                     value="{{ old('icon', 'fas fa-tag') }}"
                     placeholder="fas fa-tag"
                     oninput="updateIconPreview(this.value)">
            </div>
            <div class="form-text">
              <a href="https://fontawesome.com/icons" target="_blank">Font Awesome Icons ↗</a>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">
              {{ app()->getLocale()==='bn' ? 'রঙ' : 'Color' }}
            </label>
            <div class="input-group">
              <input type="color"
                     class="form-control form-control-color"
                     name="color"
                     value="{{ old('color', '#1a9e4a') }}"
                     style="max-width:60px;">
              <input type="text"
                     class="form-control"
                     name="color_text"
                     value="{{ old('color', '#1a9e4a') }}"
                     placeholder="#1a9e4a"
                     readonly>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">
              {{ app()->getLocale()==='bn' ? 'সাজানোর ক্রম' : 'Sort Order' }}
            </label>
            <input type="number"
                   class="form-control"
                   name="sort_order"
                   value="{{ old('sort_order', 0) }}"
                   min="0">
          </div>

          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-plus me-2"></i>
            {{ app()->getLocale()==='bn' ? 'ক্যাটাগরি যোগ করুন' : 'Add Category' }}
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- ── CATEGORY LIST ── --}}
  <div class="col-lg-8">
    <div class="card animate-fadeInUp">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title bengali-text mb-0">
          <i class="fas fa-list me-2"></i>
          {{ app()->getLocale()==='bn' ? 'সকল ক্যাটাগরি' : 'All Categories' }}
          <span class="badge bg-primary ms-2">{{ $categories->count() }}</span>
        </h5>
      </div>
      <div class="card-body p-0">
        @if($categories->isEmpty())
          <div class="text-center py-5 text-muted">
            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
            <p class="bengali-text">{{ app()->getLocale()==='bn' ? 'কোনো ক্যাটাগরি নেই।' : 'No categories found.' }}</p>
          </div>
        @else
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background:var(--bg);">
              <tr>
                <th style="width:50px;">#</th>
                <th>{{ app()->getLocale()==='bn' ? 'ক্যাটাগরি' : 'Category' }}</th>
                <th class="text-center" style="width:100px;">
                  {{ app()->getLocale()==='bn' ? 'পণ্য' : 'Products' }}
                </th>
                <th class="text-center" style="width:90px;">
                  {{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}
                </th>
                <th class="text-center" style="width:140px;">
                  {{ app()->getLocale()==='bn' ? 'অ্যাকশন' : 'Actions' }}
                </th>
              </tr>
            </thead>
            <tbody>
              @foreach($categories as $cat)
              <tr id="row{{ $cat->id }}">
                <td class="text-muted" style="font-size:13px;">{{ $loop->iteration }}</td>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:{{ $cat->color ?? '#1a9e4a' }}20;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                      <i class="{{ $cat->icon ?? 'fas fa-tag' }}" style="color:{{ $cat->color ?? '#1a9e4a' }};font-size:16px;"></i>
                    </div>
                    <div>
                      <div class="fw-semibold bengali-text" style="font-size:14px;">{{ $cat->name_bn }}</div>
                      <div class="text-muted" style="font-size:12px;">{{ $cat->name_en }}</div>
                    </div>
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary">{{ $cat->products_count }}</span>
                </td>
                <td class="text-center">
                  <form method="POST" action="{{ route('admin.categories.toggle', $cat) }}" class="d-inline">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="badge border-0 {{ $cat->status ? 'bg-success' : 'bg-danger' }}"
                            style="cursor:pointer;font-size:11px;padding:5px 10px;"
                            title="{{ $cat->status ? 'Deactivate' : 'Activate' }}">
                      {{ $cat->status
                          ? (app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active')
                          : (app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Inactive') }}
                    </button>
                  </form>
                </td>
                <td class="text-center">
                  <div class="d-flex gap-1 justify-content-center">
                    {{-- Edit button --}}
                    <button class="btn btn-outline-primary btn-sm"
                            onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name_bn) }}', '{{ addslashes($cat->name_en) }}', '{{ $cat->icon }}', '{{ $cat->color }}', {{ $cat->sort_order }})"
                            title="{{ app()->getLocale()==='bn' ? 'সম্পাদনা' : 'Edit' }}">
                      <i class="fas fa-edit"></i>
                    </button>

                    {{-- Delete button --}}
                    @if($cat->products_count == 0)
                    <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="d-inline"
                          onsubmit="return confirm('{{ app()->getLocale()==='bn' ? 'এই ক্যাটাগরি ডিলিট করবেন?' : 'Delete this category?' }}')">
                      @csrf @method('DELETE')
                      <button class="btn btn-outline-danger btn-sm" title="{{ app()->getLocale()==='bn' ? 'ডিলিট' : 'Delete' }}">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    @else
                    <button class="btn btn-outline-secondary btn-sm" disabled
                            title="{{ app()->getLocale()==='bn' ? 'পণ্য আছে, ডিলিট করা যাবে না' : 'Has products, cannot delete' }}">
                      <i class="fas fa-trash"></i>
                    </button>
                    @endif
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ── EDIT MODAL ── --}}
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title bengali-text">
          <i class="fas fa-edit me-2"></i>
          {{ app()->getLocale()==='bn' ? 'ক্যাটাগরি সম্পাদনা' : 'Edit Category' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="editForm">
        @csrf @method('PUT')
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label bengali-text">
              {{ app()->getLocale()==='bn' ? 'ক্যাটাগরি নাম (বাংলা) *' : 'Category Name (Bengali) *' }}
            </label>
            <input type="text" class="form-control" name="name_bn" id="edit_name_bn" required>
          </div>

          <div class="mb-3">
            <label class="form-label">
              {{ app()->getLocale()==='bn' ? 'ক্যাটাগরি নাম (English) *' : 'Category Name (English) *' }}
            </label>
            <input type="text" class="form-control" name="name_en" id="edit_name_en" required>
          </div>

          <div class="mb-3">
            <label class="form-label">{{ app()->getLocale()==='bn' ? 'আইকন' : 'Icon' }}</label>
            <div class="input-group">
              <span class="input-group-text" id="editIconPreview"><i class="fas fa-tag"></i></span>
              <input type="text" class="form-control" name="icon" id="edit_icon"
                     oninput="updateEditIconPreview(this.value)">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">{{ app()->getLocale()==='bn' ? 'রঙ' : 'Color' }}</label>
            <input type="color" class="form-control form-control-color w-100" name="color" id="edit_color">
          </div>

          <div class="mb-3">
            <label class="form-label">{{ app()->getLocale()==='bn' ? 'সাজানোর ক্রম' : 'Sort Order' }}</label>
            <input type="number" class="form-control" name="sort_order" id="edit_sort_order" min="0">
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>
            {{ app()->getLocale()==='bn' ? 'আপডেট করুন' : 'Update' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// Icon preview on add form
function updateIconPreview(val) {
    const el = document.getElementById('iconPreview');
    if (el) el.innerHTML = `<i class="${val || 'fas fa-tag'}"></i>`;
}

// Sync color picker with text input
document.querySelector('input[type="color"]')?.addEventListener('input', function() {
    const txt = document.querySelector('input[name="color_text"]');
    if (txt) txt.value = this.value;
});

// Edit modal
function openEditModal(id, name_bn, name_en, icon, color, sort_order) {
    const route = `{{ url('admin/categories') }}/${id}`;
    document.getElementById('editForm').action = route;
    document.getElementById('edit_name_bn').value   = name_bn;
    document.getElementById('edit_name_en').value   = name_en;
    document.getElementById('edit_icon').value      = icon;
    document.getElementById('edit_color').value     = color || '#1a9e4a';
    document.getElementById('edit_sort_order').value= sort_order;
    updateEditIconPreview(icon);
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function updateEditIconPreview(val) {
    const el = document.getElementById('editIconPreview');
    if (el) el.innerHTML = `<i class="${val || 'fas fa-tag'}"></i>`;
}
</script>
@endpush
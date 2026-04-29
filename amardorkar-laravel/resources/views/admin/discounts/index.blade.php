@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'ছাড় ব্যবস্থাপনা' : 'Discount Management')
@section('page-title', app()->getLocale()==='bn' ? 'ছাড় ব্যবস্থাপনা' : 'Discount Management')

@section('content')

<div class="mb-3 d-flex justify-content-end">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscountModal">
    <i class="fas fa-plus me-2"></i>{{ app()->getLocale()==='bn' ? 'নতুন ছাড় যোগ করুন' : 'Add New Discount' }}
  </button>
</div>

<div class="card animate-fadeInUp">
  <div class="card-header">
    <h5 class="card-title bengali-text"><i class="fas fa-percent me-2"></i>{{ app()->getLocale()==='bn' ? 'সকল ছাড়' : 'All Discounts' }}</h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Product' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'মূল মূল্য' : 'Original' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'ছাড়ের মূল্য' : 'Discount' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'ছাড়%' : 'Disc. %' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'শেষ তারিখ' : 'End Date' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($discounts as $discount)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                @if($discount->product?->image_path)
                  <img src="{{ $discount->product->image_url }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px">
                @endif
                <div>
                  <strong class="bengali-text">{{ $discount->product?->name }}</strong><br>
                  <small class="text-muted">{{ $discount->product?->product_type }}</small>
                </div>
              </div>
            </td>
            <td><strong>৳{{ number_format($discount->original_price) }}</strong></td>
            <td><strong class="text-success">৳{{ number_format($discount->discount_price) }}</strong></td>
            <td>
              <span class="badge bg-warning text-dark">{{ number_format($discount->discount_percentage,1) }}%</span>
            </td>
            <td>
              <span class="status-badge {{ $discount->is_active ? 'status-delivered' : 'status-cancelled' }}">
                {{ $discount->is_active ? (app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active') : (app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Inactive') }}
              </span>
            </td>
            <td>{{ $discount->end_date->format('M d, Y') }}</td>
            <td>
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-warning edit-discount-btn"
                  data-id="{{ $discount->id }}"
                  data-original="{{ $discount->original_price }}"
                  data-discount="{{ $discount->discount_price }}"
                  data-start="{{ $discount->start_date->format('Y-m-d\TH:i') }}"
                  data-end="{{ $discount->end_date->format('Y-m-d\TH:i') }}"
                  data-active="{{ $discount->is_active ? 1 : 0 }}">
                  <i class="fas fa-edit"></i>
                </button>
                <form method="POST" action="{{ route('admin.discounts.toggle', $discount) }}" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-sm {{ $discount->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="Toggle">
                    <i class="fas {{ $discount->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                  </button>
                </form>
                <button class="btn btn-sm btn-outline-danger delete-discount-btn" data-id="{{ $discount->id }}">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center py-4 text-muted bengali-text">{{ app()->getLocale()==='bn' ? 'কোন ছাড় পাওয়া যায়নি' : 'No discounts found' }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($discounts->hasPages())
    <div class="p-3 border-top">{{ $discounts->links() }}</div>
    @endif
  </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addDiscountModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.discounts.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title bengali-text">{{ app()->getLocale()==='bn' ? 'নতুন ছাড় যোগ করুন' : 'Add Discount' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'পণ্য নির্বাচন *' : 'Select Product *' }}</label>
                <select class="form-select" name="product_id" required>
                  <option value="">{{ app()->getLocale()==='bn' ? 'পণ্য নির্বাচন করুন' : 'Select Product' }}</option>
                  @foreach($products as $p)
                  <option value="{{ $p->id }}">{{ $p->name_en }} ({{ $p->product_type }})</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'মূল মূল্য *' : 'Original Price *' }}</label>
                <input type="number" class="form-control" name="original_price" step="0.01" required placeholder="0.00">
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড়ের মূল্য *' : 'Discount Price *' }}</label>
                <input type="number" class="form-control" name="discount_price" step="0.01" required placeholder="0.00">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'শুরু তারিখ *' : 'Start Date *' }}</label>
                <input type="datetime-local" class="form-control" name="start_date" value="{{ now()->format('Y-m-d\TH:i') }}" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'শেষ তারিখ *' : 'End Date *' }}</label>
                <input type="datetime-local" class="form-control" name="end_date" value="{{ now()->addDays(30)->format('Y-m-d\TH:i') }}" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>{{ app()->getLocale()==='bn' ? 'যোগ করুন' : 'Add' }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editDiscountModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="editDiscountForm">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড় সম্পাদনা' : 'Edit Discount' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'মূল মূল্য *' : 'Original Price *' }}</label>
                <input type="number" class="form-control" name="original_price" id="editOriginalPrice" step="0.01" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড়ের মূল্য *' : 'Discount Price *' }}</label>
                <input type="number" class="form-control" name="discount_price" id="editDiscountPrice" step="0.01" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">{{ app()->getLocale()==='bn' ? 'শুরু তারিখ' : 'Start Date' }}</label>
                <input type="datetime-local" class="form-control" name="start_date" id="editStartDate" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">{{ app()->getLocale()==='bn' ? 'শেষ তারিখ' : 'End Date' }}</label>
                <input type="datetime-local" class="form-control" name="end_date" id="editEndDate" required>
              </div>
            </div>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive">
            <label class="form-check-label bengali-text" for="editIsActive">{{ app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active' }}</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>{{ app()->getLocale()==='bn' ? 'আপডেট' : 'Update' }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteDiscountModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড় মুছে ফেলুন' : 'Delete Discount' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="deleteDiscountForm">
        @csrf @method('DELETE')
        <div class="modal-body">
          <p class="text-danger bengali-text"><i class="fas fa-exclamation-triangle me-2"></i>{{ app()->getLocale()==='bn' ? 'এই ছাড়টি মুছে ফেলবেন?' : 'Delete this discount?' }}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}</button>
          <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-2"></i>{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.edit-discount-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('editOriginalPrice').value = btn.dataset.original;
    document.getElementById('editDiscountPrice').value = btn.dataset.discount;
    document.getElementById('editStartDate').value = btn.dataset.start;
    document.getElementById('editEndDate').value = btn.dataset.end;
    document.getElementById('editIsActive').checked = btn.dataset.active == '1';
    document.getElementById('editDiscountForm').action = `/admin/discounts/${btn.dataset.id}`;
    new bootstrap.Modal(document.getElementById('editDiscountModal')).show();
  });
});
document.querySelectorAll('.delete-discount-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('deleteDiscountForm').action = `/admin/discounts/${btn.dataset.id}`;
    new bootstrap.Modal(document.getElementById('deleteDiscountModal')).show();
  });
});
</script>
@endpush

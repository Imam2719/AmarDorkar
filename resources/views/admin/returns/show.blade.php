@extends('layouts.admin')
@section('title', (app()->getLocale()==='bn' ? 'রিটার্ন' : 'Return') . ' #' . $returnRequest->return_number)

@section('content')
<div class="adm-page-wrap" style="max-width:1000px;">

  {{-- Validation errors only (layout already handles session success/error) --}}
  @if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show mb-3" style="border-radius:10px;">
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  {{-- Back + Header --}}
  <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>{{ app()->getLocale() === 'bn' ? 'ফিরে যান' : 'Back' }}
    </a>
    <div>
      <h5 class="fw-bold mb-0">
        <i class="fas fa-undo-alt me-2 text-warning"></i>
        {{ app()->getLocale() === 'bn' ? 'রিটার্ন' : 'Return' }} #{{ $returnRequest->return_number }}
      </h5>
      <small class="text-muted">{{ $returnRequest->created_at->format('d M Y, h:i A') }}</small>
    </div>
    <span class="ms-auto badge bg-{{ $returnRequest->status_badge }} px-3 py-2 fs-6">
      {{ app()->getLocale() === 'bn' ? $returnRequest->status_label_bn : $returnRequest->status_label }}
    </span>
  </div>

  <div class="row g-4">

    {{-- LEFT: Customer info + Request details + Images --}}
    <div class="col-12 col-lg-7">

      {{-- Customer & Order Info --}}
      <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-header bg-white fw-semibold py-3 border-bottom" style="border-radius:12px 12px 0 0;">
          <i class="fas fa-user-circle me-2 text-info"></i>
          {{ app()->getLocale() === 'bn' ? 'গ্রাহক ও অর্ডার তথ্য' : 'Customer & Order Info' }}
        </div>
        <div class="card-body p-3" style="font-size:.88rem;">
          <div class="row g-2">
            <div class="col-5 text-muted">{{ app()->getLocale() === 'bn' ? 'গ্রাহক নাম' : 'Customer' }}</div>
            <div class="col-7 fw-semibold">{{ $returnRequest->user->name ?? '—' }}</div>
            <div class="col-5 text-muted">{{ app()->getLocale() === 'bn' ? 'ইমেইল' : 'Email' }}</div>
            <div class="col-7">{{ $returnRequest->user->email ?? '—' }}</div>
            <div class="col-5 text-muted">{{ app()->getLocale() === 'bn' ? 'মোবাইল' : 'Mobile' }}</div>
            <div class="col-7">{{ $returnRequest->user->mobile ?? $returnRequest->order->customer_mobile ?? '—' }}</div>
            <div class="col-5 text-muted">{{ app()->getLocale() === 'bn' ? 'অর্ডার নম্বর' : 'Order #' }}</div>
            <div class="col-7">
              <a href="{{ route('admin.orders.show', $returnRequest->order_id) }}" class="fw-semibold text-decoration-none">
                {{ $returnRequest->order->order_number }}
              </a>
            </div>
            <div class="col-5 text-muted">{{ app()->getLocale() === 'bn' ? 'অর্ডার মোট' : 'Order Total' }}</div>
            <div class="col-7 text-success fw-bold">৳{{ number_format($returnRequest->order->total_amount, 2) }}</div>
            <div class="col-5 text-muted">{{ app()->getLocale() === 'bn' ? 'অর্ডারের তারিখ' : 'Order Date' }}</div>
            <div class="col-7">{{ $returnRequest->order->created_at->format('d M Y') }}</div>
          </div>

          {{-- Order items --}}
          <div class="mt-3 border-top pt-3">
            <div class="fw-semibold mb-2 text-muted" style="font-size:.82rem;">
              {{ app()->getLocale() === 'bn' ? 'অর্ডার আইটেম' : 'Order Items' }}
            </div>
            @foreach($returnRequest->order->items as $item)
            <div class="d-flex gap-2 align-items-center mb-2">
              @php $img = $item->product?->images->first(); @endphp
              @if($img)
              <img src="{{ route('image.product', $img->id) }}" style="width:40px;height:40px;object-fit:cover;border-radius:8px;">
              @else
              <div style="width:40px;height:40px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-box text-muted"></i>
              </div>
              @endif
              <div>
                <div style="font-size:.85rem;font-weight:600;">{{ $item->product_name }}</div>
                <div class="text-muted" style="font-size:.78rem;">৳{{ number_format($item->price,2) }} × {{ $item->quantity }}</div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Return details --}}
      <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-header bg-white fw-semibold py-3 border-bottom" style="border-radius:12px 12px 0 0;">
          <i class="fas fa-clipboard-list me-2 text-warning"></i>
          {{ app()->getLocale() === 'bn' ? 'রিটার্নের কারণ ও বিবরণ' : 'Return Reason & Description' }}
        </div>
        <div class="card-body p-3" style="font-size:.88rem;">
          <div class="mb-2">
            <span class="text-muted">{{ app()->getLocale() === 'bn' ? 'কারণ:' : 'Reason:' }}</span>
            <span class="ms-2 fw-semibold">{{ app()->getLocale() === 'bn' ? $returnRequest->reason_label_bn : $returnRequest->reason_label }}</span>
          </div>
          <div class="p-3" style="background:#f8fafc;border-radius:8px;line-height:1.7;">
            {{ $returnRequest->description }}
          </div>
        </div>
      </div>

      {{-- Submitted images --}}
      @if($returnRequest->images->isNotEmpty())
      <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header bg-white fw-semibold py-3 border-bottom" style="border-radius:12px 12px 0 0;">
          <i class="fas fa-images me-2 text-primary"></i>
          {{ app()->getLocale() === 'bn' ? 'গ্রাহকের সংযুক্ত ছবি' : 'Customer Submitted Images' }}
          <span class="badge bg-secondary ms-2">{{ $returnRequest->images->count() }}</span>
        </div>
        <div class="card-body p-3">
          <div class="d-flex flex-wrap gap-3">
            @foreach($returnRequest->images as $img)
            <a href="{{ route('image.return', $img->id) }}" target="_blank" title="{{ app()->getLocale() === 'bn' ? 'বড় করে দেখুন' : 'Click to expand' }}">
              <img src="{{ route('image.return', $img->id) }}"
                   style="width:100px;height:100px;object-fit:cover;border-radius:10px;border:2px solid #e5e7eb;transition:all .2s;"
                   onmouseover="this.style.transform='scale(1.06)';this.style.borderColor='#6366f1';"
                   onmouseout="this.style.transform='scale(1)';this.style.borderColor='#e5e7eb';">
            </a>
            @endforeach
          </div>
        </div>
      </div>
      @endif
    </div>

    {{-- RIGHT: Admin action panel --}}
    <div class="col-12 col-lg-5">
      <div class="card border-0 shadow-sm sticky-top" style="border-radius:12px;top:80px;">
        <div class="card-header bg-white fw-semibold py-3 border-bottom" style="border-radius:12px 12px 0 0;">
          <i class="fas fa-cogs me-2 text-warning"></i>
          {{ app()->getLocale() === 'bn' ? 'অ্যাডমিন অ্যাকশন' : 'Admin Action' }}
        </div>
        <div class="card-body p-3 p-md-4">
          <form method="POST" action="{{ route('admin.returns.update', $returnRequest) }}">
            @csrf
            @method('PUT')

            {{-- Status --}}
            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:.88rem;">
                {{ app()->getLocale() === 'bn' ? 'স্ট্যাটাস পরিবর্তন' : 'Update Status' }}
              </label>
              <select name="status" class="form-select form-select-sm @error('status') is-invalid @enderror">
                @foreach([
                  'pending'          => ['Pending','মুলতবি'],
                  'under_review'     => ['Under Review','পর্যালোচনাধীন'],
                  'approved'         => ['Approved','অনুমোদিত'],
                  'rejected'         => ['Rejected','প্রত্যাখ্যাত'],
                  'refund_initiated' => ['Refund Initiated','রিফান্ড শুরু'],
                  'refunded'         => ['Refunded','রিফান্ড সম্পন্ন'],
                ] as $val => $labels)
                <option value="{{ $val }}" {{ $returnRequest->status === $val ? 'selected' : '' }}>
                  {{ app()->getLocale() === 'bn' ? $labels[1] : $labels[0] }}
                </option>
                @endforeach
              </select>
              @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Refund amount --}}
            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:.88rem;">
                {{ app()->getLocale() === 'bn' ? 'রিফান্ড পরিমাণ (৳)' : 'Refund Amount (৳)' }}
              </label>
              <input type="number" name="refund_amount" step="0.01" min="0"
                value="{{ old('refund_amount', $returnRequest->refund_amount) }}"
                class="form-control form-control-sm @error('refund_amount') is-invalid @enderror"
                placeholder="{{ number_format($returnRequest->order->total_amount, 2) }}">
              <div class="form-text" style="font-size:.78rem;">
                {{ app()->getLocale() === 'bn' ? 'অর্ডার মোট: ৳' : 'Order total: ৳' }}{{ number_format($returnRequest->order->total_amount, 2) }}
              </div>
              @error('refund_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Refund method --}}
            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:.88rem;">
                {{ app()->getLocale() === 'bn' ? 'রিফান্ড পদ্ধতি' : 'Refund Method' }}
              </label>
              <select name="refund_method" class="form-select form-select-sm">
                <option value="">— {{ app()->getLocale() === 'bn' ? 'বেছে নিন' : 'Select' }} —</option>
                @foreach(['bKash','Nagad','Rocket','Bank Transfer','Original Payment Method','Cash'] as $m)
                <option value="{{ $m }}" {{ $returnRequest->refund_method === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
              </select>
            </div>

            {{-- Admin notes --}}
            <div class="mb-4">
              <label class="form-label fw-semibold" style="font-size:.88rem;">
                {{ app()->getLocale() === 'bn' ? 'অ্যাডমিন নোট (গ্রাহক দেখতে পাবে)' : 'Admin Notes (visible to customer)' }}
              </label>
              <textarea name="admin_notes" rows="4"
                class="form-control form-control-sm @error('admin_notes') is-invalid @enderror"
                placeholder="{{ app()->getLocale() === 'bn' ? 'গ্রাহকের জন্য বার্তা লিখুন...' : 'Write a message for the customer...' }}"
                >{{ old('admin_notes', $returnRequest->admin_notes) }}</textarea>
              @error('admin_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-semibold">
              <i class="fas fa-save me-2"></i>{{ app()->getLocale() === 'bn' ? 'আপডেট করুন' : 'Save Changes' }}
            </button>
          </form>

          {{-- Quick action buttons --}}
          <div class="border-top mt-4 pt-3">
            <div class="text-muted fw-semibold mb-2" style="font-size:.8rem;">
              {{ app()->getLocale() === 'bn' ? 'দ্রুত অ্যাকশন' : 'Quick Actions' }}
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <form method="POST" action="{{ route('admin.returns.update', $returnRequest) }}">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="approved">
                <input type="hidden" name="admin_notes" value="{{ $returnRequest->admin_notes }}">
                <input type="hidden" name="refund_amount" value="{{ $returnRequest->refund_amount }}">
                <input type="hidden" name="refund_method" value="{{ $returnRequest->refund_method }}">
                <button type="submit" class="btn btn-success btn-sm">
                  <i class="fas fa-check me-1"></i>{{ app()->getLocale() === 'bn' ? 'অনুমোদন' : 'Approve' }}
                </button>
              </form>
              <form method="POST" action="{{ route('admin.returns.update', $returnRequest) }}">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <input type="hidden" name="admin_notes" value="{{ $returnRequest->admin_notes }}">
                <input type="hidden" name="refund_amount" value="{{ $returnRequest->refund_amount }}">
                <input type="hidden" name="refund_method" value="{{ $returnRequest->refund_method }}">
                <button type="submit" class="btn btn-danger btn-sm">
                  <i class="fas fa-times me-1"></i>{{ app()->getLocale() === 'bn' ? 'প্রত্যাখ্যান' : 'Reject' }}
                </button>
              </form>
              <form method="POST" action="{{ route('admin.returns.destroy', $returnRequest) }}"
                onsubmit="return confirm('{{ app()->getLocale()==='bn' ? 'এই রিটার্ন রিকোয়েস্ট মুছে ফেলবেন?' : 'Delete this return request?' }}')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                  <i class="fas fa-trash me-1"></i>{{ app()->getLocale() === 'bn' ? 'মুছুন' : 'Delete' }}
                </button>
              </form>
            </div>
          </div>

          {{-- Resolved info --}}
          @if($returnRequest->resolved_at)
          <div class="mt-3 text-muted" style="font-size:.78rem;">
            <i class="fas fa-calendar-check me-1"></i>
            {{ app()->getLocale() === 'bn' ? 'সমাধান হয়েছে:' : 'Resolved:' }}
            {{ $returnRequest->resolved_at->format('d M Y, h:i A') }}
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

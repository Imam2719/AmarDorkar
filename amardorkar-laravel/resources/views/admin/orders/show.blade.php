@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'অর্ডার বিবরণ' : 'Order Details')
@section('page-title', app()->getLocale()==='bn' ? 'অর্ডার বিবরণ' : 'Order Details')

@section('content')
@php
  $statusBn  = ['pending'=>'মুলতবি','confirmed'=>'নিশ্চিত','processing'=>'প্রক্রিয়াকরণ','shipped'=>'পাঠানো','delivered'=>'ডেলিভার্ড','cancelled'=>'বাতিল'];
  $paymentBn = ['pending'=>'মুলতবি','paid'=>'পরিশোধিত','failed'=>'ব্যর্থ','refunded'=>'ফেরত'];
  $payStatus = $order->payment_status ?? 'pending';
@endphp

<div class="row">
  {{-- Order + Customer info --}}
  <div class="col-md-8 mb-4">
    <div class="card animate-fadeInUp">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title bengali-text">
          <i class="fas fa-receipt me-2"></i>{{ app()->getLocale()==='bn' ? 'অর্ডার' : 'Order' }} #{{ $order->order_number }}
        </h5>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left me-1"></i>{{ app()->getLocale()==='bn' ? 'ফিরে যান' : 'Back' }}
        </a>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <div class="col-md-6">
            <h6 class="bengali-text mb-2">{{ app()->getLocale()==='bn' ? 'গ্রাহকের তথ্য' : 'Customer Info' }}</h6>
            <table class="table table-sm table-borderless">
              <tr>
                <td class="text-muted bengali-text">{{ app()->getLocale()==='bn' ? 'নাম:' : 'Name:' }}</td>
                <td><strong class="bengali-text">{{ $order->customer_name }}</strong></td>
              </tr>
              <tr>
                <td class="text-muted">{{ app()->getLocale()==='bn' ? 'মোবাইল:' : 'Mobile:' }}</td>
                <td>{{ $order->customer_mobile }}</td>
              </tr>
              <tr>
                <td class="text-muted">{{ app()->getLocale()==='bn' ? 'ইমেইল:' : 'Email:' }}</td>
                <td>{{ $order->customer_email }}</td>
              </tr>
              <tr>
                <td class="text-muted">{{ app()->getLocale()==='bn' ? 'ঠিকানা:' : 'Address:' }}</td>
                <td class="bengali-text">{{ $order->customer_address }}</td>
              </tr>
              <tr>
                <td class="text-muted">{{ app()->getLocale()==='bn' ? 'লোকেশন:' : 'Location:' }}</td>
                <td class="bengali-text">{{ $order->shipping_location }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6 class="bengali-text mb-2">{{ app()->getLocale()==='bn' ? 'অর্ডারের তথ্য' : 'Order Info' }}</h6>
            <table class="table table-sm table-borderless">
              <tr>
                <td class="text-muted">{{ app()->getLocale()==='bn' ? 'তারিখ:' : 'Date:' }}</td>
                <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
              </tr>
              <tr>
                <td class="text-muted">{{ app()->getLocale()==='bn' ? 'অবস্থা:' : 'Status:' }}</td>
                <td><span class="status-badge status-{{ $order->status }}">{{ app()->getLocale()==='bn' ? ($statusBn[$order->status]??$order->status) : ucfirst($order->status) }}</span></td>
              </tr>
              <tr>
                <td class="text-muted">{{ app()->getLocale()==='bn' ? 'পেমেন্ট:' : 'Payment:' }}</td>
                <td>
                  <span class="status-badge payment-{{ $payStatus }}">
                    {{ app()->getLocale()==='bn' ? ($paymentBn[$payStatus]??$payStatus) : ucfirst($payStatus) }}
                  </span>
                </td>
              </tr>
              <tr>
                <td class="text-muted">{{ app()->getLocale()==='bn' ? 'পেমেন্ট পদ্ধতি:' : 'Method:' }}</td>
                <td>{{ strtoupper($order->payment_method) }}</td>
              </tr>
            </table>
          </div>
        </div>

        {{-- Items --}}
        <h6 class="bengali-text mb-3">{{ app()->getLocale()==='bn' ? 'পণ্যের তালিকা' : 'Order Items' }}</h6>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Product' }}</th>
                <th style="width:60px;"></th>
                <th>{{ app()->getLocale()==='bn' ? 'দাম' : 'Price' }}</th>
                <th>{{ app()->getLocale()==='bn' ? 'পরিমাণ' : 'Qty' }}</th>
                <th>{{ app()->getLocale()==='bn' ? 'সাবটোটাল' : 'Subtotal' }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($order->items as $item)
              <tr>
                <td style="padding:8px;">
                  @php
                    $adminImgUrl = $item->product ? ($item->product->image_url ?? null) : null;
                  @endphp
                  @if($adminImgUrl && !str_ends_with($adminImgUrl, 'no-image.png'))
                    <img src="{{ $adminImgUrl }}"
                         style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                  @else
                    <div style="width:48px;height:48px;background:var(--bg);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                      <i class="fas fa-box" style="color:#94a3b8;font-size:16px;"></i>
                    </div>
                  @endif
                </td>
                <td>
                  <strong class="bengali-text">{{ $item->product_name }}</strong>
                  @if($item->product_size ?? false)
                    <br><small class="text-muted">{{ $item->product_size }}</small>
                  @endif
                  @if($item->is_discounted ?? false)
                    <span class="discount-badge ms-1">{{ app()->getLocale()==='bn' ? 'ছাড়' : 'Disc.' }}</span>
                  @endif
                </td>
                <td>৳{{ number_format($item->price, 2) }}</td>
                <td>{{ $item->quantity }}</td>
                <td><strong>৳{{ number_format($item->subtotal, 2) }}</strong></td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="text-end bengali-text">{{ app()->getLocale()==='bn' ? 'সাবটোটাল:' : 'Subtotal:' }}</td>
                <td>৳{{ number_format($order->subtotal, 2) }}</td>
              </tr>
              <tr>
                <td colspan="3" class="text-end bengali-text">{{ app()->getLocale()==='bn' ? 'শিপিং চার্জ:' : 'Shipping:' }}</td>
                <td>৳{{ number_format($order->shipping_charge, 2) }}</td>
              </tr>
              <tr>
                <td colspan="3" class="text-end"><strong class="bengali-text">{{ app()->getLocale()==='bn' ? 'মোট:' : 'Total:' }}</strong></td>
                <td><strong class="text-success fs-5">৳{{ number_format($order->total_amount, 2) }}</strong></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Actions sidebar --}}
  <div class="col-md-4 mb-4">
    {{-- Update Status --}}
    <div class="card animate-fadeInUp mb-3">
      <div class="card-header">
        <h6 class="card-title bengali-text mb-0">
          <i class="fas fa-edit me-2"></i>{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস আপডেট' : 'Update Status' }}
        </h6>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.orders.update', $order) }}">
          @csrf @method('PUT')
          <input type="hidden" name="redirect_to" value="view">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'অর্ডার স্ট্যাটাস' : 'Order Status' }}</label>
            <select class="form-select" name="status">
              @foreach(['pending'=>'মুলতবি','confirmed'=>'নিশ্চিত','processing'=>'প্রক্রিয়াকরণ','shipped'=>'পাঠানো','delivered'=>'ডেলিভার্ড','cancelled'=>'বাতিল'] as $val=>$bn)
              <option value="{{ $val }}" {{ $order->status==$val ? 'selected' : '' }}>
                {{ app()->getLocale()==='bn' ? $bn : ucfirst($val) }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'পেমেন্ট স্ট্যাটাস' : 'Payment Status' }}</label>
            <select class="form-select" name="payment_status">
              @foreach(['pending'=>'মুলতবি','paid'=>'পরিশোধিত','failed'=>'ব্যর্থ','refunded'=>'ফেরত'] as $val=>$bn)
              <option value="{{ $val }}" {{ $payStatus==$val ? 'selected' : '' }}>
                {{ app()->getLocale()==='bn' ? $bn : ucfirst($val) }}
              </option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-save me-2"></i>{{ app()->getLocale()==='bn' ? 'আপডেট করুন' : 'Update' }}
          </button>
        </form>
      </div>
    </div>

    {{-- Sell / Delete --}}
    <div class="card animate-fadeInUp">
      <div class="card-header">
        <h6 class="card-title bengali-text mb-0"><i class="fas fa-cog me-2"></i>{{ app()->getLocale()==='bn' ? 'অন্যান্য' : 'Actions' }}</h6>
      </div>
      <div class="card-body d-grid gap-2">
        @if(!in_array($order->status, ['delivered','cancelled']))
        <form method="POST" action="{{ route('admin.orders.sell', $order) }}"
          onsubmit="return confirm('{{ app()->getLocale()==='bn' ? 'বিক্রয় ইতিহাসে স্থানান্তর করবেন?' : 'Move to sales history?' }}')">
          @csrf
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-check-circle me-2"></i>{{ app()->getLocale()==='bn' ? 'বিক্রয় করুন' : 'Mark as Sold' }}
          </button>
        </form>
        @endif
        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}"
          onsubmit="return confirm('{{ app()->getLocale()==='bn' ? 'স্থায়ীভাবে মুছে ফেলবেন?' : 'Permanently delete?' }}')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-outline-danger w-100">
            <i class="fas fa-trash me-2"></i>{{ app()->getLocale()==='bn' ? 'মুছে ফেলুন' : 'Delete Order' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
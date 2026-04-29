@extends('layouts.admin')
@section('title', 'Purchase History - ' . $user->name)

@section('content')
<div class="container-fluid py-4">

  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i> Back
    </a>
    <div>
      <h4 class="mb-0 fw-bold">{{ $user->name }}'s Purchase History</h4>
      <small class="text-muted">{{ $user->email }} @if($user->mobile) · {{ $user->mobile }} @endif</small>
    </div>
  </div>

  {{-- Summary Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm text-center p-3">
        <div class="fw-bold fs-4 text-success">{{ $orders->count() }}</div>
        <div class="small text-muted">Total Orders</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm text-center p-3">
        <div class="fw-bold fs-4 text-warning">{{ $orders->where('status','pending')->count() }}</div>
        <div class="small text-muted">Pending</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm text-center p-3">
        <div class="fw-bold fs-4 text-primary">{{ $orders->where('status','delivered')->count() }}</div>
        <div class="small text-muted">Delivered</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm text-center p-3">
        <div class="fw-bold fs-4 text-success">৳{{ number_format($totalSpent, 0) }}</div>
        <div class="small text-muted">Total Spent</div>
      </div>
    </div>
  </div>

  {{-- Orders Table --}}
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold py-3">
      <i class="fas fa-shopping-bag me-2 text-success"></i> All Orders
    </div>
    <div class="card-body p-0">
      @if($orders->isEmpty())
        <div class="text-center py-5 text-muted">
          <i class="fas fa-box-open fa-2x mb-2 d-block opacity-50"></i>
          No orders found for this user.
        </div>
      @else
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Order #</th>
              <th>Date</th>
              <th>Items</th>
              <th class="text-end">Total</th>
              <th class="text-center">Payment</th>
              <th class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orders as $order)
            @php
              $statusColors = [
                'pending'=>'warning','confirmed'=>'info','processing'=>'primary',
                'shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'
              ];
              $badge = $statusColors[$order->status] ?? 'secondary';
            @endphp
            <tr>
              <td><span class="fw-bold text-success small">{{ $order->order_number }}</span></td>
              <td><span class="small text-muted">{{ $order->created_at->format('d M Y, H:i') }}</span></td>
              <td>
                <span class="small">{{ $order->items->count() }} item(s)</span>
                @if($order->items->count())
                  <div class="text-muted" style="font-size:11px;">
                    {{ $order->items->take(2)->map(fn($i) => $i->product_name)->join(', ') }}
                    @if($order->items->count() > 2) +{{ $order->items->count()-2 }} more @endif
                  </div>
                @endif
              </td>
              <td class="text-end fw-bold">৳{{ number_format($order->total_amount, 0) }}</td>
              <td class="text-center">
                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-secondary' }} bg-opacity-75 small">
                  {{ ucfirst($order->payment_status ?? 'pending') }}
                </span>
              </td>
              <td class="text-center">
                <span class="badge bg-{{ $badge }} bg-opacity-75 small">
                  {{ ucfirst($order->status) }}
                </span>
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
@endsection

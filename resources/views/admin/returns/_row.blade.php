{{-- Partial: single return request table row --}}
<tr>
  <td><strong style="color:var(--text)">{{ $ret->return_number }}</strong></td>
  <td>
    <div style="color:var(--text)">{{ $ret->user->name ?? '—' }}</div>
    <small style="color:var(--muted)">{{ $ret->user->email ?? '' }}</small>
  </td>
  <td>
    <a href="{{ route('admin.orders.show', $ret->order_id) }}"
       style="color:var(--primary);font-weight:600;font-size:13px;text-decoration:none">
      {{ $ret->order->order_number ?? '—' }}
    </a>
  </td>
  <td style="font-size:12px;color:var(--muted)">
    {{ app()->getLocale() === 'bn' ? $ret->reason_label_bn : $ret->reason_label }}
  </td>
  <td>
    <span class="rr-badge rr-badge-{{ $ret->status }}">
      {{ app()->getLocale() === 'bn' ? $ret->status_label_bn : $ret->status_label }}
    </span>
  </td>
  <td style="color:var(--muted);font-size:12px">
    {{ $ret->created_at->format('d M Y') }}
  </td>
  <td style="text-align:right">
    <div class="rr-action-btns" style="justify-content:flex-end">
      <a href="{{ route('admin.returns.show', $ret) }}" class="rr-btn rr-btn-view">
        <i class="fas fa-eye"></i>{{ app()->getLocale()==='bn' ? 'দেখুন' : 'View' }}
      </a>
    </div>
  </td>
</tr>

{{-- Partial: single product table row --}}
<tr>
  <td>
    @php $img = $p->image_url; @endphp
    @if($img && !str_contains($img,'no-image'))
      <img src="{{ $img }}" class="pi-thumb" alt="{{ $p->name }}">
    @else
      <div class="pi-thumb-placeholder"><i class="fas fa-image"></i></div>
    @endif
  </td>
  <td>
    <strong style="color:var(--text)">{{ $p->name }}</strong>
    <br><small style="color:var(--muted)">{{ Str::limit($p->description, 50) }}</small>
  </td>
  <td>
    <span class="pi-badge pi-badge-type">{{ $p->product_type ?: '—' }}</span>
  </td>
  <td>
    <span class="pi-badge pi-badge-size">{{ $p->product_size ?: '—' }}</span>
  </td>
  <td><strong style="color:var(--text)">৳{{ number_format($p->price, 2) }}</strong></td>
  <td>
    @if($p->activeDiscount)
      <span class="pi-badge pi-badge-disc">{{ number_format($p->activeDiscount->discount_percentage, 1) }}% OFF</span>
      <br><small style="color:var(--muted)">৳{{ number_format($p->activeDiscount->discount_price) }}</small>
    @else
      <span style="color:var(--muted-2)">—</span>
    @endif
  </td>
  <td>
    @if($p->status)
      <span class="pi-badge pi-badge-active">{{ app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active' }}</span>
    @else
      <span class="pi-badge pi-badge-inactive">{{ app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Inactive' }}</span>
    @endif
  </td>
  <td>
    <div class="pi-action-btns">
      <a href="{{ route('admin.products.show', $p) }}"
         class="pi-btn-icon pi-btn-view"
         title="{{ app()->getLocale()==='bn' ? 'দেখুন' : 'View' }}">
        <i class="fas fa-eye"></i>
      </a>
      <a href="{{ route('admin.products.edit', $p) }}"
         class="pi-btn-icon pi-btn-edit"
         title="{{ app()->getLocale()==='bn' ? 'সম্পাদনা' : 'Edit' }}">
        <i class="fas fa-edit"></i>
      </a>
      <a href="{{ route('admin.products.notify.form', $p) }}"
         class="pi-btn-icon pi-btn-notify"
         title="{{ app()->getLocale()==='bn' ? 'নোটিফিকেশন পাঠান' : 'Notify Customers' }}">
        <i class="fas fa-bell"></i>
      </a>
      <button class="pi-btn-icon pi-btn-del pi-del-btn"
        data-id="{{ $p->id }}"
        data-name="{{ $p->name_en }}"
        data-url="{{ route('admin.products.destroy', $p) }}"
        title="{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}">
        <i class="fas fa-trash"></i>
      </button>
    </div>
  </td>
</tr>

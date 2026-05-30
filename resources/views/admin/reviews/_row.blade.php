{{-- Partial: single review table row --}}
<tr id="rv-row-{{ $r->id }}">
  <td style="color:var(--muted)">{{ $r->id }}</td>
  <td>
    <strong style="color:var(--text);font-size:13px">{{ $r->product?->name_en ?? 'N/A' }}</strong>
  </td>
  <td style="color:var(--text)">{{ $r->user?->name ?? 'N/A' }}</td>
  <td>
    <span class="rv-stars">
      @for($i = 1; $i <= 5; $i++)
        <span class="{{ $i <= $r->rating ? 'rv-star-on' : 'rv-star-off' }}">★</span>
      @endfor
    </span>
  </td>
  <td style="max-width:200px;color:var(--text);font-size:13px">
    {{ Str::limit($r->comment, 60) }}
  </td>
  <td>
    @if($r->image_url)
      <a href="{{ $r->image_url }}" target="_blank">
        <img src="{{ $r->image_url }}" class="rv-img" alt="review">
      </a>
    @else
      <span style="color:var(--muted-2)">—</span>
    @endif
  </td>
  <td>
    @if($r->is_approved)
      <span class="rv-badge rv-badge-approved">
        {{ app()->getLocale()==='bn' ? 'অনুমোদিত' : 'Approved' }}
      </span>
    @else
      <span class="rv-badge rv-badge-pending">
        {{ app()->getLocale()==='bn' ? 'অপেক্ষমাণ' : 'Pending' }}
      </span>
    @endif
  </td>
  <td style="color:var(--muted);font-size:12px">
    {{ $r->created_at->format('d M Y') }}
  </td>
  <td>
    <div class="rv-action-btns">
      @if(!$r->is_approved)
        <form method="POST" action="{{ route('admin.reviews.approve', $r) }}">
          @csrf @method('PATCH')
          <button type="submit" class="rv-btn rv-btn-approve"
            title="{{ app()->getLocale()==='bn' ? 'অনুমোদন করুন' : 'Approve' }}">
            <i class="fas fa-check"></i>
          </button>
        </form>
      @else
        <form method="POST" action="{{ route('admin.reviews.reject', $r) }}">
          @csrf @method('PATCH')
          <button type="submit" class="rv-btn rv-btn-reject"
            title="{{ app()->getLocale()==='bn' ? 'প্রত্যাখ্যান করুন' : 'Reject' }}">
            <i class="fas fa-ban"></i>
          </button>
        </form>
      @endif
      <form method="POST" action="{{ route('admin.reviews.destroy', $r) }}"
        onsubmit="return confirm('{{ app()->getLocale()==='bn' ? 'এই রিভিউটি মুছে ফেলবেন?' : 'Delete this review?' }}')">
        @csrf @method('DELETE')
        <button type="submit" class="rv-btn rv-btn-del"
          title="{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}">
          <i class="fas fa-trash"></i>
        </button>
      </form>
    </div>
  </td>
</tr>

@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'রিভিউ ব্যবস্থাপনা' : 'Review Management')
@section('page-title', app()->getLocale()==='bn' ? 'রিভিউ ব্যবস্থাপনা' : 'Review Management')

@section('content')

{{-- Stats --}}
<div class="adm-stats fade-up" style="grid-template-columns:repeat(3,1fr)">
  <div class="adm-stat blue">
    <div class="adm-stat-icon blue"><i class="fas fa-star"></i></div>
    <div class="adm-stat-val">{{ $stats['total'] }}</div>
    <div class="adm-stat-lbl">{{ app()->getLocale()==='bn' ? 'মোট রিভিউ' : 'Total Reviews' }}</div>
  </div>
  <div class="adm-stat green">
    <div class="adm-stat-icon green"><i class="fas fa-check-circle"></i></div>
    <div class="adm-stat-val">{{ $stats['approved'] }}</div>
    <div class="adm-stat-lbl">{{ app()->getLocale()==='bn' ? 'অনুমোদিত' : 'Approved' }}</div>
  </div>
  <div class="adm-stat amber">
    <div class="adm-stat-icon amber"><i class="fas fa-clock"></i></div>
    <div class="adm-stat-val">{{ $stats['pending'] }}</div>
    <div class="adm-stat-lbl">{{ app()->getLocale()==='bn' ? 'অপেক্ষমাণ' : 'Pending' }}</div>
  </div>
</div>

{{-- Filters --}}
<div class="adm-card mb-4 fade-up-1">
  <div class="adm-card-body">
    <form method="GET" class="row g-3">
      <div class="col-md-5">
        <input type="text" name="search" class="adm-input"
          placeholder="{{ app()->getLocale()==='bn' ? 'রিভিউ, পণ্য বা ব্যবহারকারী খুঁজুন...' : 'Search reviews, products or users...' }}"
          value="{{ $search }}">
      </div>
      <div class="col-md-4">
        <select name="filter" class="adm-select">
          <option value="">{{ app()->getLocale()==='bn' ? 'সব রিভিউ' : 'All Reviews' }}</option>
          <option value="approved" @selected($filter === 'approved')>
            {{ app()->getLocale()==='bn' ? 'অনুমোদিত' : 'Approved' }}
          </option>
          <option value="pending" @selected($filter === 'pending')>
            {{ app()->getLocale()==='bn' ? 'অপেক্ষমাণ' : 'Pending' }}
          </option>
        </select>
      </div>
      <div class="col-md-3">
        <button class="adm-btn adm-btn-primary w-100" type="submit">
          <i class="fas fa-search"></i>
          {{ app()->getLocale()==='bn' ? 'ফিল্টার করুন' : 'Filter' }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="adm-card fade-up-2">
  <div class="adm-card-head">
    <div class="adm-card-title">
      <i class="fas fa-comment-alt" style="color:var(--primary)"></i>
      {{ app()->getLocale()==='bn' ? 'রিভিউ তালিকা' : 'Reviews List' }}
    </div>
  </div>
  <div class="table-responsive">
    <table class="adm-table">
      <thead>
        <tr>
          <th>#</th>
          <th>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Product' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'ব্যবহারকারী' : 'User' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'রেটিং' : 'Rating' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'মন্তব্য' : 'Comment' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'ছবি' : 'Image' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'তারিখ' : 'Date' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($reviews as $review)
        <tr>
          <td style="color:var(--muted)">{{ $review->id }}</td>
          <td>
            <span style="font-weight:600;font-size:13px;">{{ $review->product?->name_en ?? 'N/A' }}</span>
          </td>
          <td>{{ $review->user?->name ?? 'N/A' }}</td>
          <td>
            @for($i = 1; $i <= 5; $i++)
              <span style="color:{{ $i <= $review->rating ? 'var(--warning)' : 'var(--muted-2)' }}">★</span>
            @endfor
          </td>
          <td style="max-width:200px;">
            <span title="{{ $review->comment }}" style="font-size:13px;">
              {{ Str::limit($review->comment, 60) }}
            </span>
          </td>
          <td>
            @if($review->image_url)
              <a href="{{ $review->image_url }}" target="_blank">
                <img src="{{ $review->image_url }}" alt="review"
                  style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
              </a>
            @else
              <span style="color:var(--muted-2)">—</span>
            @endif
          </td>
          <td>
            @if($review->is_approved)
              <span class="adm-badge s-confirmed">
                {{ app()->getLocale()==='bn' ? 'অনুমোদিত' : 'Approved' }}
              </span>
            @else
              <span class="adm-badge s-pending">
                {{ app()->getLocale()==='bn' ? 'অপেক্ষমাণ' : 'Pending' }}
              </span>
            @endif
          </td>
          <td style="color:var(--muted);font-size:12px;">{{ $review->created_at->format('d M Y') }}</td>
          <td>
            <div class="d-flex gap-1">
              @if(!$review->is_approved)
                <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                  @csrf @method('PATCH')
                  <button class="adm-btn adm-btn-xs"
                    style="background:var(--success-s);color:var(--success);border:1px solid var(--success);border-radius:6px;"
                    title="{{ app()->getLocale()==='bn' ? 'অনুমোদন করুন' : 'Approve' }}">
                    <i class="fas fa-check"></i>
                  </button>
                </form>
              @else
                <form method="POST" action="{{ route('admin.reviews.reject', $review) }}">
                  @csrf @method('PATCH')
                  <button class="adm-btn adm-btn-xs"
                    style="background:var(--warning-s);color:var(--warning);border:1px solid var(--warning);border-radius:6px;"
                    title="{{ app()->getLocale()==='bn' ? 'প্রত্যাখ্যান করুন' : 'Reject' }}">
                    <i class="fas fa-ban"></i>
                  </button>
                </form>
              @endif
              <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                    onsubmit="return confirm('{{ app()->getLocale()==='bn' ? 'এই রিভিউটি মুছে ফেলবেন?' : 'Delete this review?' }}')">
                @csrf @method('DELETE')
                <button class="adm-btn adm-btn-xs"
                  style="background:var(--danger-s);color:var(--danger);border:1px solid var(--danger);border-radius:6px;"
                  title="{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" style="text-align:center;padding:40px;color:var(--muted)">
            <i class="fas fa-star fa-2x mb-3" style="display:block;opacity:.3"></i>
            {{ app()->getLocale()==='bn' ? 'কোনো রিভিউ পাওয়া যায়নি।' : 'No reviews found.' }}
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($reviews->hasPages())
  <div class="adm-card-body" style="border-top:1px solid var(--border)">
    {{ $reviews->links() }}
  </div>
  @endif
</div>

@endsection

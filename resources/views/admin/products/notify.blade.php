@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'কাস্টমার নোটিফিকেশন' : 'Notify Customers')
@section('page-title', app()->getLocale()==='bn' ? 'কাস্টমার নোটিফিকেশন' : 'Notify Customers')

@push('styles')
<style>
.notify-wrap {
  max-width: 720px;
}
.notify-product-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 20px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: var(--r);
  margin-bottom: 24px;
}
.notify-product-thumb {
  width: 72px; height: 72px;
  object-fit: cover;
  border-radius: var(--r-sm);
  border: 1px solid var(--border);
  flex-shrink: 0;
}
.notify-product-thumb-ph {
  width: 72px; height: 72px;
  border-radius: var(--r-sm);
  border: 1px solid var(--border);
  background: var(--white);
  display: flex; align-items: center; justify-content: center;
  color: var(--muted-2); flex-shrink: 0; font-size: 1.4rem;
}
.notify-product-info h4 {
  font-size: 15px; font-weight: 700; color: var(--text); margin: 0 0 4px;
}
.notify-product-info .pi-price {
  color: var(--primary); font-weight: 700; font-size: 16px;
}

/* Audience cards */
.aud-cards { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 6px; }
.aud-card {
  flex: 1 1 200px;
  border: 2px solid var(--border);
  border-radius: var(--r);
  padding: 14px 16px;
  cursor: pointer;
  transition: all .15s;
  background: var(--white);
  position: relative;
}
.aud-card:hover { border-color: var(--primary); background: var(--primary-s); }
.aud-card.selected { border-color: var(--primary); background: var(--primary-s); }
.aud-card input[type=radio] { position: absolute; opacity: 0; pointer-events: none; }
.aud-card-title {
  font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 3px;
  display: flex; align-items: center; gap: 6px;
}
.aud-card-count {
  font-size: 20px; font-weight: 800; color: var(--primary);
}
.aud-card-desc { font-size: 11px; color: var(--muted); margin-top: 2px; }

/* Channel toggles */
.ch-toggles { display: flex; gap: 12px; flex-wrap: wrap; }
.ch-toggle {
  display: flex; align-items: center; gap: 10px;
  border: 2px solid var(--border);
  border-radius: var(--r);
  padding: 12px 18px;
  cursor: pointer;
  transition: all .15s;
  background: var(--white);
  flex: 1 1 160px;
}
.ch-toggle:hover { border-color: var(--primary); }
.ch-toggle.selected-email { border-color: var(--primary); background: var(--primary-s); }
.ch-toggle.selected-whatsapp { border-color: #25d366; background: rgba(37,211,102,.08); }
.ch-toggle input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--primary); }
.ch-toggle-icon { font-size: 1.3rem; }
.ch-label { font-size: 13px; font-weight: 600; color: var(--text); }
.ch-sublabel { font-size: 11px; color: var(--muted); }

/* Subject field */
#subjectGroup { display: none; }

/* Preview panel */
.notify-preview {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 16px;
  margin-top: 8px;
}
.notify-preview-header {
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .6px; color: var(--muted); margin-bottom: 8px;
}
.notify-preview-body {
  font-size: 13px; color: var(--text); white-space: pre-wrap; line-height: 1.6;
}

/* Action row */
.notify-actions {
  display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 24px;
}
</style>
@endpush

@section('content')
<div class="notify-wrap">

  {{-- Back --}}
  <div class="mb-3">
    <a href="{{ route('admin.products.index') }}" class="adm-btn adm-btn-ghost adm-btn-sm">
      <i class="fas fa-arrow-left"></i>{{ app()->getLocale()==='bn' ? 'পণ্যের তালিকায় ফিরুন' : 'Back to Products' }}
    </a>
  </div>

  {{-- Product preview --}}
  <div class="notify-product-card">
    @php $img = $product->image_url; @endphp
    @if($img && !str_contains($img,'no-image'))
      <img src="{{ $img }}" class="notify-product-thumb" alt="{{ $product->name }}">
    @else
      <div class="notify-product-thumb-ph"><i class="fas fa-box"></i></div>
    @endif
    <div class="notify-product-info">
      <h4>{{ $product->name }}</h4>
      <div class="pi-price">৳{{ number_format($product->price, 2) }}</div>
      @if($product->product_type)
        <small style="color:var(--muted)">{{ $product->product_type }}</small>
      @endif
    </div>
  </div>

  {{-- Form Card --}}
  <div class="adm-card">
    <div class="adm-card-head">
      <div class="adm-card-title">
        <i class="fas fa-bell" style="color:var(--primary)"></i>
        {{ app()->getLocale()==='bn' ? 'কাস্টমারদের নোটিফাই করুন' : 'Send Customer Notification' }}
      </div>
    </div>
    <div class="adm-card-body">
      <form method="POST" action="{{ route('admin.products.notify.send', $product) }}" id="notifyForm">
        @csrf

        {{-- Step 1: Audience --}}
        <div class="mb-4">
          <label class="adm-label mb-2">
            <i class="fas fa-users me-1"></i>
            {{ app()->getLocale()==='bn' ? 'কাদের কাছে পাঠাবেন?' : 'Select Audience' }}
          </label>
          <div class="aud-cards">
            <label class="aud-card selected" id="audAll" onclick="selectAud('all')">
              <input type="radio" name="audience" value="all" checked>
              <div class="aud-card-title"><i class="fas fa-globe"></i>{{ app()->getLocale()==='bn' ? 'সকল কাস্টমার' : 'All Customers' }}</div>
              <div class="aud-card-count">{{ $totalCustomers }}</div>
              <div class="aud-card-desc">{{ app()->getLocale()==='bn' ? 'সকল রেজিস্টার্ড কাস্টমার' : 'All registered customers' }}</div>
            </label>
            <label class="aud-card" id="audTop" onclick="selectAud('top')">
              <input type="radio" name="audience" value="top">
              <div class="aud-card-title"><i class="fas fa-star" style="color:var(--warning)"></i>{{ app()->getLocale()==='bn' ? 'টপ কাস্টমার' : 'Top Customers' }}</div>
              <div class="aud-card-count" style="color:var(--warning)">{{ $topCustomers }}</div>
              <div class="aud-card-desc">{{ app()->getLocale()==='bn' ? '২+ অর্ডার করেছেন' : '2+ orders placed' }}</div>
            </label>
          </div>
          @error('audience')<div class="adm-error">{{ $message }}</div>@enderror
        </div>

        {{-- Step 2: Channels --}}
        <div class="mb-4">
          <label class="adm-label mb-2">
            <i class="fas fa-paper-plane me-1"></i>
            {{ app()->getLocale()==='bn' ? 'কোন চ্যানেলে পাঠাবেন?' : 'Select Channels' }}
          </label>
          <div class="ch-toggles">
            <label class="ch-toggle" id="chEmail" onclick="toggleChannel('email')">
              <input type="checkbox" name="channels[]" value="email" id="cbEmail" checked>
              <span class="ch-toggle-icon">📧</span>
              <div>
                <div class="ch-label">Email</div>
                <div class="ch-sublabel">{{ app()->getLocale()==='bn' ? 'সুন্দর HTML ইমেইল' : 'Styled HTML email' }}</div>
              </div>
            </label>
            <label class="ch-toggle" id="chWa" onclick="toggleChannel('whatsapp')">
              <input type="checkbox" name="channels[]" value="whatsapp" id="cbWa">
              <span class="ch-toggle-icon">💬</span>
              <div>
                <div class="ch-label">WhatsApp</div>
                <div class="ch-sublabel">{{ app()->getLocale()==='bn' ? 'API দিয়ে মেসেজ' : 'UltraMsg / API' }}</div>
              </div>
            </label>
          </div>
          @error('channels')<div class="adm-error mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Subject (email only) --}}
        <div class="mb-3" id="subjectGroup">
          <label class="adm-label">
            {{ app()->getLocale()==='bn' ? 'ইমেইল সাবজেক্ট' : 'Email Subject' }}
          </label>
          <input type="text" class="adm-input" name="subject" id="subjectInput"
            value="{{ app()->getLocale()==='bn' ? 'AmardoKar — নতুন পণ্য এসেছে!' : 'AmardoKar — New Product Alert!' }}"
            placeholder="{{ app()->getLocale()==='bn' ? 'ইমেইল সাবজেক্ট...' : 'Email subject...' }}">
        </div>

        {{-- Message --}}
        <div class="mb-3">
          <label class="adm-label">
            {{ app()->getLocale()==='bn' ? 'মেসেজ' : 'Message' }}
            <span style="color:var(--muted);font-weight:400">({{ app()->getLocale()==='bn' ? 'সর্বোচ্চ ১০০০ অক্ষর' : 'Max 1000 chars' }})</span>
          </label>
          <textarea class="adm-input" name="message" id="msgInput" rows="4"
            maxlength="1000"
            placeholder="{{ app()->getLocale()==='bn' ? 'কাস্টমারদের জন্য মেসেজ লিখুন...' : 'Write a message for your customers...' }}"
            oninput="updatePreview()">{{ app()->getLocale()==='bn'
              ? 'আমাদের নতুন পণ্য দেখুন! সীমিত সময়ের জন্য পাওয়া যাচ্ছে।'
              : 'Check out our exciting new product! Available for a limited time.' }}</textarea>
          <small style="color:var(--muted)" id="charCount">0 / 1000</small>
          @error('message')<div class="adm-error">{{ $message }}</div>@enderror
        </div>

        {{-- Preview --}}
        <div class="notify-preview">
          <div class="notify-preview-header"><i class="fas fa-eye me-1"></i>{{ app()->getLocale()==='bn' ? 'WhatsApp প্রিভিউ' : 'WhatsApp Preview' }}</div>
          <div class="notify-preview-body" id="waPreview"></div>
        </div>

        {{-- Actions --}}
        <div class="notify-actions">
          <button type="submit" class="adm-btn adm-btn-primary" id="sendBtn">
            <i class="fas fa-paper-plane"></i>
            {{ app()->getLocale()==='bn' ? 'নোটিফিকেশন পাঠান' : 'Send Notification' }}
          </button>
          <a href="{{ route('admin.products.index') }}" class="adm-btn adm-btn-ghost">
            {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
          </a>
        </div>

      </form>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
const PRODUCT_NAME  = {{ json_encode($product->name) }};
const PRODUCT_PRICE = "৳{{ number_format($product->price, 2) }}";
const PRODUCT_URL   = "{{ url('/product/'.$product->id) }}";

function selectAud(val) {
  document.getElementById('audAll').classList.toggle('selected', val === 'all');
  document.getElementById('audTop').classList.toggle('selected', val === 'top');
  document.querySelector(`input[value="${val}"]`).checked = true;
}

function toggleChannel(ch) {
  const cb = document.getElementById('cb' + (ch === 'email' ? 'Email' : 'Wa'));
  cb.checked = !cb.checked;
  const el = document.getElementById(ch === 'email' ? 'chEmail' : 'chWa');
  if (ch === 'email') {
    el.classList.toggle('selected-email', cb.checked);
    document.getElementById('subjectGroup').style.display = cb.checked ? 'block' : 'none';
  } else {
    el.classList.toggle('selected-whatsapp', cb.checked);
  }
  updatePreview();
}

function updatePreview() {
  const msg = document.getElementById('msgInput').value;
  const count = msg.length;
  document.getElementById('charCount').textContent = count + ' / 1000';

  const preview = `🛍️ *AmardoKar — নতুন পণ্য*\n\n*${PRODUCT_NAME}*\n💰 মূল্য: ${PRODUCT_PRICE}\n\n${msg}\n\n🔗 ${PRODUCT_URL}`;
  document.getElementById('waPreview').textContent = preview;
}

// Init
document.getElementById('chEmail').classList.add('selected-email');
document.getElementById('subjectGroup').style.display = 'block';
updatePreview();

// Submit guard
document.getElementById('notifyForm').addEventListener('submit', function() {
  const btn = document.getElementById('sendBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ app()->getLocale()==="bn" ? "পাঠানো হচ্ছে..." : "Sending..." }}';
});
</script>
@endpush

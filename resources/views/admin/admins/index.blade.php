@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'অ্যাডমিন ব্যবস্থাপনা' : 'Admin Management')
@section('page-title', app()->getLocale()==='bn' ? 'অ্যাডমিন ব্যবস্থাপনা' : 'Admin Management')

@section('content')
<div class="fade-up">

  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 style="font-size:18px;font-weight:700;color:var(--text);margin-bottom:4px;">
        {{ app()->getLocale()==='bn' ? 'অ্যাডমিন ব্যবস্থাপনা' : 'Admin Management' }}
      </h4>
      <p style="color:var(--muted);font-size:13px;margin:0;">
        {{ app()->getLocale()==='bn'
          ? 'অ্যাডমিন অ্যাকাউন্ট পরিচালনা করুন। শুধুমাত্র সুপার অ্যাডমিন নতুন অ্যাডমিন যোগ বা মুছতে পারবেন।'
          : 'Manage admin accounts. Only Super Admins can add or remove admins.' }}
      </p>
    </div>
    <button class="adm-btn adm-btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
      <i class="fas fa-plus"></i>
      {{ app()->getLocale()==='bn' ? 'নতুন অ্যাডমিন যোগ করুন' : 'Add New Admin' }}
    </button>
  </div>

  {{-- Admins Table --}}
  <div class="adm-card">
    <div class="adm-card-head">
      <div class="adm-card-title">
        <i class="fas fa-user-shield" style="color:var(--primary)"></i>
        {{ app()->getLocale()==='bn' ? 'সকল অ্যাডমিন' : 'All Admins' }}
      </div>
      <span style="background:var(--bg);color:var(--muted);font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;border:1px solid var(--border);">
        {{ $admins->count() }} {{ app()->getLocale()==='bn' ? 'জন' : 'total' }}
      </span>
    </div>
    <div class="table-responsive">
      <table class="adm-table">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ app()->getLocale()==='bn' ? 'নাম' : 'Name' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'ইমেইল' : 'Email' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'ভূমিকা' : 'Role' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'তৈরির তারিখ' : 'Created' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($admins as $admin)
          <tr>
            <td style="color:var(--muted)">{{ $loop->iteration }}</td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;background:linear-gradient(135deg,var(--primary),var(--purple));border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0;">
                  {{ strtoupper(substr($admin->name,0,1)) }}
                </div>
                <span style="font-weight:600;">{{ $admin->name }}</span>
                @if($admin->id === auth('admin')->id())
                  <span style="background:rgba(22,163,74,.15);color:#4ade80;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;">
                    {{ app()->getLocale()==='bn' ? 'আপনি' : 'You' }}
                  </span>
                @endif
              </div>
            </td>
            <td>{{ $admin->email }}</td>
            <td>
              @if($admin->role === 'super_admin')
                <span style="background:rgba(217,119,6,.15);color:#fbbf24;display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                  ⭐ {{ app()->getLocale()==='bn' ? 'সুপার অ্যাডমিন' : 'Super Admin' }}
                </span>
              @else
                <span style="background:var(--primary-s);color:var(--primary);display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                  {{ app()->getLocale()==='bn' ? 'অ্যাডমিন' : 'Admin' }}
                </span>
              @endif
            </td>
            <td style="color:var(--muted)">{{ $admin->created_at->format('d M Y') }}</td>
            <td>
              <div class="d-flex gap-2">
                <button
                  class="adm-btn adm-btn-ghost adm-btn-icon adm-btn-xs"
                  onclick="openEditModal({{ $admin->id }}, '{{ addslashes($admin->name) }}', '{{ $admin->email }}', '{{ $admin->role }}')"
                  title="{{ app()->getLocale()==='bn' ? 'সম্পাদনা' : 'Edit' }}"
                  style="color:var(--primary);border-color:var(--primary)">
                  <i class="fas fa-edit"></i>
                </button>
                @if($admin->id !== auth('admin')->id())
                <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}"
                      onsubmit="return confirm('{{ app()->getLocale()==='bn' ? addslashes($admin->name).' কে মুছে ফেলবেন?' : 'Remove '.addslashes($admin->name).'?' }}')">
                  @csrf @method('DELETE')
                  <button type="submit"
                    class="adm-btn adm-btn-ghost adm-btn-icon adm-btn-xs"
                    title="{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}"
                    style="color:var(--danger);border-color:var(--danger)">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Add Admin Modal --}}
@push('modals')
<div class="modal fade" id="addAdminModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-user-plus me-2" style="color:var(--primary)"></i>
          {{ app()->getLocale()==='bn' ? 'নতুন অ্যাডমিন যোগ করুন' : 'Add New Admin' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('admin.admins.store') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পুরো নাম' : 'Full Name' }}</label>
            <input type="text" name="name" class="adm-input"
              placeholder="{{ app()->getLocale()==='bn' ? 'যেমন: রাহেলা বেগম' : 'e.g. John Doe' }}" required>
          </div>
          <div class="mb-3">
            <label class="adm-label">{{ app()->getLocale()==='bn' ? 'ইমেইল ঠিকানা' : 'Email Address' }}</label>
            <input type="email" name="email" class="adm-input"
              placeholder="admin@example.com" required>
          </div>
          <div class="mb-3">
            <label class="adm-label">{{ app()->getLocale()==='bn' ? 'ভূমিকা' : 'Role' }}</label>
            <select name="role" class="adm-select">
              <option value="admin">{{ app()->getLocale()==='bn' ? 'অ্যাডমিন' : 'Admin' }}</option>
              <option value="super_admin">{{ app()->getLocale()==='bn' ? 'সুপার অ্যাডমিন' : 'Super Admin' }}</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পাসওয়ার্ড' : 'Password' }}</label>
            <input type="password" name="password" class="adm-input"
              placeholder="{{ app()->getLocale()==='bn' ? 'কমপক্ষে ৮ অক্ষর' : 'Min 8 characters' }}" required>
          </div>
          <div class="mb-0">
            <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পাসওয়ার্ড নিশ্চিত করুন' : 'Confirm Password' }}</label>
            <input type="password" name="password_confirmation" class="adm-input" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="adm-btn adm-btn-ghost" data-bs-dismiss="modal">
            {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
          </button>
          <button type="submit" class="adm-btn adm-btn-primary">
            <i class="fas fa-plus"></i>
            {{ app()->getLocale()==='bn' ? 'অ্যাডমিন যোগ করুন' : 'Add Admin' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit Admin Modal --}}
<div class="modal fade" id="editAdminModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-edit me-2" style="color:var(--primary)"></i>
          {{ app()->getLocale()==='bn' ? 'অ্যাডমিন সম্পাদনা' : 'Edit Admin' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="editAdminForm" action="">
        @csrf @method('PATCH')
        <div class="modal-body">
          <div class="mb-3">
            <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পুরো নাম' : 'Full Name' }}</label>
            <input type="text" name="name" id="editName" class="adm-input" required>
          </div>
          <div class="mb-3">
            <label class="adm-label">{{ app()->getLocale()==='bn' ? 'ইমেইল ঠিকানা' : 'Email Address' }}</label>
            <input type="email" name="email" id="editEmail" class="adm-input" required>
          </div>
          <div class="mb-3">
            <label class="adm-label">{{ app()->getLocale()==='bn' ? 'ভূমিকা' : 'Role' }}</label>
            <select name="role" id="editRole" class="adm-select">
              <option value="admin">{{ app()->getLocale()==='bn' ? 'অ্যাডমিন' : 'Admin' }}</option>
              <option value="super_admin">{{ app()->getLocale()==='bn' ? 'সুপার অ্যাডমিন' : 'Super Admin' }}</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="adm-label">
              {{ app()->getLocale()==='bn' ? 'নতুন পাসওয়ার্ড' : 'New Password' }}
              <span style="color:var(--muted);font-weight:400;">
                ({{ app()->getLocale()==='bn' ? 'না বদলালে খালি রাখুন' : 'leave blank to keep current' }})
              </span>
            </label>
            <input type="password" name="password" class="adm-input"
              placeholder="{{ app()->getLocale()==='bn' ? 'না বদলালে খালি রাখুন' : 'Leave blank to keep current' }}">
          </div>
          <div class="mb-0">
            <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পাসওয়ার্ড নিশ্চিত করুন' : 'Confirm New Password' }}</label>
            <input type="password" name="password_confirmation" class="adm-input">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="adm-btn adm-btn-ghost" data-bs-dismiss="modal">
            {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
          </button>
          <button type="submit" class="adm-btn adm-btn-primary">
            <i class="fas fa-save"></i>
            {{ app()->getLocale()==='bn' ? 'পরিবর্তন সংরক্ষণ করুন' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endpush

@endsection

@push('scripts')
<script>
function openEditModal(id, name, email, role) {
  document.getElementById('editAdminForm').action = '/admin/admins/' + id;
  document.getElementById('editName').value = name;
  document.getElementById('editEmail').value = email;
  document.getElementById('editRole').value = role;
  new bootstrap.Modal(document.getElementById('editAdminModal')).show();
}
</script>
@endpush
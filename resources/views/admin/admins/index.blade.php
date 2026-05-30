@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'অ্যাডমিন ব্যবস্থাপনা' : 'Admin Management')
@section('page-title', app()->getLocale()==='bn' ? 'অ্যাডমিন ব্যবস্থাপনা' : 'Admin Management')

@section('content')
<div class="fade-up">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 style="font-size:18px;font-weight:700;color:var(--text);margin-bottom:4px;">
        {{ app()->getLocale()==='bn' ? 'অ্যাডমিন ব্যবস্থাপনা' : 'Admin Management' }}
      </h4>
      <p style="color:var(--muted);font-size:13px;margin:0;">
        {{ app()->getLocale()==='bn'
          ? 'অ্যাডমিন অ্যাকাউন্ট ও তাদের পেজ অ্যাক্সেস নিয়ন্ত্রণ করুন।'
          : 'Manage admin accounts and control their page access.' }}
      </p>
    </div>
    <button class="adm-btn adm-btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
      <i class="fas fa-plus"></i>
      {{ app()->getLocale()==='bn' ? 'নতুন অ্যাডমিন যোগ করুন' : 'Add New Admin' }}
    </button>
  </div>

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
            <th>{{ app()->getLocale()==='bn' ? 'অনুমতি' : 'Permissions' }}</th>
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
            <td>
              @if($admin->isSuperAdmin())
                <span style="color:var(--muted);font-size:12px;font-style:italic;">
                  {{ app()->getLocale()==='bn' ? 'সম্পূর্ণ অ্যাক্সেস' : 'Full Access' }}
                </span>
              @else
                @php $perms = $admin->permissions ?? []; @endphp
                @if(count($perms) === 0)
                  <span style="color:var(--danger);font-size:12px;font-weight:600;">
                    <i class="fas fa-ban me-1"></i>{{ app()->getLocale()==='bn' ? 'কোনো অ্যাক্সেস নেই' : 'No Access' }}
                  </span>
                @else
                  <div class="d-flex flex-wrap gap-1">
                    @foreach($perms as $pKey)
                      @if(isset($allPerms[$pKey]))
                        <span style="background:var(--primary-s);color:var(--primary);font-size:10px;font-weight:600;padding:2px 7px;border-radius:10px;">
                          <i class="fas {{ $allPerms[$pKey]['icon'] }} me-1" style="font-size:9px;"></i>
                          {{ app()->getLocale()==='bn' ? $allPerms[$pKey]['label_bn'] : $allPerms[$pKey]['label_en'] }}
                        </span>
                      @endif
                    @endforeach
                  </div>
                @endif
              @endif
            </td>
            <td style="color:var(--muted)">{{ $admin->created_at->format('d M Y') }}</td>
            <td>
              <div class="d-flex gap-2">
                <button
                  class="adm-btn adm-btn-ghost adm-btn-icon adm-btn-xs"
                  onclick="openEditModal({{ $admin->id }}, '{{ addslashes($admin->name) }}', '{{ $admin->email }}', '{{ $admin->role }}', {{ json_encode($admin->permissions ?? []) }})"
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

@push('modals')

{{-- ── ADD ADMIN MODAL ───────────────────────────────── --}}
<div class="modal fade" id="addAdminModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
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
          <div class="row g-3">
            <div class="col-md-6">
              <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পুরো নাম' : 'Full Name' }}</label>
              <input type="text" name="name" class="adm-input"
                placeholder="{{ app()->getLocale()==='bn' ? 'যেমন: রাহেলা বেগম' : 'e.g. John Doe' }}" required>
            </div>
            <div class="col-md-6">
              <label class="adm-label">{{ app()->getLocale()==='bn' ? 'ইমেইল ঠিকানা' : 'Email Address' }}</label>
              <input type="email" name="email" class="adm-input" placeholder="admin@example.com" required>
            </div>
            <div class="col-md-4">
              <label class="adm-label">{{ app()->getLocale()==='bn' ? 'ভূমিকা' : 'Role' }}</label>
              <select name="role" class="adm-select" id="addRoleSelect" onchange="toggleAddPermissions(this.value)">
                <option value="admin">{{ app()->getLocale()==='bn' ? 'অ্যাডমিন' : 'Admin' }}</option>
                <option value="super_admin">{{ app()->getLocale()==='bn' ? 'সুপার অ্যাডমিন' : 'Super Admin' }}</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পাসওয়ার্ড' : 'Password' }}</label>
              <input type="password" name="password" class="adm-input"
                placeholder="{{ app()->getLocale()==='bn' ? 'কমপক্ষে ৮ অক্ষর' : 'Min 8 characters' }}" required>
            </div>
            <div class="col-md-4">
              <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পাসওয়ার্ড নিশ্চিত করুন' : 'Confirm Password' }}</label>
              <input type="password" name="password_confirmation" class="adm-input" required>
            </div>
          </div>

          {{-- Permissions --}}
          <div id="addPermissionsSection" class="mt-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <label class="adm-label mb-0" style="font-size:14px;font-weight:700;">
                <i class="fas fa-shield-alt me-1" style="color:var(--primary)"></i>
                {{ app()->getLocale()==='bn' ? 'পেজ অ্যাক্সেস অনুমতি' : 'Page Access Permissions' }}
              </label>
              <div class="d-flex gap-2">
                <button type="button" class="adm-btn adm-btn-ghost adm-btn-xs" onclick="selectAllPerms('add')">
                  {{ app()->getLocale()==='bn' ? 'সব নির্বাচন' : 'Select All' }}
                </button>
                <button type="button" class="adm-btn adm-btn-ghost adm-btn-xs" onclick="clearAllPerms('add')">
                  {{ app()->getLocale()==='bn' ? 'সব বাদ' : 'Clear All' }}
                </button>
              </div>
            </div>
            <p style="font-size:12px;color:var(--muted);margin-bottom:12px;">
              {{ app()->getLocale()==='bn'
                ? 'এই অ্যাডমিন কোন কোন পেজ ব্যবহার করতে পারবে তা নির্বাচন করুন।'
                : 'Select which pages this admin can access.' }}
            </p>
            <div class="row g-2">
              @foreach($allPerms as $key => $perm)
              <div class="col-6 col-md-4 col-lg-3">
                <label class="perm-label" data-prefix="add" style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;transition:all .15s;background:var(--white);">
                  <input type="checkbox" name="permissions[]" value="{{ $key }}"
                    class="add-perm-check"
                    style="width:16px;height:16px;accent-color:var(--primary);flex-shrink:0;"
                    onchange="syncPermLabel(this)">
                  <span style="font-size:12px;font-weight:600;color:var(--text);">
                    <i class="fas {{ $perm['icon'] }} me-1" style="color:var(--primary);font-size:11px;"></i>
                    {{ app()->getLocale()==='bn' ? $perm['label_bn'] : $perm['label_en'] }}
                  </span>
                </label>
              </div>
              @endforeach
            </div>
          </div>

          <div id="addSuperNote" style="display:none;" class="mt-3">
            <div style="background:rgba(217,119,6,.1);border:1px solid rgba(217,119,6,.3);border-radius:8px;padding:12px 14px;color:#fbbf24;font-size:13px;">
              <i class="fas fa-crown me-2"></i>
              {{ app()->getLocale()==='bn'
                ? 'সুপার অ্যাডমিনের সকল পেজে স্বয়ংক্রিয়ভাবে সম্পূর্ণ অ্যাক্সেস থাকবে।'
                : 'Super Admin automatically has full access to all pages.' }}
            </div>
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

{{-- ── EDIT ADMIN MODAL ──────────────────────────────── --}}
<div class="modal fade" id="editAdminModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
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
          <div class="row g-3">
            <div class="col-md-6">
              <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পুরো নাম' : 'Full Name' }}</label>
              <input type="text" name="name" id="editName" class="adm-input" required>
            </div>
            <div class="col-md-6">
              <label class="adm-label">{{ app()->getLocale()==='bn' ? 'ইমেইল ঠিকানা' : 'Email Address' }}</label>
              <input type="email" name="email" id="editEmail" class="adm-input" required>
            </div>
            <div class="col-md-4">
              <label class="adm-label">{{ app()->getLocale()==='bn' ? 'ভূমিকা' : 'Role' }}</label>
              <select name="role" id="editRole" class="adm-select" onchange="toggleEditPermissions(this.value)">
                <option value="admin">{{ app()->getLocale()==='bn' ? 'অ্যাডমিন' : 'Admin' }}</option>
                <option value="super_admin">{{ app()->getLocale()==='bn' ? 'সুপার অ্যাডমিন' : 'Super Admin' }}</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="adm-label">
                {{ app()->getLocale()==='bn' ? 'নতুন পাসওয়ার্ড' : 'New Password' }}
                <span style="color:var(--muted);font-weight:400;">({{ app()->getLocale()==='bn' ? 'না বদলালে খালি' : 'leave blank to keep' }})</span>
              </label>
              <input type="password" name="password" class="adm-input">
            </div>
            <div class="col-md-4">
              <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পাসওয়ার্ড নিশ্চিত করুন' : 'Confirm Password' }}</label>
              <input type="password" name="password_confirmation" class="adm-input">
            </div>
          </div>

          {{-- Permissions --}}
          <div id="editPermissionsSection" class="mt-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <label class="adm-label mb-0" style="font-size:14px;font-weight:700;">
                <i class="fas fa-shield-alt me-1" style="color:var(--primary)"></i>
                {{ app()->getLocale()==='bn' ? 'পেজ অ্যাক্সেস অনুমতি' : 'Page Access Permissions' }}
              </label>
              <div class="d-flex gap-2">
                <button type="button" class="adm-btn adm-btn-ghost adm-btn-xs" onclick="selectAllPerms('edit')">
                  {{ app()->getLocale()==='bn' ? 'সব নির্বাচন' : 'Select All' }}
                </button>
                <button type="button" class="adm-btn adm-btn-ghost adm-btn-xs" onclick="clearAllPerms('edit')">
                  {{ app()->getLocale()==='bn' ? 'সব বাদ' : 'Clear All' }}
                </button>
              </div>
            </div>
            <p style="font-size:12px;color:var(--muted);margin-bottom:12px;">
              {{ app()->getLocale()==='bn'
                ? 'এই অ্যাডমিন কোন কোন পেজ ব্যবহার করতে পারবে তা নির্বাচন করুন।'
                : 'Select which pages this admin can access.' }}
            </p>
            <div class="row g-2">
              @foreach($allPerms as $key => $perm)
              <div class="col-6 col-md-4 col-lg-3">
                <label class="perm-label" data-prefix="edit" style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;transition:all .15s;background:var(--white);">
                  <input type="checkbox" name="permissions[]" value="{{ $key }}"
                    class="edit-perm-check"
                    style="width:16px;height:16px;accent-color:var(--primary);flex-shrink:0;"
                    onchange="syncPermLabel(this)">
                  <span style="font-size:12px;font-weight:600;color:var(--text);">
                    <i class="fas {{ $perm['icon'] }} me-1" style="color:var(--primary);font-size:11px;"></i>
                    {{ app()->getLocale()==='bn' ? $perm['label_bn'] : $perm['label_en'] }}
                  </span>
                </label>
              </div>
              @endforeach
            </div>
          </div>

          <div id="editSuperNote" style="display:none;" class="mt-3">
            <div style="background:rgba(217,119,6,.1);border:1px solid rgba(217,119,6,.3);border-radius:8px;padding:12px 14px;color:#fbbf24;font-size:13px;">
              <i class="fas fa-crown me-2"></i>
              {{ app()->getLocale()==='bn'
                ? 'সুপার অ্যাডমিনের সকল পেজে স্বয়ংক্রিয়ভাবে সম্পূর্ণ অ্যাক্সেস থাকবে।'
                : 'Super Admin automatically has full access to all pages.' }}
            </div>
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
function toggleAddPermissions(role) {
  document.getElementById('addPermissionsSection').style.display = role === 'super_admin' ? 'none' : 'block';
  document.getElementById('addSuperNote').style.display          = role === 'super_admin' ? 'block' : 'none';
}

function toggleEditPermissions(role) {
  document.getElementById('editPermissionsSection').style.display = role === 'super_admin' ? 'none' : 'block';
  document.getElementById('editSuperNote').style.display          = role === 'super_admin' ? 'block' : 'none';
}

function openEditModal(id, name, email, role, permissions) {
  document.getElementById('editAdminForm').action = '/admin/admins/' + id;
  document.getElementById('editName').value  = name;
  document.getElementById('editEmail').value = email;
  document.getElementById('editRole').value  = role;

  // সব checkbox reset
  document.querySelectorAll('.edit-perm-check').forEach(cb => {
    cb.checked = false;
    syncPermLabel(cb);
  });

  // saved permissions গুলো tick করো
  if (Array.isArray(permissions)) {
    permissions.forEach(key => {
      const cb = document.querySelector(`.edit-perm-check[value="${key}"]`);
      if (cb) { cb.checked = true; syncPermLabel(cb); }
    });
  }

  toggleEditPermissions(role);
  new bootstrap.Modal(document.getElementById('editAdminModal')).show();
}

function syncPermLabel(cb) {
  const label = cb.closest('label');
  if (!label) return;
  label.style.borderColor = cb.checked ? 'var(--primary)' : 'var(--border)';
  label.style.background  = cb.checked ? 'var(--primary-s)' : 'var(--white)';
}

function selectAllPerms(prefix) {
  document.querySelectorAll(`.${prefix}-perm-check`).forEach(cb => {
    cb.checked = true;
    syncPermLabel(cb);
  });
}

function clearAllPerms(prefix) {
  document.querySelectorAll(`.${prefix}-perm-check`).forEach(cb => {
    cb.checked = false;
    syncPermLabel(cb);
  });
}
</script>
@endpush
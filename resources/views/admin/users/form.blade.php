@extends('admin.layouts.app')
@section('title', $user->exists ? __t('แก้ไขผู้ใช้งาน', 'Edit user') . ' · ' . $user->name : __t('เพิ่มผู้ใช้งาน', 'Add user'))

@section('content')
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
  @csrf
  @if($user->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ข้อมูลผู้ใช้งาน', 'User details') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ชื่อ', 'Name') }} <span class="text-danger">*</span></label>
          <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="mb-2">
          <label class="form-label">{{ __t('อีเมล', 'Email') }} <span class="text-danger">*</span></label>
          <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="mb-2">
          <label class="form-label">{{ __t('เบอร์โทร', 'Phone') }}</label>
          <input class="form-control" name="phone" value="{{ old('phone', $user->phone) }}">
        </div>
        <div class="mb-2">
          <label class="form-label">
            {{ __t('รหัสผ่าน', 'Password') }} @if($user->exists)<span class="text-secondary">({{ __t('เว้นว่างถ้าไม่เปลี่ยน', 'leave blank to keep') }})</span>@else<span class="text-danger">*</span>@endif
          </label>
          <input class="form-control" type="password" name="password" autocomplete="new-password"
                 {{ $user->exists ? '' : 'required' }}>
          <div class="form-text small">{{ __t('อย่างน้อย 8 ตัวอักษร', 'At least 8 characters') }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('สิทธิ์การใช้งาน', 'Permissions') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('สิทธิ์', 'Role') }} <span class="text-danger">*</span></label>
          <select class="form-select" name="role" id="roleSelect" required>
            <option value="owner" @selected(old('role', $user->role) === 'owner')>{{ __t('เจ้าของ — จัดการได้ทุกอย่าง', 'Owner — full access') }}</option>
            <option value="manager" @selected(old('role', $user->role) === 'manager')>{{ __t('ผู้จัดการ — เห็นทุกสาขา', 'Manager — all branches') }}</option>
            <option value="staff" @selected(old('role', $user->role) === 'staff')>{{ __t('พนักงาน — เห็นเฉพาะสาขาตัวเอง', 'Staff — own branch only') }}</option>
          </select>
        </div>

        <div class="mb-2" id="branchRow">
          <label class="form-label">{{ __t('สาขาที่สังกัด', 'Assigned branch') }}</label>
          <select class="form-select" name="branch_id">
            <option value="">— {{ __t('ไม่ระบุ', 'Not set') }} —</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}" @selected(old('branch_id', $user->branch_id) == $b->id)>{{ $b->name }}</option>
            @endforeach
          </select>
          <div class="form-text small">{{ __t('พนักงานจะเห็นเฉพาะข้อมูลของสาขานี้', 'Staff only see data from this branch') }}</div>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">{{ __t('เปิดใช้งานบัญชีนี้', 'Account is active') }}</label>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
  </div>
</form>

@if($user->exists && $user->id !== auth()->id())
  <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-2"
        data-confirm="{{ __t('ยืนยันลบผู้ใช้งานนี้?', 'Delete this user?') }}">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> {{ __t('ลบ', 'Delete') }}</button>
  </form>
@endif
@endsection

@push('scripts')
<script>
var roleSelect = document.getElementById('roleSelect');
var branchRow = document.getElementById('branchRow');
function syncRole(){
  branchRow.style.display = roleSelect.value === 'staff' ? '' : 'none';
}
roleSelect.addEventListener('change', syncRole);
syncRole();
</script>
@endpush

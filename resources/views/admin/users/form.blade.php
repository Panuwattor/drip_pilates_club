@extends('admin.layouts.app')
@section('title', $user->exists ? 'แก้ไขผู้ใช้งาน · ' . $user->name : 'เพิ่มผู้ใช้งาน')

@section('content')
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
  @csrf
  @if($user->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card-panel mb-3">
        <div class="ttl">ข้อมูลผู้ใช้งาน</div>

        <div class="mb-2">
          <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
          <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="mb-2">
          <label class="form-label">อีเมล <span class="text-danger">*</span></label>
          <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="mb-2">
          <label class="form-label">เบอร์โทร</label>
          <input class="form-control" name="phone" value="{{ old('phone', $user->phone) }}">
        </div>
        <div class="mb-2">
          <label class="form-label">
            รหัสผ่าน @if($user->exists)<span class="text-secondary">(เว้นว่างถ้าไม่เปลี่ยน)</span>@else<span class="text-danger">*</span>@endif
          </label>
          <input class="form-control" type="password" name="password" autocomplete="new-password"
                 {{ $user->exists ? '' : 'required' }}>
          <div class="form-text small">อย่างน้อย 8 ตัวอักษร</div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card-panel mb-3">
        <div class="ttl">สิทธิ์การใช้งาน</div>

        <div class="mb-2">
          <label class="form-label">สิทธิ์ <span class="text-danger">*</span></label>
          <select class="form-select" name="role" id="roleSelect" required>
            <option value="owner" @selected(old('role', $user->role) === 'owner')>เจ้าของ — จัดการได้ทุกอย่าง</option>
            <option value="manager" @selected(old('role', $user->role) === 'manager')>ผู้จัดการ — เห็นทุกสาขา</option>
            <option value="staff" @selected(old('role', $user->role) === 'staff')>พนักงาน — เห็นเฉพาะสาขาตัวเอง</option>
          </select>
        </div>

        <div class="mb-2" id="branchRow">
          <label class="form-label">สาขาที่สังกัด</label>
          <select class="form-select" name="branch_id">
            <option value="">— ไม่ระบุ —</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}" @selected(old('branch_id', $user->branch_id) == $b->id)>{{ $b->name_th }}</option>
            @endforeach
          </select>
          <div class="form-text small">พนักงานจะเห็นเฉพาะข้อมูลของสาขานี้</div>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">เปิดใช้งานบัญชีนี้</label>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> บันทึก</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
  </div>
</form>

@if($user->exists && $user->id !== auth()->id())
  <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-2"
        data-confirm="ยืนยันลบผู้ใช้งานนี้?">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> ลบ</button>
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

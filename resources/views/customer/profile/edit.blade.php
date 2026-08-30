@extends('customer.layout')
@section('title', 'ข้อมูลของฉัน')

@section('content')
<div class="page-header">
  <h1>ข้อมูลของฉัน</h1>
  <p>รหัสสมาชิก {{ $customer->code }}</p>
</div>

<div class="panel mb-3">
  <a href="{{ route('customer.profile.index') }}" class="small text-decoration-none">
    <i class="bi bi-arrow-left"></i> {{ __t('กลับหน้าโปรไฟล์', 'Back to profile') }}
  </a>
</div>

@if(session('status'))
  <div class="alert alert-success py-2 small">{{ session('status') }}</div>
@endif

@if(session('error'))
  <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

@if($errors->any())
  <div class="alert alert-danger py-2 small">
    <ul class="mb-0 ps-3">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

{{-- สถานะการผูก LINE --}}
<div class="p-3 mb-3" style="background:var(--accent-soft);border-radius:12px;">
  @if($customer->hasLineLinked())
    <div class="d-flex align-items-center gap-2">
      @if($customer->line_picture_url)
        <img src="{{ $customer->line_picture_url }}" alt=""
             style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
      @endif
      <div class="flex-grow-1">
        <div class="small fw-semibold">
          <i class="bi bi-check-circle-fill" style="color:#06C755;"></i>
          ผูกกับ LINE แล้ว
        </div>
        <div class="small" style="color:var(--ink-soft);">{{ $customer->line_display_name }}</div>
      </div>
    </div>

    <form method="POST" action="{{ route('customer.line.unlink') }}" class="mt-2"
          onsubmit="return confirm('ยกเลิกการผูกบัญชี LINE?');">
      @csrf
      <button class="btn btn-sm btn-outline-secondary" type="submit">ยกเลิกการผูก</button>
    </form>
  @else
    <div class="small mb-2">
      <strong>ยังไม่ได้ผูกบัญชี LINE</strong><br>
      <span style="color:var(--ink-soft);">ผูกแล้วเข้าสู่ระบบได้ในคลิกเดียว ไม่ต้องจำรหัสผ่าน</span>
    </div>
    @include('customer.auth._line-button', ['text' => 'ผูกบัญชี LINE'])
  @endif
</div>

{{-- ข้อมูลส่วนตัว --}}
<form method="POST" action="{{ route('customer.profile.update') }}">
  @csrf @method('PUT')

  <div class="row g-2">
    <div class="col-6 mb-3">
      <label class="form-label" for="first_name">ชื่อ <span class="text-danger">*</span></label>
      <input class="form-control" type="text" id="first_name" name="first_name"
             value="{{ old('first_name', $customer->first_name) }}" required>
    </div>
    <div class="col-6 mb-3">
      <label class="form-label" for="last_name">นามสกุล</label>
      <input class="form-control" type="text" id="last_name" name="last_name"
             value="{{ old('last_name', $customer->last_name) }}">
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">เบอร์โทรศัพท์</label>
    <input class="form-control" type="tel" value="{{ $customer->phone }}" disabled>
    <div class="form-text small">เปลี่ยนเบอร์โทรได้ที่หน้าร้าน กรุณาติดต่อเจ้าหน้าที่</div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="email">อีเมล</label>
    <input class="form-control" type="email" id="email" name="email"
           value="{{ old('email', $customer->email) }}">
  </div>

  <div class="row g-2">
    <div class="col-6 mb-3">
      <label class="form-label" for="birth_date">วันเกิด</label>
      <input class="form-control" type="date" id="birth_date" name="birth_date"
             value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}">
    </div>
    <div class="col-6 mb-3">
      <label class="form-label" for="gender">เพศ</label>
      <select class="form-select" id="gender" name="gender">
        <option value="">— ไม่ระบุ —</option>
        <option value="female" @selected(old('gender', $customer->gender) === 'female')>หญิง</option>
        <option value="male" @selected(old('gender', $customer->gender) === 'male')>ชาย</option>
        <option value="other" @selected(old('gender', $customer->gender) === 'other')>อื่นๆ</option>
      </select>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="home_branch_id">สาขาที่สะดวก</label>
    <select class="form-select" id="home_branch_id" name="home_branch_id">
      <option value="">— ไม่ระบุ —</option>
      @foreach($branches as $b)
        <option value="{{ $b->id }}" @selected(old('home_branch_id', $customer->home_branch_id) == $b->id)>
          {{ $b->name_th }}
        </option>
      @endforeach
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label" for="medical_note">ข้อมูลสุขภาพที่ครูควรทราบ</label>
    <textarea class="form-control" id="medical_note" name="medical_note" rows="2"
              placeholder="เช่น เคยผ่าตัดหลัง ปวดเข่า">{{ old('medical_note', $customer->medical_note) }}</textarea>
  </div>

  <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="is_pregnant" name="is_pregnant" value="1"
           {{ old('is_pregnant', $customer->is_pregnant) ? 'checked' : '' }}>
    <label class="form-check-label small" for="is_pregnant">กำลังตั้งครรภ์</label>
  </div>

  <div class="row g-2">
    <div class="col-6 mb-3">
      <label class="form-label" for="emergency_contact_name">ผู้ติดต่อฉุกเฉิน</label>
      <input class="form-control" type="text" id="emergency_contact_name" name="emergency_contact_name"
             value="{{ old('emergency_contact_name', $customer->emergency_contact_name) }}">
    </div>
    <div class="col-6 mb-3">
      <label class="form-label" for="emergency_contact_phone">เบอร์ผู้ติดต่อ</label>
      <input class="form-control" type="tel" id="emergency_contact_phone" name="emergency_contact_phone"
             value="{{ old('emergency_contact_phone', $customer->emergency_contact_phone) }}">
    </div>
  </div>

  <button class="btn btn-accent w-100" type="submit">บันทึกข้อมูล</button>
</form>

{{-- รหัสผ่าน --}}
<hr class="my-4" style="border-color:var(--line);">

<form method="POST" action="{{ route('customer.profile.password') }}">
  @csrf @method('PUT')

  <div class="small fw-semibold mb-2">
    {{ $customer->hasPassword() ? 'เปลี่ยนรหัสผ่าน' : 'ตั้งรหัสผ่าน' }}
  </div>

  @unless($customer->hasPassword())
    <div class="alert alert-warning py-2 small">
      คุณยังไม่มีรหัสผ่าน ตั้งไว้เพื่อเข้าสู่ระบบด้วยเบอร์โทรได้ในกรณีที่ใช้ LINE ไม่ได้
    </div>
  @endunless

  @if($customer->hasPassword())
    <div class="mb-3">
      <label class="form-label" for="current_password">รหัสผ่านปัจจุบัน</label>
      <input class="form-control" type="password" id="current_password" name="current_password"
             autocomplete="current-password">
    </div>
  @endif

  <div class="mb-3">
    <label class="form-label" for="password">รหัสผ่านใหม่</label>
    <input class="form-control" type="password" id="password" name="password" autocomplete="new-password">
  </div>

  <div class="mb-3">
    <label class="form-label" for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
    <input class="form-control" type="password" id="password_confirmation" name="password_confirmation"
           autocomplete="new-password">
  </div>

  <button class="btn btn-outline-secondary w-100" type="submit">
    {{ $customer->hasPassword() ? 'เปลี่ยนรหัสผ่าน' : 'ตั้งรหัสผ่าน' }}
  </button>
</form>
@endsection

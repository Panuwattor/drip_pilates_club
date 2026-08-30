@extends('customer.auth.layout')
@section('title', 'กรอกข้อมูลเพิ่มเติม')

@section('content')

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

@if($pending)
  {{-- ขั้นยืนยัน OTP: เบอร์ที่กรอกไปตรงกับบัญชีเดิมที่แอดมินสร้างไว้ --}}
  <h1>ยืนยันเบอร์โทร</h1>
  <p class="sub">
    เบอร์ <strong>{{ $pending['phone'] }}</strong> มีข้อมูลสมาชิกอยู่แล้ว
    กรอกรหัส 6 หลักที่ส่งไปทาง SMS เพื่อเชื่อมบัญชีเดิมของคุณเข้ากับ LINE
  </p>

  @if(!empty($pending['ref_code']))
    <div class="text-center mb-3 small" style="color:var(--ink-soft);">
      รหัสอ้างอิง <strong style="color:var(--ink);letter-spacing:.1em;">{{ $pending['ref_code'] }}</strong>
      — ต้องตรงกับที่ระบุใน SMS
    </div>
  @endif

  <form method="POST" action="{{ route('customer.line.otp.verify') }}">
    @csrf

    <div class="mb-3">
      <label class="form-label" for="code">รหัสยืนยัน</label>
      <input class="form-control otp-input" type="text" id="code" name="code"
             inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
             placeholder="000000" required autofocus autocomplete="one-time-code">
    </div>

    <button class="btn btn-accent w-100" type="submit">ยืนยันและเชื่อมบัญชี</button>
  </form>

  <div class="d-flex justify-content-between mt-3 small">
    <form method="POST" action="{{ route('customer.line.otp.resend') }}">
      @csrf
      <button class="btn btn-link p-0 small text-decoration-none" type="submit">ขอรหัสใหม่</button>
    </form>

    <form method="POST" action="{{ route('customer.line.merge.cancel') }}">
      @csrf
      <button class="btn btn-link p-0 small text-decoration-none" type="submit"
              style="color:var(--ink-soft);">ใช้เบอร์อื่น</button>
    </form>
  </div>

@else
  {{-- ขั้นกรอกข้อมูล: เพิ่งสมัครผ่าน LINE เสร็จ --}}
  <h1>อีกนิดเดียว</h1>
  <p class="sub">กรอกชื่อและเบอร์โทรเพื่อเริ่มจองคลาสได้เลย</p>

  @if($customer->line_picture_url)
    <div class="text-center mb-3">
      <img src="{{ $customer->line_picture_url }}" alt=""
           style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:1px solid var(--line);">
      <div class="small mt-2" style="color:var(--ink-soft);">
        <i class="bi bi-check-circle-fill" style="color:#06C755;"></i>
        เชื่อมกับ LINE ของ {{ $customer->line_display_name }} แล้ว
      </div>
    </div>
  @endif

  <form method="POST" action="{{ route('customer.profile.complete.submit') }}">
    @csrf

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
      <label class="form-label" for="phone">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
      <input class="form-control" type="tel" id="phone" name="phone" value="{{ old('phone') }}"
             inputmode="numeric" placeholder="08xxxxxxxx" required autofocus>
      <div class="form-text small">ใช้ยืนยันตัวตนตอนเข้าคลาส และให้เจ้าหน้าที่ติดต่อกลับได้</div>
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

    <button class="btn btn-accent w-100" type="submit">บันทึกและเริ่มใช้งาน</button>
  </form>
@endif

<div class="text-center mt-3">
  <form method="POST" action="{{ route('customer.logout') }}">
    @csrf
    <button class="btn btn-link p-0 small text-decoration-none" type="submit"
            style="color:var(--ink-soft);">ออกจากระบบ</button>
  </form>
</div>
@endsection

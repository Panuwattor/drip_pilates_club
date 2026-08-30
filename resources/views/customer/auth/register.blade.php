@extends('customer.auth.layout')
@section('title', 'สมัครสมาชิก')

@section('content')
<h1>สมัครสมาชิก</h1>
<p class="sub">สร้างบัญชีเพื่อเริ่มจองคลาส</p>

@if($errors->any())
  <div class="alert alert-danger py-2 small">
    <ul class="mb-0 ps-3">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

@include('customer.auth._line-button', ['text' => 'สมัครด้วย LINE'])

@if(app(\App\Services\LineLoginService::class)->isConfigured())
  <div class="or-divider">หรือกรอกข้อมูลเอง</div>
@endif

<form method="POST" action="{{ route('customer.register') }}">
  @csrf

  <div class="row g-2">
    <div class="col-6 mb-3">
      <label class="form-label" for="first_name">ชื่อ</label>
      <input class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required autofocus>
    </div>
    <div class="col-6 mb-3">
      <label class="form-label" for="last_name">นามสกุล</label>
      <input class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}">
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="phone">เบอร์โทรศัพท์</label>
    <input class="form-control" type="tel" id="phone" name="phone" value="{{ old('phone') }}"
           inputmode="numeric" placeholder="08xxxxxxxx" required>
    <div class="form-text small">ใช้เบอร์นี้เข้าสู่ระบบ</div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="email">อีเมล <span class="text-secondary">(ไม่บังคับ)</span></label>
    <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}">
  </div>

  <div class="mb-3">
    <label class="form-label" for="home_branch_id">สาขาที่สะดวก</label>
    <select class="form-select" id="home_branch_id" name="home_branch_id">
      <option value="">— เลือกภายหลังก็ได้ —</option>
      @foreach($branches as $b)
        <option value="{{ $b->id }}" @selected(old('home_branch_id') == $b->id)>{{ $b->name_th }}</option>
      @endforeach
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label" for="password">รหัสผ่าน</label>
    <input class="form-control" type="password" id="password" name="password" required autocomplete="new-password">
    <div class="form-text small">อย่างน้อย 6 ตัวอักษร</div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="password_confirmation">ยืนยันรหัสผ่าน</label>
    <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
  </div>

  <button class="btn btn-accent w-100" type="submit">สมัครสมาชิก</button>
</form>

<div class="text-center mt-3 small">
  มีบัญชีอยู่แล้ว? <a href="{{ route('customer.login') }}">เข้าสู่ระบบ</a>
</div>
@endsection

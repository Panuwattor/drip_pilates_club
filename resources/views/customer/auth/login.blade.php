@extends('customer.auth.layout')
@section('title', 'เข้าสู่ระบบ')

@section('content')
<h1>เข้าสู่ระบบ</h1>
<p class="sub">จองคลาสและดูตารางของคุณ</p>

@if($errors->any())
  <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
@endif

@if(session('error'))
  <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

@if(session('status'))
  <div class="alert alert-success py-2 small">{{ session('status') }}</div>
@endif

@include('customer.auth._line-button')

@if(app(\App\Services\LineLoginService::class)->isConfigured())
  <div class="or-divider">หรือใช้เบอร์โทร</div>
@endif

<form method="POST" action="{{ route('customer.login') }}">
  @csrf

  <div class="mb-3">
    <label class="form-label" for="phone">เบอร์โทรศัพท์</label>
    <input class="form-control" type="tel" id="phone" name="phone" value="{{ old('phone') }}"
           inputmode="numeric" placeholder="08xxxxxxxx" required autofocus>
  </div>

  <div class="mb-3">
    <label class="form-label" for="password">รหัสผ่าน</label>
    <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
  </div>

  <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
    <label class="form-check-label small" for="remember">จดจำฉันไว้</label>
  </div>

  <button class="btn btn-accent w-100" type="submit">เข้าสู่ระบบ</button>
</form>

<div class="text-center mt-3 small">
  ยังไม่มีบัญชี? <a href="{{ route('customer.register') }}">สมัครสมาชิก</a>
</div>
@endsection

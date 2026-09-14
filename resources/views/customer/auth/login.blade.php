@extends('customer.auth.layout')
@section('title', __t('เข้าสู่ระบบ', 'Log In'))

@section('content')
<h1>{{ __t('เข้าสู่ระบบ', 'Log In') }}</h1>
<p class="sub">{{ __t('จองคลาสและดูตารางของคุณ', 'Book classes and view your schedule') }}</p>

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
  <div class="or-divider">{{ __t('หรือใช้เบอร์โทร', 'or use your phone number') }}</div>
@endif

<form method="POST" action="{{ route('customer.login') }}">
  @csrf

  <div class="mb-3">
    <label class="form-label" for="phone">{{ __t('เบอร์โทรศัพท์', 'Phone number') }}</label>
    <input class="form-control" type="tel" id="phone" name="phone" value="{{ old('phone') }}"
           inputmode="numeric" placeholder="08xxxxxxxx" required autofocus>
  </div>

  <div class="mb-3">
    <label class="form-label" for="password">{{ __t('รหัสผ่าน', 'Password') }}</label>
    <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
  </div>

  <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
    <label class="form-check-label small" for="remember">{{ __t('จดจำฉันไว้', 'Remember me') }}</label>
  </div>

  <button class="btn btn-accent w-100" type="submit">{{ __t('เข้าสู่ระบบ', 'Log In') }}</button>
</form>

<div class="text-center mt-3 small">
  {{ __t('ยังไม่มีบัญชี?', "Don't have an account?") }}
  <a href="{{ route('customer.register') }}">{{ __t('สมัครสมาชิก', 'Sign up') }}</a>
</div>
@endsection

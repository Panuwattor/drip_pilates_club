@extends('customer.auth.layout')
@section('title', __t('สมัครสมาชิก', 'Sign Up'))

@section('content')
<h1>{{ __t('สมัครสมาชิก', 'Sign Up') }}</h1>
<p class="sub">{{ __t('สร้างบัญชีเพื่อเริ่มจองคลาส', 'Create an account to start booking classes') }}</p>

@if($errors->any())
  <div class="alert alert-danger py-2 small">
    <div class="fw-semibold mb-1">{{ __t('ยังสมัครไม่ได้ กรุณาตรวจสอบข้อมูลต่อไปนี้', 'We could not sign you up yet. Please check the following:') }}</div>
    <ul class="mb-0 ps-3">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

@include('customer.auth._line-button', ['text' => __t('สมัครด้วย LINE', 'Sign up with LINE')])

@if(app(\App\Services\LineLoginService::class)->isConfigured())
  <div class="or-divider">{{ __t('หรือกรอกข้อมูลเอง', 'or fill in your details') }}</div>
@endif

<form method="POST" action="{{ route('customer.register') }}">
  @csrf

  <div class="row g-2">
    <div class="col-6 mb-3">
      <label class="form-label" for="first_name">{{ __t('ชื่อ', 'First name') }}</label>
      <input class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required autofocus>
      @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 mb-3">
      <label class="form-label" for="last_name">{{ __t('นามสกุล', 'Last name') }}</label>
      <input class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}">
      @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="phone">{{ __t('เบอร์โทรศัพท์', 'Phone number') }}</label>
    <input class="form-control @error('phone') is-invalid @enderror" type="tel" id="phone" name="phone" value="{{ old('phone') }}"
           inputmode="numeric" placeholder="08xxxxxxxx" required>
    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text small">{{ __t('ใช้เบอร์นี้เข้าสู่ระบบ', 'You will use this number to log in') }}</div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="email">{{ __t('อีเมล', 'Email') }} <span class="text-secondary">{{ __t('(ไม่บังคับ)', '(optional)') }}</span></label>
    <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="mb-3">
    <label class="form-label" for="home_branch_id">{{ __t('สาขาที่สะดวก', 'Preferred branch') }}</label>
    <select class="form-select" id="home_branch_id" name="home_branch_id">
      <option value="">— {{ __t('เลือกภายหลังก็ได้', 'You can choose later') }} —</option>
      @foreach($branches as $b)
        <option value="{{ $b->id }}" @selected(old('home_branch_id') == $b->id)>{{ $b->trans('name') }}</option>
      @endforeach
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label" for="password">{{ __t('รหัสผ่าน', 'Password') }}</label>
    <input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" required autocomplete="new-password">
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text small">{{ __t('อย่างน้อย 6 ตัวอักษร', 'At least 6 characters') }}</div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="password_confirmation">{{ __t('ยืนยันรหัสผ่าน', 'Confirm password') }}</label>
    <input class="form-control @error('password') is-invalid @enderror" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
  </div>

  <button class="btn btn-accent w-100" type="submit">{{ __t('สมัครสมาชิก', 'Sign Up') }}</button>
</form>

<div class="text-center mt-3 small">
  {{ __t('มีบัญชีอยู่แล้ว?', 'Already have an account?') }}
  <a href="{{ route('customer.login') }}">{{ __t('เข้าสู่ระบบ', 'Log in') }}</a>
</div>
@endsection

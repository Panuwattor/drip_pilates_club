@extends('customer.auth.layout')
@section('title', __t('กรอกข้อมูลเพิ่มเติม', 'Complete your profile'))

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
  <h1>{{ __t('ยืนยันเบอร์โทร', 'Verify your phone') }}</h1>
  <p class="sub">
    @if(app()->getLocale() === 'en')
      The number <strong>{{ $pending['phone'] }}</strong> already belongs to a member.
      Enter the 6-digit code sent by SMS to link your existing account with LINE.
    @else
      เบอร์ <strong>{{ $pending['phone'] }}</strong> มีข้อมูลสมาชิกอยู่แล้ว
      กรอกรหัส 6 หลักที่ส่งไปทาง SMS เพื่อเชื่อมบัญชีเดิมของคุณเข้ากับ LINE
    @endif
  </p>

  @if(!empty($pending['ref_code']))
    <div class="text-center mb-3 small" style="color:var(--ink-soft);">
      {{ __t('รหัสอ้างอิง', 'Reference code') }}
      <strong style="color:var(--ink);letter-spacing:.1em;">{{ $pending['ref_code'] }}</strong>
      — {{ __t('ต้องตรงกับที่ระบุใน SMS', 'must match the code shown in the SMS') }}
    </div>
  @endif

  <form method="POST" action="{{ route('customer.line.otp.verify') }}">
    @csrf

    <div class="mb-3">
      <label class="form-label" for="code">{{ __t('รหัสยืนยัน', 'Verification code') }}</label>
      <input class="form-control otp-input" type="text" id="code" name="code"
             inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
             placeholder="000000" required autofocus autocomplete="one-time-code">
    </div>

    <button class="btn btn-accent w-100" type="submit">{{ __t('ยืนยันและเชื่อมบัญชี', 'Verify and link account') }}</button>
  </form>

  <div class="d-flex justify-content-between mt-3 small">
    <form method="POST" action="{{ route('customer.line.otp.resend') }}">
      @csrf
      <button class="btn btn-link p-0 small text-decoration-none" type="submit">{{ __t('ขอรหัสใหม่', 'Resend code') }}</button>
    </form>

    <form method="POST" action="{{ route('customer.line.merge.cancel') }}">
      @csrf
      <button class="btn btn-link p-0 small text-decoration-none" type="submit"
              style="color:var(--ink-soft);">{{ __t('ใช้เบอร์อื่น', 'Use another number') }}</button>
    </form>
  </div>

@else
  {{-- ขั้นกรอกข้อมูล: เพิ่งสมัครผ่าน LINE เสร็จ --}}
  <h1>{{ __t('อีกนิดเดียว', 'Almost there') }}</h1>
  <p class="sub">{{ __t('กรอกชื่อและเบอร์โทรเพื่อเริ่มจองคลาสได้เลย', 'Add your name and phone number to start booking classes') }}</p>

  @if($customer->line_picture_url)
    <div class="text-center mb-3">
      <img src="{{ $customer->line_picture_url }}" alt=""
           style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:1px solid var(--line);">
      <div class="small mt-2" style="color:var(--ink-soft);">
        <i class="bi bi-check-circle-fill" style="color:#06C755;"></i>
        @if(app()->getLocale() === 'en')
          Linked with the LINE account of {{ $customer->line_display_name }}
        @else
          เชื่อมกับ LINE ของ {{ $customer->line_display_name }} แล้ว
        @endif
      </div>
    </div>
  @endif

  <form method="POST" action="{{ route('customer.profile.complete.submit') }}">
    @csrf

    <div class="row g-2">
      <div class="col-6 mb-3">
        <label class="form-label" for="first_name">{{ __t('ชื่อ', 'First name') }} <span class="text-danger">*</span></label>
        <input class="form-control" type="text" id="first_name" name="first_name"
               value="{{ old('first_name', $customer->first_name) }}" required>
      </div>
      <div class="col-6 mb-3">
        <label class="form-label" for="last_name">{{ __t('นามสกุล', 'Last name') }}</label>
        <input class="form-control" type="text" id="last_name" name="last_name"
               value="{{ old('last_name', $customer->last_name) }}">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label" for="phone">{{ __t('เบอร์โทรศัพท์', 'Phone number') }} <span class="text-danger">*</span></label>
      <input class="form-control" type="tel" id="phone" name="phone" value="{{ old('phone') }}"
             inputmode="numeric" placeholder="08xxxxxxxx" required autofocus>
      <div class="form-text small">{{ __t('ใช้ยืนยันตัวตนตอนเข้าคลาส และให้เจ้าหน้าที่ติดต่อกลับได้', 'Used to identify you at check-in and to let our staff reach you') }}</div>
    </div>

    <div class="mb-3">
      <label class="form-label" for="home_branch_id">{{ __t('สาขาที่สะดวก', 'Preferred branch') }}</label>
      <select class="form-select" id="home_branch_id" name="home_branch_id">
        <option value="">— {{ __t('ไม่ระบุ', 'Not specified') }} —</option>
        @foreach($branches as $b)
          <option value="{{ $b->id }}" @selected(old('home_branch_id', $customer->home_branch_id) == $b->id)>
            {{ $b->trans('name') }}
          </option>
        @endforeach
      </select>
    </div>

    <button class="btn btn-accent w-100" type="submit">{{ __t('บันทึกและเริ่มใช้งาน', 'Save and get started') }}</button>
  </form>
@endif

<div class="text-center mt-3">
  <form method="POST" action="{{ route('customer.logout') }}">
    @csrf
    <button class="btn btn-link p-0 small text-decoration-none" type="submit"
            style="color:var(--ink-soft);">{{ __t('ออกจากระบบ', 'Log out') }}</button>
  </form>
</div>
@endsection

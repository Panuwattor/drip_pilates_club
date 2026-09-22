@extends('admin.layouts.app')
@section('title', __t('เปลี่ยนรหัสผ่าน', 'Change password'))

@section('content')
<div class="row g-3">
  <div class="col-lg-6">
    <form method="POST" action="{{ route('admin.profile.password.update') }}">
      @csrf
      @method('PUT')

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('เปลี่ยนรหัสผ่าน', 'Change password') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('บัญชี', 'Account') }}</label>
          <input class="form-control" value="{{ auth()->user()->email }}" disabled>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('รหัสผ่านปัจจุบัน', 'Current password') }} <span class="text-danger">*</span></label>
          <input class="form-control @error('current_password') is-invalid @enderror"
                 type="password" name="current_password" autocomplete="current-password" required autofocus>
          @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('รหัสผ่านใหม่', 'New password') }} <span class="text-danger">*</span></label>
          <input class="form-control @error('password') is-invalid @enderror"
                 type="password" name="password" autocomplete="new-password" required>
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <div class="form-text small">{{ __t('อย่างน้อย 8 ตัวอักษร', 'At least 8 characters') }}</div>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ยืนยันรหัสผ่านใหม่', 'Confirm new password') }} <span class="text-danger">*</span></label>
          <input class="form-control" type="password" name="password_confirmation" autocomplete="new-password" required>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">{{ __t('บันทึกรหัสผ่าน', 'Save password') }}</button>
        <a class="btn btn-light" href="{{ route('admin.dashboard') }}">{{ __t('ยกเลิก', 'Cancel') }}</a>
      </div>
    </form>
  </div>
</div>
@endsection

@extends('admin.layouts.app')
@section('title', $customer->exists ? __t('แก้ไขข้อมูล', 'Edit') . ' · ' . $customer->full_name : __t('เพิ่มลูกค้า', 'Add customer'))

@section('content')
<form method="POST" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
  @csrf
  @if($customer->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ข้อมูลส่วนตัว', 'Personal details') }}</div>
        <div class="row g-2">
          <div class="col-md-4 mb-2">
            <label class="form-label">{{ __t('ชื่อ', 'First name') }} <span class="text-danger">*</span></label>
            <input class="form-control" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required>
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">{{ __t('นามสกุล', 'Last name') }}</label>
            <input class="form-control" name="last_name" value="{{ old('last_name', $customer->last_name) }}">
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">{{ __t('ชื่อเล่น', 'Nickname') }}</label>
            <input class="form-control" name="nickname" value="{{ old('nickname', $customer->nickname) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('เบอร์โทร', 'Phone') }} <span class="text-danger">*</span></label>
            <input class="form-control" name="phone" value="{{ old('phone', $customer->phone) }}" required>
            <div class="form-text small">{{ __t('ใช้เข้าสู่ระบบ', 'Used to sign in') }}</div>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('อีเมล', 'Email') }}</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $customer->email) }}">
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">{{ __t('วันเกิด', 'Date of birth') }}</label>
            <input class="form-control" type="date" name="birth_date"
                   value="{{ old('birth_date', $customer->birth_date?->toDateString()) }}">
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">{{ __t('เพศ', 'Gender') }}</label>
            <select class="form-select" name="gender">
              <option value="">— {{ __t('ไม่ระบุ', 'Not set') }} —</option>
              @foreach(['female'=>__t('หญิง','Female'),'male'=>__t('ชาย','Male'),'other'=>__t('อื่นๆ','Other')] as $v => $l)
                <option value="{{ $v }}" @selected(old('gender', $customer->gender) === $v)>{{ $l }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">{{ __t('ภาษาที่ใช้', 'Preferred language') }}</label>
            <select class="form-select" name="preferred_locale" required>
              <option value="th" @selected(old('preferred_locale', $customer->preferred_locale ?? 'th') === 'th')>{{ __t('ไทย', 'Thai') }}</option>
              <option value="en" @selected(old('preferred_locale', $customer->preferred_locale) === 'en')>English</option>
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('สาขาประจำ', 'Home branch') }}</label>
            <select class="form-select" name="home_branch_id">
              <option value="">— {{ __t('ไม่ระบุ', 'Not set') }} —</option>
              @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected(old('home_branch_id', $customer->home_branch_id) == $b->id)>{{ $b->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('รหัสผ่าน', 'Password') }} @unless($customer->exists)<span class="text-secondary">({{ __t('ไม่บังคับ', 'optional') }})</span>@endunless</label>
            <input class="form-control" type="password" name="password" autocomplete="new-password">
            <div class="form-text small">
              {{ $customer->exists ? __t('เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยน', 'Leave blank to keep the current password') : __t('เว้นว่างได้ ลูกค้าตั้งเองภายหลัง', 'Optional — the customer can set it later') }}
            </div>
          </div>
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ข้อมูลสุขภาพและผู้ติดต่อฉุกเฉิน', 'Health & emergency contact') }}</div>
        <div class="mb-2">
          <label class="form-label">{{ __t('หมายเหตุสุขภาพ / อาการบาดเจ็บ', 'Health notes / injuries') }}</label>
          <textarea class="form-control" name="medical_note" rows="2"
                    placeholder="{{ __t('เช่น ปวดหลังส่วนล่าง เคยผ่าตัดเข่าขวา', 'e.g. lower back pain, right knee surgery') }}">{{ old('medical_note', $customer->medical_note) }}</textarea>
          <div class="form-text small">{{ __t('ครูผู้สอนจะเห็นข้อมูลนี้ในรายชื่อผู้เรียน', 'Trainers see this on the class roster') }}</div>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="is_pregnant" name="is_pregnant" value="1"
                 {{ old('is_pregnant', $customer->is_pregnant) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_pregnant">{{ __t('กำลังตั้งครรภ์', 'Currently pregnant') }}</label>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">{{ __t('ผู้ติดต่อฉุกเฉิน', 'Emergency contact') }}</label>
            <input class="form-control" name="emergency_contact_name"
                   value="{{ old('emergency_contact_name', $customer->emergency_contact_name) }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __t('เบอร์ติดต่อฉุกเฉิน', 'Emergency phone') }}</label>
            <input class="form-control" name="emergency_contact_phone"
                   value="{{ old('emergency_contact_phone', $customer->emergency_contact_phone) }}">
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('สถานะและโน้ตภายใน', 'Status & internal notes') }}</div>
        <div class="mb-2">
          <label class="form-label">{{ __t('สถานะ', 'Status') }} <span class="text-danger">*</span></label>
          <select class="form-select" name="status" required>
            @foreach(['active'=>__t('ใช้งานอยู่','Active'),'inactive'=>__t('ไม่ใช้งาน','Inactive'),'banned'=>__t('ถูกระงับ','Banned')] as $v => $l)
              <option value="{{ $v }}" @selected(old('status', $customer->status ?? 'active') === $v)>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">{{ __t('โน้ตภายใน', 'Internal note') }}</label>
          <textarea class="form-control" name="admin_note" rows="3"
                    placeholder="{{ __t('ลูกค้าไม่เห็นข้อความนี้', 'Not visible to the customer') }}">{{ old('admin_note', $customer->admin_note) }}</textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
    <a href="{{ $customer->exists ? route('admin.customers.show', $customer) : route('admin.customers.index') }}"
       class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
  </div>
</form>
@endsection

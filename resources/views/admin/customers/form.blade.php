@extends('admin.layouts.app')
@section('title', $customer->exists ? 'แก้ไขข้อมูล · ' . $customer->full_name : 'เพิ่มลูกค้า')

@section('content')
<form method="POST" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
  @csrf
  @if($customer->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">ข้อมูลส่วนตัว</div>
        <div class="row g-2">
          <div class="col-md-4 mb-2">
            <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
            <input class="form-control" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required>
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">นามสกุล</label>
            <input class="form-control" name="last_name" value="{{ old('last_name', $customer->last_name) }}">
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">ชื่อเล่น</label>
            <input class="form-control" name="nickname" value="{{ old('nickname', $customer->nickname) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">เบอร์โทร <span class="text-danger">*</span></label>
            <input class="form-control" name="phone" value="{{ old('phone', $customer->phone) }}" required>
            <div class="form-text small">ใช้เข้าสู่ระบบ</div>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">อีเมล</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $customer->email) }}">
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">วันเกิด</label>
            <input class="form-control" type="date" name="birth_date"
                   value="{{ old('birth_date', $customer->birth_date?->toDateString()) }}">
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">เพศ</label>
            <select class="form-select" name="gender">
              <option value="">— ไม่ระบุ —</option>
              @foreach(['female'=>'หญิง','male'=>'ชาย','other'=>'อื่นๆ'] as $v => $l)
                <option value="{{ $v }}" @selected(old('gender', $customer->gender) === $v)>{{ $l }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4 mb-2">
            <label class="form-label">ภาษาที่ใช้</label>
            <select class="form-select" name="preferred_locale" required>
              <option value="th" @selected(old('preferred_locale', $customer->preferred_locale ?? 'th') === 'th')>ไทย</option>
              <option value="en" @selected(old('preferred_locale', $customer->preferred_locale) === 'en')>English</option>
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">สาขาประจำ</label>
            <select class="form-select" name="home_branch_id">
              <option value="">— ไม่ระบุ —</option>
              @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected(old('home_branch_id', $customer->home_branch_id) == $b->id)>{{ $b->name_th }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">รหัสผ่าน @unless($customer->exists)<span class="text-secondary">(ไม่บังคับ)</span>@endunless</label>
            <input class="form-control" type="password" name="password" autocomplete="new-password">
            <div class="form-text small">
              {{ $customer->exists ? 'เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยน' : 'เว้นว่างได้ ลูกค้าตั้งเองภายหลัง' }}
            </div>
          </div>
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">ข้อมูลสุขภาพและผู้ติดต่อฉุกเฉิน</div>
        <div class="mb-2">
          <label class="form-label">หมายเหตุสุขภาพ / อาการบาดเจ็บ</label>
          <textarea class="form-control" name="medical_note" rows="2"
                    placeholder="เช่น ปวดหลังส่วนล่าง เคยผ่าตัดเข่าขวา">{{ old('medical_note', $customer->medical_note) }}</textarea>
          <div class="form-text small">ครูผู้สอนจะเห็นข้อมูลนี้ในรายชื่อผู้เรียน</div>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="is_pregnant" name="is_pregnant" value="1"
                 {{ old('is_pregnant', $customer->is_pregnant) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_pregnant">กำลังตั้งครรภ์</label>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">ผู้ติดต่อฉุกเฉิน</label>
            <input class="form-control" name="emergency_contact_name"
                   value="{{ old('emergency_contact_name', $customer->emergency_contact_name) }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">เบอร์ติดต่อฉุกเฉิน</label>
            <input class="form-control" name="emergency_contact_phone"
                   value="{{ old('emergency_contact_phone', $customer->emergency_contact_phone) }}">
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">สถานะและโน้ตภายใน</div>
        <div class="mb-2">
          <label class="form-label">สถานะ <span class="text-danger">*</span></label>
          <select class="form-select" name="status" required>
            @foreach(['active'=>'ใช้งานอยู่','inactive'=>'ไม่ใช้งาน','banned'=>'ถูกระงับ'] as $v => $l)
              <option value="{{ $v }}" @selected(old('status', $customer->status ?? 'active') === $v)>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">โน้ตภายใน</label>
          <textarea class="form-control" name="admin_note" rows="3"
                    placeholder="ลูกค้าไม่เห็นข้อความนี้">{{ old('admin_note', $customer->admin_note) }}</textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> บันทึก</button>
    <a href="{{ $customer->exists ? route('admin.customers.show', $customer) : route('admin.customers.index') }}"
       class="btn btn-outline-secondary">ยกเลิก</a>
  </div>
</form>
@endsection

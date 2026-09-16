@extends('admin.layouts.app')
@section('title', $trainer->exists ? __t('แก้ไขครู', 'Edit trainer') . ' · ' . $trainer->name : __t('เพิ่มครูผู้สอน', 'Add trainer'))

@section('content')
<form method="POST" enctype="multipart/form-data"
      action="{{ $trainer->exists ? route('admin.trainers.update', $trainer) : route('admin.trainers.store') }}">
  @csrf
  @if($trainer->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ข้อมูลครู', 'Trainer details') }}</div>

        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <label class="form-label">{{ __t('รหัสครู', 'Trainer code') }} <span class="text-danger">*</span></label>
            <input class="form-control" name="code" value="{{ old('code', $trainer->code) }}" placeholder="TR-01" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __t('ลำดับการแสดง', 'Display order') }}</label>
            <input class="form-control" type="number" name="sort_order" value="{{ old('sort_order', $trainer->sort_order ?? 0) }}">
          </div>
        </div>

        @include('admin.partials.bilingual-field', [
          'name' => 'name', 'label' => __t('ชื่อที่แสดง', 'Display name'), 'model' => $trainer, 'required' => true,
          'placeholder' => __t('เช่น ครูแนน / Coach Nan', 'e.g. ครูแนน / Coach Nan'),
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'nickname', 'label' => __t('ชื่อเล่น (ใช้บนตาราง)', 'Nickname (shown on the schedule)'), 'model' => $trainer,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'bio', 'label' => __t('ประวัติโดยย่อ', 'Short bio'), 'model' => $trainer, 'type' => 'textarea', 'rows' => 3,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'specialties', 'label' => __t('ความถนัด', 'Specialties'), 'model' => $trainer,
          'placeholder' => __t('คั่นด้วยจุลภาค เช่น Reformer, Prenatal', 'Comma separated, e.g. Reformer, Prenatal'),
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'certifications', 'label' => __t('ใบรับรอง', 'Certifications'), 'model' => $trainer,
        ])
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ติดต่อ', 'Contact') }}</div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">{{ __t('เบอร์โทร', 'Phone') }}</label>
            <input class="form-control" name="phone" value="{{ old('phone', $trainer->phone) }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __t('อีเมล', 'Email') }}</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $trainer->email) }}">
          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
        <a href="{{ route('admin.trainers.index') }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('รูปโปรไฟล์', 'Profile photo') }}</div>
        @if($trainer->avatar)
          <img src="{{ asset($trainer->avatar) }}" alt=""
               style="width:88px;height:88px;border-radius:50%;object-fit:cover;margin-bottom:.6rem;">
        @endif
        <input class="form-control form-control-sm" type="file" name="avatar_file" accept="image/*">
        <div class="form-text small">{{ __t('ไฟล์ภาพไม่เกิน 2 MB', 'Image up to 2 MB') }}</div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('สอนที่สาขา', 'Teaches at') }}</div>
        @php $selected = old('branch_ids', $trainer->exists ? $trainer->branches->pluck('id')->all() : $branches->pluck('id')->all()); @endphp
        @foreach($branches as $branch)
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="branch_{{ $branch->id }}"
                   name="branch_ids[]" value="{{ $branch->id }}"
                   {{ in_array($branch->id, $selected) ? 'checked' : '' }}>
            <label class="form-check-label" for="branch_{{ $branch->id }}">{{ $branch->name }}</label>
          </div>
        @endforeach
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('สถานะ', 'Status') }}</div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $trainer->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">{{ __t('เปิดใช้งาน', 'Active') }}</label>
        </div>
      </div>

      @if($trainer->exists)
        <div class="card-panel">
          <div class="ttl">{{ __t('ลิงก์ตารางสอนส่วนตัว', 'Personal schedule link') }}</div>
          <p class="small text-secondary">
            {{ __t('ส่งลิงก์นี้ให้ครู เปิดดูตารางและรายชื่อผู้เรียนได้โดยไม่ต้องเข้าสู่ระบบ', 'Send this link to the trainer — they can view their schedule and rosters without signing in.') }}
          </p>
          <div class="input-group input-group-sm mb-2">
            <input class="form-control" id="trainerLink" readonly
                   value="{{ route('trainer.schedule', $trainer->public_token) }}">
            <button class="btn btn-outline-secondary" type="button" id="copyBtn"><i class="bi bi-clipboard"></i></button>
          </div>
          <div class="alert-soft small mb-2">
            <i class="bi bi-shield-exclamation"></i>
            {{ __t('ใครมีลิงก์นี้ก็เปิดดูได้ รวมถึงหมายเหตุสุขภาพของลูกค้า ถ้าลิงก์หลุดให้สร้างใหม่', 'Anyone with this link can open it, including customers\' health notes. Generate a new one if it leaks.') }}
          </div>
        </div>
      @endif
    </div>
  </div>
</form>

@if($trainer->exists)
  <div class="d-flex gap-2 mt-2">
    <form method="POST" action="{{ route('admin.trainers.regenerate', $trainer) }}"
          data-confirm="{{ __t('สร้างลิงก์ใหม่? ลิงก์เดิมที่ส่งให้ครูไปแล้วจะใช้ไม่ได้อีก', 'Generate a new link? The old one will stop working.') }}">
      @csrf
      <button class="btn btn-sm btn-outline-secondary" type="submit">
        <i class="bi bi-arrow-repeat"></i> {{ __t('สร้างลิงก์ใหม่', 'New link') }}
      </button>
    </form>

    <form method="POST" action="{{ route('admin.trainers.destroy', $trainer) }}"
          data-confirm="{{ __t('ยืนยันลบครูคนนี้?', 'Delete this trainer?') }}">
      @csrf @method('DELETE')
      <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> {{ __t('ลบ', 'Delete') }}</button>
    </form>
  </div>
@endif
@endsection

@push('scripts')
<script>
var copyBtn = document.getElementById('copyBtn');
copyBtn && copyBtn.addEventListener('click', function(){
  var input = document.getElementById('trainerLink');
  navigator.clipboard.writeText(input.value).then(function(){
    copyBtn.innerHTML = '<i class="bi bi-check-lg"></i>';
    setTimeout(function(){ copyBtn.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 1400);
  }).catch(function(){ input.select(); document.execCommand('copy'); });
});
</script>
@endpush

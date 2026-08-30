@extends('admin.layouts.app')
@section('title', $trainer->exists ? 'แก้ไขครู · ' . $trainer->name_th : 'เพิ่มครูผู้สอน')

@section('content')
<form method="POST" enctype="multipart/form-data"
      action="{{ $trainer->exists ? route('admin.trainers.update', $trainer) : route('admin.trainers.store') }}">
  @csrf
  @if($trainer->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">ข้อมูลครู</div>

        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <label class="form-label">รหัสครู <span class="text-danger">*</span></label>
            <input class="form-control" name="code" value="{{ old('code', $trainer->code) }}" placeholder="TR-01" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">ลำดับการแสดง</label>
            <input class="form-control" type="number" name="sort_order" value="{{ old('sort_order', $trainer->sort_order ?? 0) }}">
          </div>
        </div>

        @include('admin.partials.bilingual-field', [
          'name' => 'name', 'label' => 'ชื่อที่แสดง', 'model' => $trainer, 'required' => true,
          'placeholder' => 'เช่น ครูแนน / Coach Nan',
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'nickname', 'label' => 'ชื่อเล่น (ใช้บนตาราง)', 'model' => $trainer,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'bio', 'label' => 'ประวัติโดยย่อ', 'model' => $trainer, 'type' => 'textarea', 'rows' => 3,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'specialties', 'label' => 'ความถนัด', 'model' => $trainer,
          'placeholder' => 'คั่นด้วยจุลภาค เช่น Reformer, Prenatal',
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'certifications', 'label' => 'ใบรับรอง', 'model' => $trainer,
        ])
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">ติดต่อ</div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">เบอร์โทร</label>
            <input class="form-control" name="phone" value="{{ old('phone', $trainer->phone) }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">อีเมล</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $trainer->email) }}">
          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> บันทึก</button>
        <a href="{{ route('admin.trainers.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">รูปโปรไฟล์</div>
        @if($trainer->avatar)
          <img src="{{ asset($trainer->avatar) }}" alt=""
               style="width:88px;height:88px;border-radius:50%;object-fit:cover;margin-bottom:.6rem;">
        @endif
        <input class="form-control form-control-sm" type="file" name="avatar_file" accept="image/*">
        <div class="form-text small">ไฟล์ภาพไม่เกิน 2 MB</div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">สอนที่สาขา</div>
        @php $selected = old('branch_ids', $trainer->exists ? $trainer->branches->pluck('id')->all() : $branches->pluck('id')->all()); @endphp
        @foreach($branches as $branch)
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="branch_{{ $branch->id }}"
                   name="branch_ids[]" value="{{ $branch->id }}"
                   {{ in_array($branch->id, $selected) ? 'checked' : '' }}>
            <label class="form-check-label" for="branch_{{ $branch->id }}">{{ $branch->name_th }}</label>
          </div>
        @endforeach
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">สถานะ</div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $trainer->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">เปิดใช้งาน</label>
        </div>
      </div>

      @if($trainer->exists)
        <div class="card-panel">
          <div class="ttl">ลิงก์ตารางสอนส่วนตัว</div>
          <p class="small text-secondary">
            ส่งลิงก์นี้ให้ครู เปิดดูตารางและรายชื่อผู้เรียนได้โดยไม่ต้องเข้าสู่ระบบ
          </p>
          <div class="input-group input-group-sm mb-2">
            <input class="form-control" id="trainerLink" readonly
                   value="{{ route('trainer.schedule', $trainer->public_token) }}">
            <button class="btn btn-outline-secondary" type="button" id="copyBtn"><i class="bi bi-clipboard"></i></button>
          </div>
          <div class="alert-soft small mb-2">
            <i class="bi bi-shield-exclamation"></i>
            ใครมีลิงก์นี้ก็เปิดดูได้ รวมถึงหมายเหตุสุขภาพของลูกค้า ถ้าลิงก์หลุดให้สร้างใหม่
          </div>
        </div>
      @endif
    </div>
  </div>
</form>

@if($trainer->exists)
  <div class="d-flex gap-2 mt-2">
    <form method="POST" action="{{ route('admin.trainers.regenerate', $trainer) }}"
          data-confirm="สร้างลิงก์ใหม่? ลิงก์เดิมที่ส่งให้ครูไปแล้วจะใช้ไม่ได้อีก">
      @csrf
      <button class="btn btn-sm btn-outline-secondary" type="submit">
        <i class="bi bi-arrow-repeat"></i> สร้างลิงก์ใหม่
      </button>
    </form>

    <form method="POST" action="{{ route('admin.trainers.destroy', $trainer) }}"
          data-confirm="ยืนยันลบครูคนนี้?">
      @csrf @method('DELETE')
      <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> ลบ</button>
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

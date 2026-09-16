@extends('admin.layouts.app')
@section('title', $classType->exists ? __t('แก้ไขประเภทคลาส', 'Edit class type') . ' · ' . $classType->name : __t('เพิ่มประเภทคลาส', 'Add class type'))

@section('content')
<form method="POST" action="{{ $classType->exists ? route('admin.class-types.update', $classType) : route('admin.class-types.store') }}">
  @csrf
  @if($classType->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ข้อมูลคลาส', 'Class details') }}</div>

        <div class="mb-3">
          <label class="form-label">{{ __t('รหัสคลาส', 'Class code') }} <span class="text-danger">*</span></label>
          <input class="form-control" name="code" value="{{ old('code', $classType->code) }}"
                 placeholder="reformer-flow" required>
        </div>

        @include('admin.partials.bilingual-field', [
          'name' => 'name', 'label' => __t('ชื่อคลาส', 'Class name'), 'model' => $classType, 'required' => true,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'description', 'label' => __t('คำอธิบาย', 'Description'), 'model' => $classType, 'type' => 'textarea', 'rows' => 3,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'suitable_for', 'label' => __t('เหมาะกับใคร', 'Suitable for'), 'model' => $classType,
        ])
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('รายละเอียดการจัดคลาส', 'Class setup') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ระดับ', 'Level') }} <span class="text-danger">*</span></label>
          <select class="form-select" name="level" required>
            @foreach(['all'=>__t('ทุกระดับ','All levels'),'beginner'=>__t('เริ่มต้น','Beginner'),'intermediate'=>__t('ระดับกลาง','Intermediate'),'advanced'=>__t('ระดับสูง','Advanced')] as $v => $l)
              <option value="{{ $v }}" @selected(old('level', $classType->level) === $v)>{{ $l }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('อุปกรณ์', 'Equipment') }} <span class="text-danger">*</span></label>
          <select class="form-select" name="equipment_type" required>
            @foreach(['reformer'=>'Reformer','mat'=>'Mat','cadillac'=>'Cadillac','chair'=>'Chair','mixed'=>__t('ผสม','Mixed')] as $v => $l)
              <option value="{{ $v }}" @selected(old('equipment_type', $classType->equipment_type) === $v)>{{ $l }}</option>
            @endforeach
          </select>
        </div>

        <div class="row g-2">
          <div class="col-6 mb-2">
            <label class="form-label">{{ __t('ระยะเวลา', 'Duration') }} <span class="text-danger">*</span></label>
            <div class="input-group">
              <input class="form-control" type="number" name="duration_min"
                     value="{{ old('duration_min', $classType->duration_min ?? 50) }}" min="15" required>
              <span class="input-group-text">{{ __t('นาที', 'min') }}</span>
            </div>
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">{{ __t('ที่นั่งเริ่มต้น', 'Default seats') }} <span class="text-danger">*</span></label>
            <input class="form-control" type="number" name="default_capacity"
                   value="{{ old('default_capacity', $classType->default_capacity ?? 8) }}" min="1" required>
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">{{ __t('ใช้เครดิต', 'Credit cost') }} <span class="text-danger">*</span></label>
            <input class="form-control" type="number" step="0.5" name="credit_cost"
                   value="{{ old('credit_cost', $classType->credit_cost ?? 1) }}" min="0" required>
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">{{ __t('สีในปฏิทิน', 'Calendar colour') }}</label>
            <input class="form-control form-control-color w-100" type="color" name="color"
                   value="{{ old('color', $classType->color ?: '#6C88B4') }}">
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">{{ __t('ลำดับการแสดง', 'Display order') }}</label>
            <input class="form-control" type="number" name="sort_order"
                   value="{{ old('sort_order', $classType->sort_order ?? 0) }}">
          </div>
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $classType->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">{{ __t('เปิดใช้งาน', 'Active') }}</label>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
    <a href="{{ route('admin.class-types.index') }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
  </div>
</form>

@if($classType->exists)
  <form method="POST" action="{{ route('admin.class-types.destroy', $classType) }}" class="mt-2"
        data-confirm="{{ __t('ยืนยันลบประเภทคลาสนี้?', 'Delete this class type?') }}">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> {{ __t('ลบ', 'Delete') }}</button>
  </form>
@endif
@endsection

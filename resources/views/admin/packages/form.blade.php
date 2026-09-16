@extends('admin.layouts.app')
@section('title', $package->exists ? __t('แก้ไขแพ็กเกจ', 'Edit package') . ' · ' . $package->name : __t('เพิ่มแพ็กเกจ', 'Add package'))

@section('content')
<form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}">
  @csrf
  @if($package->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ข้อมูลแพ็กเกจ', 'Package details') }}</div>

        <div class="mb-3">
          <label class="form-label">{{ __t('รหัสแพ็กเกจ', 'Package code') }} <span class="text-danger">*</span></label>
          <input class="form-control" name="code" value="{{ old('code', $package->code) }}" placeholder="pack-10" required>
        </div>

        @include('admin.partials.bilingual-field', [
          'name' => 'name', 'label' => __t('ชื่อแพ็กเกจ', 'Package name'), 'model' => $package, 'required' => true,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'description', 'label' => __t('คำอธิบาย', 'Description'), 'model' => $package, 'type' => 'textarea', 'rows' => 2,
        ])
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('โควตาการจอง (ใช้กับแพ็กเหมาจ่ายเป็นหลัก)', 'Booking quota (mainly for unlimited packages)') }}</div>
        <div class="alert-soft small mb-3">
          {{ __t('กันลูกค้าจองรัวทิ้งไว้ เว้นว่าง = ไม่จำกัด', 'Stops customers hoarding bookings. Leave blank for unlimited.') }}
        </div>
        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label">{{ __t('สูงสุดต่อวัน', 'Max per day') }}</label>
            <input class="form-control" type="number" name="max_per_day"
                   value="{{ old('max_per_day', $package->max_per_day) }}" min="1">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __t('สูงสุดต่อสัปดาห์', 'Max per week') }}</label>
            <input class="form-control" type="number" name="max_per_week"
                   value="{{ old('max_per_week', $package->max_per_week) }}" min="1">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __t('จองล่วงหน้าค้างได้', 'Max open bookings') }}</label>
            <input class="form-control" type="number" name="max_future_bookings"
                   value="{{ old('max_future_bookings', $package->max_future_bookings) }}" min="1">
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ชนิดและราคา', 'Type & pricing') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ชนิดแพ็กเกจ', 'Package type') }} <span class="text-danger">*</span></label>
          <select class="form-select" name="type" id="typeSelect" required>
            <option value="credit_pack" @selected(old('type', $package->type) === 'credit_pack')>{{ __t('นับครั้ง (ตัดเครดิต)', 'Credit pack (deducts credits)') }}</option>
            <option value="unlimited" @selected(old('type', $package->type) === 'unlimited')>{{ __t('เหมาจ่าย (ไม่ตัดเครดิต)', 'Unlimited (no credit deduction)') }}</option>
            <option value="trial" @selected(old('type', $package->type) === 'trial')>{{ __t('ทดลอง (ซื้อได้ครั้งเดียว)', 'Trial (one purchase only)') }}</option>
          </select>
        </div>

        <div class="mb-2" id="creditRow">
          <label class="form-label">{{ __t('จำนวนครั้ง', 'Number of credits') }} <span class="text-danger">*</span></label>
          <input class="form-control" type="number" name="credit_amount"
                 value="{{ old('credit_amount', $package->credit_amount) }}" min="1">
          <div class="form-text small">{{ __t('แพ็กเหมาจ่ายไม่ต้องกรอกช่องนี้', 'Not needed for unlimited packages') }}</div>
        </div>

        <div class="row g-2">
          <div class="col-6 mb-2">
            <label class="form-label">{{ __t('ราคา', 'Price') }} <span class="text-danger">*</span></label>
            <div class="input-group">
              <input class="form-control" type="number" step="0.01" name="price"
                     value="{{ old('price', $package->price) }}" min="0" required>
              <span class="input-group-text">฿</span>
            </div>
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">{{ __t('ราคาก่อนลด', 'Compare-at price') }}</label>
            <div class="input-group">
              <input class="form-control" type="number" step="0.01" name="compare_at_price"
                     value="{{ old('compare_at_price', $package->compare_at_price) }}" min="0">
              <span class="input-group-text">฿</span>
            </div>
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">{{ __t('ราคาต่อคลาส', 'Price per class') }}</label>
            <div class="input-group">
              <input class="form-control" type="number" step="0.01" name="price_per_class"
                     value="{{ old('price_per_class', $package->price_per_class) }}" min="0">
              <span class="input-group-text">฿</span>
            </div>
            <div class="form-text small">{{ __t('ไว้โชว์ในเมนูเฉยๆ เช่น 2,490฿/คลาส', 'Display only, e.g. ฿2,490/class') }}</div>
          </div>
        </div>

        <div class="row g-2">
          <div class="col-6 mb-2">
            <label class="form-label">{{ __t('อายุการใช้งาน', 'Validity') }} <span class="text-danger">*</span></label>
            <div class="input-group">
              <input class="form-control" type="number" name="valid_days"
                     value="{{ old('valid_days', $package->valid_days ?? 90) }}" min="1" required>
              <span class="input-group-text">{{ __t('วัน', 'days') }}</span>
            </div>
            <div class="form-text small">{{ __t('ตัวที่ระบบใช้คำนวณจริง', 'The value the system actually calculates with') }}</div>
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">{{ __t('แสดงเป็น', 'Display as') }}</label>
            <div class="input-group">
              <input class="form-control" type="number" name="valid_months"
                     value="{{ old('valid_months', $package->valid_months) }}" min="1">
              <span class="input-group-text">{{ __t('เดือน', 'months') }}</span>
            </div>
            <div class="form-text small">{{ __t('ไว้โชว์ในเมนูเฉยๆ', 'Display only') }}</div>
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ลำดับการแสดง', 'Display order') }}</label>
          <input class="form-control" type="number" name="sort_order"
                 value="{{ old('sort_order', $package->sort_order ?? 0) }}">
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ตัวเลือก', 'Options') }}</div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="all_class_types" name="all_class_types" value="1"
                 {{ old('all_class_types', $package->all_class_types ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="all_class_types">{{ __t('ใช้ได้กับทุกประเภทคลาส', 'Valid for all class types') }}</label>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="all_branches" name="all_branches" value="1"
                 {{ old('all_branches', $package->all_branches ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="all_branches">{{ __t('ขายได้ทุกสาขา', 'Sold at all branches') }}</label>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="once_per_customer" name="once_per_customer" value="1"
                 {{ old('once_per_customer', $package->once_per_customer) ? 'checked' : '' }}>
          <label class="form-check-label" for="once_per_customer">{{ __t('ซื้อได้ครั้งเดียวต่อคน', 'One purchase per customer') }}</label>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                 {{ old('is_public', $package->is_public ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_public">{{ __t('แสดงบนเว็บให้ลูกค้าเห็น', 'Visible to customers on the website') }}</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">{{ __t('เปิดขาย', 'On sale') }}</label>
        </div>
      </div>

      <div class="card-panel mb-3" id="classTypeBox">
        <div class="ttl">{{ __t('ใช้ได้กับคลาสเหล่านี้เท่านั้น', 'Valid only for these classes') }}</div>
        @php $selected = old('class_type_ids', $package->exists ? $package->classTypes->pluck('id')->all() : []); @endphp
        @foreach($classTypes as $ct)
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="ct_{{ $ct->id }}"
                   name="class_type_ids[]" value="{{ $ct->id }}"
                   {{ in_array($ct->id, $selected) ? 'checked' : '' }}>
            <label class="form-check-label" for="ct_{{ $ct->id }}">{{ $ct->name }}</label>
          </div>
        @endforeach
      </div>

      <div class="card-panel mb-3" id="branchBox">
        <div class="ttl">{{ __t('ขายเฉพาะสาขาเหล่านี้', 'Sold only at these branches') }}</div>
        <div class="form-text small mb-2">{{ __t('ใช้เมื่อแต่ละสาขาคิดราคาไม่เท่ากัน', 'Use when branches charge different prices') }}</div>
        @php $selectedBranches = old('branch_ids', $package->exists ? $package->branches->pluck('id')->all() : []); @endphp
        @foreach($branches as $b)
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="br_{{ $b->id }}"
                   name="branch_ids[]" value="{{ $b->id }}"
                   {{ in_array($b->id, $selectedBranches) ? 'checked' : '' }}>
            <label class="form-check-label" for="br_{{ $b->id }}">
              {{ $b->name }}
              @unless($b->is_active)<span class="text-muted small">({{ __t('ปิดใช้งาน', 'inactive') }})</span>@endunless
            </label>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
    <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
  </div>
</form>

@if($package->exists)
  <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="mt-2"
        data-confirm="{{ __t('ยืนยันลบแพ็กเกจนี้?', 'Delete this package?') }}">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> {{ __t('ลบ', 'Delete') }}</button>
  </form>
@endif
@endsection

@push('scripts')
<script>
var typeSelect = document.getElementById('typeSelect');
var creditRow = document.getElementById('creditRow');
var allTypes = document.getElementById('all_class_types');
var classTypeBox = document.getElementById('classTypeBox');
var allBranches = document.getElementById('all_branches');
var branchBox = document.getElementById('branchBox');

function syncType(){
  var unlimited = typeSelect.value === 'unlimited';
  creditRow.style.display = unlimited ? 'none' : '';
  creditRow.querySelector('input').required = !unlimited;
}
function syncClassTypes(){
  classTypeBox.style.display = allTypes.checked ? 'none' : '';
}
function syncBranches(){
  branchBox.style.display = allBranches.checked ? 'none' : '';
}

typeSelect.addEventListener('change', syncType);
allTypes.addEventListener('change', syncClassTypes);
allBranches.addEventListener('change', syncBranches);
syncType();
syncClassTypes();
syncBranches();
</script>
@endpush

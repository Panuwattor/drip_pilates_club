@extends('admin.layouts.app')
@section('title', $package->exists ? 'แก้ไขแพ็กเกจ · ' . $package->name_th : 'เพิ่มแพ็กเกจ')

@section('content')
<form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}">
  @csrf
  @if($package->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">ข้อมูลแพ็กเกจ</div>

        <div class="mb-3">
          <label class="form-label">รหัสแพ็กเกจ <span class="text-danger">*</span></label>
          <input class="form-control" name="code" value="{{ old('code', $package->code) }}" placeholder="pack-10" required>
        </div>

        @include('admin.partials.bilingual-field', [
          'name' => 'name', 'label' => 'ชื่อแพ็กเกจ', 'model' => $package, 'required' => true,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'description', 'label' => 'คำอธิบาย', 'model' => $package, 'type' => 'textarea', 'rows' => 2,
        ])
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">โควตาการจอง (ใช้กับแพ็กเหมาจ่ายเป็นหลัก)</div>
        <div class="alert-soft small mb-3">
          กันลูกค้าจองรัวทิ้งไว้ เว้นว่าง = ไม่จำกัด
        </div>
        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label">สูงสุดต่อวัน</label>
            <input class="form-control" type="number" name="max_per_day"
                   value="{{ old('max_per_day', $package->max_per_day) }}" min="1">
          </div>
          <div class="col-md-4">
            <label class="form-label">สูงสุดต่อสัปดาห์</label>
            <input class="form-control" type="number" name="max_per_week"
                   value="{{ old('max_per_week', $package->max_per_week) }}" min="1">
          </div>
          <div class="col-md-4">
            <label class="form-label">จองล่วงหน้าค้างได้</label>
            <input class="form-control" type="number" name="max_future_bookings"
                   value="{{ old('max_future_bookings', $package->max_future_bookings) }}" min="1">
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">ชนิดและราคา</div>

        <div class="mb-2">
          <label class="form-label">ชนิดแพ็กเกจ <span class="text-danger">*</span></label>
          <select class="form-select" name="type" id="typeSelect" required>
            <option value="credit_pack" @selected(old('type', $package->type) === 'credit_pack')>นับครั้ง (ตัดเครดิต)</option>
            <option value="unlimited" @selected(old('type', $package->type) === 'unlimited')>เหมาจ่าย (ไม่ตัดเครดิต)</option>
            <option value="trial" @selected(old('type', $package->type) === 'trial')>ทดลอง (ซื้อได้ครั้งเดียว)</option>
          </select>
        </div>

        <div class="mb-2" id="creditRow">
          <label class="form-label">จำนวนครั้ง <span class="text-danger">*</span></label>
          <input class="form-control" type="number" name="credit_amount"
                 value="{{ old('credit_amount', $package->credit_amount) }}" min="1">
          <div class="form-text small">แพ็กเหมาจ่ายไม่ต้องกรอกช่องนี้</div>
        </div>

        <div class="row g-2">
          <div class="col-6 mb-2">
            <label class="form-label">ราคา <span class="text-danger">*</span></label>
            <div class="input-group">
              <input class="form-control" type="number" step="0.01" name="price"
                     value="{{ old('price', $package->price) }}" min="0" required>
              <span class="input-group-text">฿</span>
            </div>
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">ราคาก่อนลด</label>
            <div class="input-group">
              <input class="form-control" type="number" step="0.01" name="compare_at_price"
                     value="{{ old('compare_at_price', $package->compare_at_price) }}" min="0">
              <span class="input-group-text">฿</span>
            </div>
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">ราคาต่อคลาส</label>
            <div class="input-group">
              <input class="form-control" type="number" step="0.01" name="price_per_class"
                     value="{{ old('price_per_class', $package->price_per_class) }}" min="0">
              <span class="input-group-text">฿</span>
            </div>
            <div class="form-text small">ไว้โชว์ในเมนูเฉยๆ เช่น 2,490฿/คลาส</div>
          </div>
        </div>

        <div class="row g-2">
          <div class="col-6 mb-2">
            <label class="form-label">อายุการใช้งาน <span class="text-danger">*</span></label>
            <div class="input-group">
              <input class="form-control" type="number" name="valid_days"
                     value="{{ old('valid_days', $package->valid_days ?? 90) }}" min="1" required>
              <span class="input-group-text">วัน</span>
            </div>
            <div class="form-text small">ตัวที่ระบบใช้คำนวณจริง</div>
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">แสดงเป็น</label>
            <div class="input-group">
              <input class="form-control" type="number" name="valid_months"
                     value="{{ old('valid_months', $package->valid_months) }}" min="1">
              <span class="input-group-text">เดือน</span>
            </div>
            <div class="form-text small">ไว้โชว์ในเมนูเฉยๆ</div>
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">ลำดับการแสดง</label>
          <input class="form-control" type="number" name="sort_order"
                 value="{{ old('sort_order', $package->sort_order ?? 0) }}">
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">ตัวเลือก</div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="all_class_types" name="all_class_types" value="1"
                 {{ old('all_class_types', $package->all_class_types ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="all_class_types">ใช้ได้กับทุกประเภทคลาส</label>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="all_branches" name="all_branches" value="1"
                 {{ old('all_branches', $package->all_branches ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="all_branches">ขายได้ทุกสาขา</label>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="once_per_customer" name="once_per_customer" value="1"
                 {{ old('once_per_customer', $package->once_per_customer) ? 'checked' : '' }}>
          <label class="form-check-label" for="once_per_customer">ซื้อได้ครั้งเดียวต่อคน</label>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                 {{ old('is_public', $package->is_public ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_public">แสดงบนเว็บให้ลูกค้าเห็น</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">เปิดขาย</label>
        </div>
      </div>

      <div class="card-panel mb-3" id="classTypeBox">
        <div class="ttl">ใช้ได้กับคลาสเหล่านี้เท่านั้น</div>
        @php $selected = old('class_type_ids', $package->exists ? $package->classTypes->pluck('id')->all() : []); @endphp
        @foreach($classTypes as $ct)
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="ct_{{ $ct->id }}"
                   name="class_type_ids[]" value="{{ $ct->id }}"
                   {{ in_array($ct->id, $selected) ? 'checked' : '' }}>
            <label class="form-check-label" for="ct_{{ $ct->id }}">{{ $ct->name_th }}</label>
          </div>
        @endforeach
      </div>

      <div class="card-panel mb-3" id="branchBox">
        <div class="ttl">ขายเฉพาะสาขาเหล่านี้</div>
        <div class="form-text small mb-2">ใช้เมื่อแต่ละสาขาคิดราคาไม่เท่ากัน</div>
        @php $selectedBranches = old('branch_ids', $package->exists ? $package->branches->pluck('id')->all() : []); @endphp
        @foreach($branches as $b)
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="br_{{ $b->id }}"
                   name="branch_ids[]" value="{{ $b->id }}"
                   {{ in_array($b->id, $selectedBranches) ? 'checked' : '' }}>
            <label class="form-check-label" for="br_{{ $b->id }}">
              {{ $b->name_th }}
              @unless($b->is_active)<span class="text-muted small">(ปิดใช้งาน)</span>@endunless
            </label>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> บันทึก</button>
    <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
  </div>
</form>

@if($package->exists)
  <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="mt-2"
        data-confirm="ยืนยันลบแพ็กเกจนี้?">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> ลบ</button>
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

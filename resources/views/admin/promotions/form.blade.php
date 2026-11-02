@extends('admin.layouts.app')
@section('title', $promotion->exists ? __t('แก้ไขโปรโมชั่น', 'Edit promotion') . ' · ' . $promotion->name : __t('เพิ่มโปรโมชั่น', 'Add promotion'))

@section('content')
<form method="POST" action="{{ $promotion->exists ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}">
  @csrf
  @if($promotion->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ข้อมูลโปรโมชั่น', 'Promotion details') }}</div>

        <div class="mb-3">
          <label class="form-label">{{ __t('รหัสโปรโมชั่น', 'Promotion code') }} <span class="text-danger">*</span></label>
          <input class="form-control" name="code" value="{{ old('code', $promotion->code) }}" placeholder="newyear2026" required>
        </div>

        @include('admin.partials.bilingual-field', [
          'name' => 'name', 'label' => __t('ชื่อโปรโมชั่น', 'Promotion name'), 'model' => $promotion, 'required' => true,
          'placeholder' => __t('เช่น ลด 20% ต้อนรับปีใหม่', 'e.g. 20% off New Year'),
        ])
      </div>

      <div class="card-panel mb-3" id="packageBox">
        <div class="ttl">{{ __t('ลดเฉพาะแพ็กเหล่านี้', 'Discount only these packages') }}</div>
        <div class="form-text small mb-2">{{ __t('ติ๊ก "ลดทุกแพ็ก" ด้านขวาถ้าต้องการลดทั้งหมด', 'Tick "All packages" on the right to discount everything') }}</div>
        @php $selected = old('package_ids', $promotion->exists ? $promotion->packages->pluck('id')->all() : []); @endphp
        @forelse($packages as $p)
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="pkg_{{ $p->id }}"
                   name="package_ids[]" value="{{ $p->id }}"
                   {{ in_array($p->id, $selected) ? 'checked' : '' }}>
            <label class="form-check-label" for="pkg_{{ $p->id }}">
              {{ $p->name }} <span class="text-secondary small">· {{ number_format($p->price) }} ฿</span>
            </label>
          </div>
        @empty
          <div class="text-secondary small">{{ __t('ยังไม่มีแพ็กเกจที่เปิดขาย', 'No active packages') }}</div>
        @endforelse
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ส่วนลด', 'Discount') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ชนิดส่วนลด', 'Discount type') }} <span class="text-danger">*</span></label>
          <select class="form-select" name="discount_type" id="discountType" required>
            <option value="percent" @selected(old('discount_type', $promotion->discount_type) === 'percent')>{{ __t('เปอร์เซ็นต์ (%)', 'Percentage (%)') }}</option>
            <option value="fixed" @selected(old('discount_type', $promotion->discount_type) === 'fixed')>{{ __t('จำนวนเงิน (บาท)', 'Fixed amount (THB)') }}</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ค่าส่วนลด', 'Discount value') }} <span class="text-danger">*</span></label>
          <div class="input-group">
            <input class="form-control" type="number" step="0.01" name="discount_value"
                   value="{{ old('discount_value', $promotion->discount_value) }}" min="0" required>
            <span class="input-group-text" id="discountUnit">%</span>
          </div>
          <div class="form-text small" id="percentHint">{{ __t('ลดเป็นเปอร์เซ็นต์ของราคาแพ็ก 0–100', 'Percentage off the package price, 0–100') }}</div>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ลำดับการแสดง', 'Display order') }}</label>
          <input class="form-control" type="number" name="sort_order"
                 value="{{ old('sort_order', $promotion->sort_order ?? 0) }}">
          <div class="form-text small">{{ __t('เมื่อแพ็กเข้าหลายโปร ระบบจะใช้โปรที่ลดได้มากที่สุด', 'When a package matches several promos, the biggest discount wins') }}</div>
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ช่วงเวลา', 'Period') }}</div>
        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('เริ่ม', 'Starts') }} <span class="text-danger">*</span></label>
            <input class="form-control" type="datetime-local" name="starts_at"
                   value="{{ old('starts_at', optional($promotion->starts_at)->format('Y-m-d\TH:i')) }}" required>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('สิ้นสุด', 'Ends') }} <span class="text-danger">*</span></label>
            <input class="form-control" type="datetime-local" name="ends_at"
                   value="{{ old('ends_at', optional($promotion->ends_at)->format('Y-m-d\TH:i')) }}" required>
          </div>
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ตัวเลือก', 'Options') }}</div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="applies_to_all" name="applies_to_all" value="1"
                 {{ old('applies_to_all', $promotion->applies_to_all ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="applies_to_all">{{ __t('ลดทุกแพ็ก', 'All packages') }}</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $promotion->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">{{ __t('เปิดใช้งาน', 'Active') }}</label>
        </div>
        <div class="form-text small">{{ __t('ปิดไว้เพื่อหยุดโปรชั่วคราวโดยไม่ต้องลบ', 'Turn off to pause the promo without deleting it') }}</div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
  </div>
</form>

@if($promotion->exists)
  <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" class="mt-2"
        data-confirm="{{ __t('ยืนยันลบโปรโมชั่นนี้?', 'Delete this promotion?') }}">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> {{ __t('ลบ', 'Delete') }}</button>
  </form>
@endif
@endsection

@push('scripts')
<script>
var discountType = document.getElementById('discountType');
var discountUnit = document.getElementById('discountUnit');
var percentHint = document.getElementById('percentHint');
var appliesAll = document.getElementById('applies_to_all');
var packageBox = document.getElementById('packageBox');

function syncDiscountType(){
  var percent = discountType.value === 'percent';
  discountUnit.textContent = percent ? '%' : '฿';
  percentHint.style.display = percent ? '' : 'none';
}
function syncPackages(){
  packageBox.style.display = appliesAll.checked ? 'none' : '';
}

discountType.addEventListener('change', syncDiscountType);
appliesAll.addEventListener('change', syncPackages);
syncDiscountType();
syncPackages();
</script>
@endpush

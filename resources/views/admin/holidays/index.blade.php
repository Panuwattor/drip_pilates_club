@extends('admin.layouts.app')
@section('title', __t('วันหยุด', 'Holidays'))

@section('content')
<div class="alert-soft mb-3">
  <i class="bi bi-info-circle"></i>
  {{ __t('ตอนสร้างรอบเรียน ระบบจะข้ามวันหยุดที่ระบุไว้ที่นี่ ถ้ามีรอบที่สร้างไปแล้วในวันนั้น ต้องเข้าไปยกเลิกรายรอบเอง', 'Session generation skips the holidays listed here. Sessions already created on those dates must be cancelled individually.') }}
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card-panel">
      <div class="ttl">{{ __t('เพิ่มวันหยุด', 'Add holiday') }}</div>
      <form method="POST" action="{{ route('admin.holidays.store') }}">
        @csrf
        <div class="mb-2">
          <label class="form-label">{{ __t('วันที่', 'Date') }} <span class="text-danger">*</span></label>
          <input class="form-control" type="date" name="date" required>
        </div>
        <div class="mb-2">
          <label class="form-label">{{ __t('สาขา', 'Branch') }}</label>
          <select class="form-select" name="branch_id">
            <option value="">{{ __t('ทุกสาขา', 'All branches') }}</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">{{ __t('เหตุผล (ไทย)', 'Reason (Thai)') }} <span class="text-danger">*</span></label>
          <input class="form-control" name="reason_th" placeholder="{{ __t('เช่น วันสงกรานต์', 'e.g. Songkran') }}" required>
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __t('เหตุผล (English)', 'Reason (English)') }} <span class="text-danger">*</span></label>
          <input class="form-control" name="reason_en" placeholder="e.g. Songkran" required>
        </div>
        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg"></i> {{ __t('เพิ่มวันหยุด', 'Add holiday') }}</button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card-panel">
      <div class="ttl">{{ __t('วันหยุดที่บันทึกไว้', 'Saved holidays') }}</div>

      @if($holidays->isEmpty())
        <div class="empty-note"><i class="bi bi-calendar-x"></i>{{ __t('ยังไม่มีวันหยุด', 'No holidays yet') }}</div>
      @else
        <div class="table-wrap">
          <table class="table align-middle">
            <thead><tr><th>{{ __t('วันที่', 'Date') }}</th><th>{{ __t('เหตุผล', 'Reason') }}</th><th>{{ __t('สาขา', 'Branch') }}</th><th></th></tr></thead>
            <tbody>
              @foreach($holidays as $h)
                <tr class="{{ $h->date->isPast() ? 'opacity-50' : '' }}">
                  <td>
                    <div class="fw-semibold">{{ $h->date->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}</div>
                    <div class="small text-secondary">{{ $h->date->locale(app()->getLocale())->isoFormat('dddd') }}</div>
                  </td>
                  <td>
                    {{ $h->reason }}
                    <div class="small text-secondary">{{ app()->getLocale() === 'en' ? $h->reason_th : $h->reason_en }}</div>
                  </td>
                  <td class="small text-secondary">{{ $h->branch?->name ?? __t('ทุกสาขา', 'All branches') }}</td>
                  <td class="text-end">
                    <form method="POST" action="{{ route('admin.holidays.destroy', $h) }}"
                          data-confirm="{{ __t('ลบวันหยุดนี้?', 'Delete this holiday?') }}">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

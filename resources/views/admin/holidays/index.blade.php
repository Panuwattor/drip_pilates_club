@extends('admin.layouts.app')
@section('title', 'วันหยุด')

@section('content')
<div class="alert-soft mb-3">
  <i class="bi bi-info-circle"></i>
  ตอนสร้างรอบเรียน ระบบจะข้ามวันหยุดที่ระบุไว้ที่นี่
  ถ้ามีรอบที่สร้างไปแล้วในวันนั้น ต้องเข้าไปยกเลิกรายรอบเอง
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card-panel">
      <div class="ttl">เพิ่มวันหยุด</div>
      <form method="POST" action="{{ route('admin.holidays.store') }}">
        @csrf
        <div class="mb-2">
          <label class="form-label">วันที่ <span class="text-danger">*</span></label>
          <input class="form-control" type="date" name="date" required>
        </div>
        <div class="mb-2">
          <label class="form-label">สาขา</label>
          <select class="form-select" name="branch_id">
            <option value="">ทุกสาขา</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}">{{ $b->name_th }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">เหตุผล (ไทย) <span class="text-danger">*</span></label>
          <input class="form-control" name="reason_th" placeholder="เช่น วันสงกรานต์" required>
        </div>
        <div class="mb-3">
          <label class="form-label">เหตุผล (English) <span class="text-danger">*</span></label>
          <input class="form-control" name="reason_en" placeholder="e.g. Songkran" required>
        </div>
        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg"></i> เพิ่มวันหยุด</button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card-panel">
      <div class="ttl">วันหยุดที่บันทึกไว้</div>

      @if($holidays->isEmpty())
        <div class="empty-note"><i class="bi bi-calendar-x"></i>ยังไม่มีวันหยุด</div>
      @else
        <div class="table-wrap">
          <table class="table align-middle">
            <thead><tr><th>วันที่</th><th>เหตุผล</th><th>สาขา</th><th></th></tr></thead>
            <tbody>
              @foreach($holidays as $h)
                <tr class="{{ $h->date->isPast() ? 'opacity-50' : '' }}">
                  <td>
                    <div class="fw-semibold">{{ $h->date->locale('th')->isoFormat('D MMM YYYY') }}</div>
                    <div class="small text-secondary">{{ $h->date->locale('th')->isoFormat('dddd') }}</div>
                  </td>
                  <td>
                    {{ $h->reason_th }}
                    <div class="small text-secondary">{{ $h->reason_en }}</div>
                  </td>
                  <td class="small text-secondary">{{ $h->branch?->name_th ?? 'ทุกสาขา' }}</td>
                  <td class="text-end">
                    <form method="POST" action="{{ route('admin.holidays.destroy', $h) }}"
                          data-confirm="ลบวันหยุดนี้?">
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

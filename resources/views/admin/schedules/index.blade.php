@extends('admin.layouts.app')
@section('title', __t('ตารางประจำสัปดาห์', 'Weekly Schedule'))

@section('topbar-actions')
  <a href="{{ route('admin.schedules.create', ['branch' => $branchId]) }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> {{ __t('เพิ่มคลาสประจำ', 'Add recurring class') }}
  </a>
@endsection

@section('content')
<div class="alert-soft mb-3">
  <i class="bi bi-info-circle"></i>
  {{ __t('ตั้งตารางประจำสัปดาห์ไว้ครั้งเดียว แล้วกด "สร้างรอบเรียน" ระบบจะสร้างรอบจริงล่วงหน้าให้อัตโนมัติ ถ้าแก้ตารางนี้ รอบที่สร้างไปแล้วจะไม่เปลี่ยนตาม ต้องไปแก้รายรอบที่หน้ารอบเรียน', 'Set the weekly schedule once, then press "Generate sessions" to create real sessions in advance. Editing this schedule does not change sessions already generated — adjust those individually on the Class Sessions page.') }}
</div>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <form method="GET" class="d-flex gap-2">
    <select class="form-select form-select-sm" name="branch" style="width:auto;" onchange="this.form.submit()">
      @foreach($branches as $branch)
        <option value="{{ $branch->id }}" @selected($branch->id == $branchId)>{{ $branch->name }}</option>
      @endforeach
    </select>
  </form>

  <form method="POST" action="{{ route('admin.schedules.generate') }}" class="ms-auto d-flex gap-2">
    @csrf
    <div class="input-group input-group-sm" style="width:auto;">
      <span class="input-group-text">{{ __t('สร้างล่วงหน้า', 'Generate ahead') }}</span>
      <input class="form-control" type="number" name="days" value="90" min="1" max="365" style="width:80px;">
      <span class="input-group-text">{{ __t('วัน', 'days') }}</span>
    </div>
    <button class="btn btn-sm btn-primary" type="submit">
      <i class="bi bi-magic"></i> {{ __t('สร้างรอบเรียน', 'Generate sessions') }}
    </button>
  </form>
</div>

<div class="row g-3">
  @foreach($days as $dow => $dayName)
    <div class="col-lg-4 col-md-6">
      <div class="card-panel h-100">
        <div class="d-flex align-items-center mb-2">
          <div class="ttl mb-0">{{ $dayName }}</div>
          <a href="{{ route('admin.schedules.create', ['branch' => $branchId, 'day' => $dow]) }}"
             class="btn btn-sm btn-outline-secondary ms-auto py-0 px-2" title="{{ __t('เพิ่มคลาสวันนี้', 'Add a class on this day') }}">
            <i class="bi bi-plus-lg"></i>
          </a>
        </div>

        @php $daySchedules = $schedules[$dow] ?? collect(); @endphp

        @forelse($daySchedules as $s)
          <a href="{{ route('admin.schedules.edit', $s) }}"
             class="d-flex align-items-center gap-2 p-2 mb-1 text-decoration-none rounded-3"
             style="border:1px solid var(--line); color:var(--ink); {{ $s->is_active ? '' : 'opacity:.5;' }}">
            <span style="width:4px;align-self:stretch;border-radius:2px;background:{{ $s->classType->color ?: 'var(--accent)' }};"></span>
            <div class="flex-grow-1 min-width-0">
              <div class="d-flex align-items-center gap-2">
                <span class="fw-bold" style="font-variant-numeric:tabular-nums;">{{ substr($s->start_time, 0, 5) }}</span>
                <span class="small">{{ $s->classType->name }}</span>
              </div>
              <div class="small text-secondary text-truncate">
                {{ $s->trainer?->nickname ?: $s->trainer?->name ?: __t('ยังไม่กำหนดครู', 'No trainer assigned') }}
                @if($s->room) · {{ $s->room->name }} @endif
                · {{ $s->capacity }} {{ __t('ที่', 'seats') }}
              </div>
            </div>
            @unless($s->is_active)
              <span class="badge-soft badge-danger">{{ __t('ปิด', 'Off') }}</span>
            @endunless
          </a>
        @empty
          <div class="text-center small text-secondary py-3">{{ __t('ไม่มีคลาส', 'No classes') }}</div>
        @endforelse
      </div>
    </div>
  @endforeach
</div>
@endsection

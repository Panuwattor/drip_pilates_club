@extends('admin.layouts.app')
@section('title', 'รอบเรียน')

@section('content')
<form method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <select class="form-select form-select-sm" name="branch" style="width:auto;" onchange="this.form.submit()">
    @foreach($branches as $branch)
      <option value="{{ $branch->id }}" @selected($branch->id == $branchId)>{{ $branch->name_th }}</option>
    @endforeach
  </select>

  <input class="form-control form-control-sm" type="date" name="date" value="{{ $date }}"
         style="width:auto;" onchange="this.form.submit()">

  <div class="btn-group btn-group-sm">
    <button class="btn {{ $view === 'day' ? 'btn-primary' : 'btn-outline-secondary' }}"
            type="submit" name="view" value="day">รายวัน</button>
    <button class="btn {{ $view === 'week' ? 'btn-primary' : 'btn-outline-secondary' }}"
            type="submit" name="view" value="week">รายสัปดาห์</button>
  </div>

  <a href="{{ route('admin.sessions.index', ['branch' => $branchId, 'date' => now()->toDateString(), 'view' => $view]) }}"
     class="btn btn-sm btn-outline-secondary">วันนี้</a>

  <div class="ms-auto small text-secondary">
    ทั้งหมด {{ $flatSessions->count() }} รอบ ·
    จอง {{ $flatSessions->sum('booked_count') }}/{{ $flatSessions->sum('capacity') }} ที่
  </div>
</form>

@if($flatSessions->isEmpty())
  <div class="empty-note"><i class="bi bi-calendar3"></i>ไม่มีรอบเรียนในช่วงที่เลือก</div>
@else
  @foreach($sessions as $groupKey => $groupSessions)
    @if($view === 'week')
      <div class="ttl mt-3 mb-2" style="font-size:.72rem;">
        {{ \Carbon\Carbon::parse($groupKey)->locale('th')->isoFormat('dddd D MMMM') }}
      </div>
    @endif

    <div class="card-panel mb-2">
      <div class="table-wrap">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>เวลา</th><th>คลาส</th><th>ครู</th><th>ห้อง</th>
              <th class="text-center">จอง</th><th class="text-center">คิว</th>
              <th>สถานะ</th><th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($groupSessions as $s)
              @php $t = $s->actualTrainer(); @endphp
              <tr>
                <td class="fw-bold" style="font-variant-numeric:tabular-nums;white-space:nowrap;">
                  {{ $s->start_at->format('H:i') }}
                  <div class="small text-secondary fw-normal">{{ $s->end_at->format('H:i') }}</div>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span style="width:4px;height:28px;border-radius:2px;background:{{ $s->classType->color ?: 'var(--accent)' }};"></span>
                    <div>
                      <div class="fw-semibold">{{ $s->classType->name_th }}</div>
                      <div class="small text-secondary">{{ $s->classType->name_en }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  {{ $t?->nickname_th ?: $t?->name_th ?: '—' }}
                  @if($s->substitute_trainer_id)
                    <span class="badge-soft badge-warn">สอนแทน</span>
                  @endif
                </td>
                <td class="small text-secondary">{{ $s->room?->name_th ?? '—' }}</td>
                <td class="text-center" style="font-variant-numeric:tabular-nums;">
                  <span class="{{ $s->isFull() ? 'text-danger fw-bold' : '' }}">
                    {{ $s->booked_count }}/{{ $s->capacity }}
                  </span>
                </td>
                <td class="text-center">
                  @if($s->waitlist_count > 0)
                    <span class="badge-soft badge-warn">{{ $s->waitlist_count }}</span>
                  @else
                    <span class="text-secondary">—</span>
                  @endif
                </td>
                <td>
                  @if($s->status === 'cancelled')
                    <span class="badge-soft badge-danger">ยกเลิก</span>
                  @elseif($s->start_at->isPast())
                    <span class="badge-soft">จบแล้ว</span>
                  @else
                    <span class="badge-soft badge-ok">ปกติ</span>
                  @endif
                </td>
                <td class="text-end">
                  <a href="{{ route('admin.sessions.show', $s) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-list-check"></i> รายชื่อ
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endforeach
@endif
@endsection

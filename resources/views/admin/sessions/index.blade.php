@extends('admin.layouts.app')
@section('title', __t('รอบเรียน', 'Class Sessions'))

@section('content')
<form method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <select class="form-select form-select-sm" name="branch" style="width:auto;" onchange="this.form.submit()">
    @foreach($branches as $branch)
      <option value="{{ $branch->id }}" @selected($branch->id == $branchId)>{{ $branch->name }}</option>
    @endforeach
  </select>

  <input class="form-control form-control-sm" type="date" name="date" value="{{ $date }}"
         style="width:auto;" onchange="this.form.submit()">

  <div class="btn-group btn-group-sm">
    <button class="btn {{ $view === 'day' ? 'btn-primary' : 'btn-outline-secondary' }}"
            type="submit" name="view" value="day">{{ __t('รายวัน', 'Day') }}</button>
    <button class="btn {{ $view === 'week' ? 'btn-primary' : 'btn-outline-secondary' }}"
            type="submit" name="view" value="week">{{ __t('รายสัปดาห์', 'Week') }}</button>
  </div>

  <a href="{{ route('admin.sessions.index', ['branch' => $branchId, 'date' => now()->toDateString(), 'view' => $view]) }}"
     class="btn btn-sm btn-outline-secondary">{{ __t('วันนี้', 'Today') }}</a>

  <div class="ms-auto small text-secondary">
    {{ __t('ทั้งหมด', 'Total') }} {{ $flatSessions->count() }} {{ __t('รอบ', 'sessions') }} ·
    {{ __t('จอง', 'Booked') }} {{ $flatSessions->sum('booked_count') }}/{{ $flatSessions->sum('capacity') }} {{ __t('ที่', 'seats') }}
  </div>
</form>

@if($flatSessions->isEmpty())
  <div class="empty-note"><i class="bi bi-calendar3"></i>{{ __t('ไม่มีรอบเรียนในช่วงที่เลือก', 'No sessions in the selected range') }}</div>
@else
  @foreach($sessions as $groupKey => $groupSessions)
    @if($view === 'week')
      <div class="ttl mt-3 mb-2" style="font-size:.72rem;">
        {{ \Carbon\Carbon::parse($groupKey)->locale(app()->getLocale())->isoFormat('dddd D MMMM') }}
      </div>
    @endif

    <div class="card-panel mb-2">
      <div class="table-wrap">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>{{ __t('เวลา', 'Time') }}</th><th>{{ __t('คลาส', 'Class') }}</th><th>{{ __t('ครู', 'Trainer') }}</th><th>{{ __t('ห้อง', 'Room') }}</th>
              <th class="text-center">{{ __t('จอง', 'Booked') }}</th><th class="text-center">{{ __t('คิว', 'Waitlist') }}</th>
              <th>{{ __t('สถานะ', 'Status') }}</th><th></th>
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
                      <div class="fw-semibold">{{ $s->classType->name }}</div>
                      <div class="small text-secondary">{{ app()->getLocale() === 'en' ? $s->classType->name_th : $s->classType->name_en }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  {{ $t?->nickname ?: $t?->name ?: '—' }}
                  @if($s->substitute_trainer_id)
                    <span class="badge-soft badge-warn">{{ __t('สอนแทน', 'Substitute') }}</span>
                  @endif
                </td>
                <td class="small text-secondary">{{ $s->room?->name ?? '—' }}</td>
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
                    <span class="badge-soft badge-danger">{{ __t('ยกเลิก', 'Cancelled') }}</span>
                  @elseif($s->start_at->isPast())
                    <span class="badge-soft">{{ __t('จบแล้ว', 'Finished') }}</span>
                  @else
                    <span class="badge-soft badge-ok">{{ __t('ปกติ', 'Scheduled') }}</span>
                  @endif
                </td>
                <td class="text-end">
                  <a href="{{ route('admin.sessions.show', $s) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-list-check"></i> {{ __t('รายชื่อ', 'Roster') }}
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

@extends('customer.layout')
@section('title', __t('การจองของฉัน', 'My Bookings'))

@section('content')
<div class="page-header">
  <h1>{{ __t('การจองของฉัน', 'My Bookings') }}</h1>
  <p>{{ __t('คลาสที่กำลังจะถึงและประวัติ', 'Upcoming classes and history') }}</p>
</div>

<div class="row g-4">
  <div class="col-md-8">
    <div class="section-title">{{ __t('กำลังจะถึง', 'Upcoming') }}</div>

    @forelse($upcoming as $b)
      @php $bs = $b->classSession; $bt = $bs->actualTrainer(); @endphp
      <div class="class-card mb-3">
        <div class="class-time-rail">
          {{ $bs->start_at->format('H:i') }}
          <small>
            @if($bs->start_at->isToday()) {{ __t('วันนี้', 'Today') }}
            @elseif($bs->start_at->isTomorrow()) {{ __t('พรุ่งนี้', 'Tomorrow') }}
            @else {{ $bs->start_at->locale(app()->getLocale())->isoFormat('ddd D MMM') }}
            @endif
          </small>
        </div>
        <div class="class-info">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="min-width-0">
              <h4>{{ $bs->classType->name }}</h4>
              <p class="meta">
                @if($bt?->avatar)<img src="{{ asset($bt->avatar) }}" alt="" class="coach-avatar">@endif
                <span>{{ $bt?->nickname ?: $bt?->name }}</span> ·
                <span>{{ $bs->branch->name }}</span>
                @if($bs->room) · <span>{{ $bs->room->name }}</span>@endif
              </p>
            </div>
            @if($b->status === 'waitlisted')
              <span class="badge rounded-pill" style="background:#F4E3C7;color:#8A6112;">
                {{ __t('คิวที่ ' . $b->waitlist_position, 'Queue #' . $b->waitlist_position) }}
              </span>
            @else
              <span class="badge rounded-pill" style="background:#D8ECD9;color:#2F6B33;">
                {{ __t('ยืนยันแล้ว', 'Confirmed') }}
              </span>
            @endif
          </div>
          <div class="booking-actions mt-2">
            <a href="#" class="muted js-cancel-booking" data-booking="{{ $b->id }}">{{ __t('ยกเลิก', 'Cancel') }}</a>
          </div>
        </div>
      </div>
    @empty
      <div class="no-class-note">
        {{ __t('ยังไม่มีคลาสที่จองไว้', 'No upcoming bookings') }}
        <div class="mt-3">
          <a href="{{ route('customer.schedule') }}" class="btn btn-book btn-sm">{{ __t('ดูตารางคลาส', 'View schedule') }}</a>
        </div>
      </div>
    @endforelse
  </div>

  <div class="col-md-4">
    <div class="section-title">{{ __t('ประวัติที่ผ่านมา', 'Past History') }}</div>

    @forelse($pastBookings as $b)
      @php
        $st = [
          'attended'       => [__t('เข้าเรียนแล้ว', 'Attended'), 'background:var(--accent-soft);color:var(--accent-deep);'],
          'no_show'        => [__t('ไม่มาเรียน', 'No-show'), 'background:#F6DADA;color:#9B3232;'],
          'cancelled'      => [__t('ยกเลิกแล้ว', 'Cancelled'), 'background:var(--sage-soft);color:var(--sage);'],
          'late_cancelled' => [__t('ยกเลิกช้า', 'Late cancel'), 'background:#F4E3C7;color:#8A6112;'],
        ][$b->status] ?? [$b->status, ''];
      @endphp
      <div class="class-card mb-2">
        <div class="class-time-rail">
          {{ $b->classSession->start_at->format('H:i') }}
          <small>{{ $b->classSession->start_at->locale(app()->getLocale())->isoFormat('D MMM') }}</small>
        </div>
        <div class="class-info">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="min-width-0"><h4>{{ $b->classSession->classType->name }}</h4></div>
            <span class="badge rounded-pill" style="{{ $st[1] }}">{{ $st[0] }}</span>
          </div>
        </div>
      </div>
    @empty
      <div class="no-class-note">{{ __t('ยังไม่มีประวัติ', 'No history yet') }}</div>
    @endforelse
  </div>
</div>
@endsection

@section('extra-script')
document.addEventListener('click', function(e){
  var link = e.target.closest('.js-cancel-booking');
  if (!link) return;
  e.preventDefault();
  var id = link.dataset.booking;

  cancelBooking(id, {
    onStart: function(){ link.style.pointerEvents = 'none'; },
    onDone: function(){ setTimeout(function(){ window.location.reload(); }, 900); },
    onFail: function(){ link.style.pointerEvents = ''; }
  });
});
@endsection

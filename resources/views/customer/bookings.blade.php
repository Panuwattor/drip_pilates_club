@extends('customer.layout')
@section('title', __t('การจองของฉัน', 'My Bookings'))

@section('content')
<div class="page-header">
  <h1>{{ __t('การจองของฉัน', 'My Bookings') }}</h1>
  <p>{{ __t('คลาสที่กำลังจะถึงและประวัติ', 'Upcoming classes and history') }}</p>
</div>

@guest('customer')
  <div class="no-class-note">
    {{ __t('เข้าสู่ระบบเพื่อดูการจองของคุณ', 'Log in to see your bookings') }}
    <div class="mt-3">
      <a href="{{ route('customer.login') }}" class="btn btn-book btn-sm">{{ __t('เข้าสู่ระบบ', 'Log in') }}</a>
    </div>
  </div>
@else
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
@endguest
@endsection

@section('extra-script')
document.addEventListener('click', function(e){
  var link = e.target.closest('.js-cancel-booking');
  if (!link) return;
  e.preventDefault();
  var id = link.dataset.booking;

  fetch('/bookings/' + id + '/cancel-preview', {
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(function(r){ return r.json(); })
  .then(function(p){
    var msg;
    if (p.will_lose_credit) {
      msg = currentLang === 'th'
        ? 'เลยกำหนดยกเลิกฟรีแล้ว (ต้องยกเลิกก่อนคลาสเริ่ม ' + p.deadline_hours + ' ชั่วโมง)\n\nถ้ายกเลิกตอนนี้จะเสียเครดิต ' + p.credit_at_stake + ' เครดิต ยืนยันหรือไม่?'
        : 'The free-cancellation window has passed (' + p.deadline_hours + ' hours before class).\n\nCancelling now will forfeit ' + p.credit_at_stake + ' credit(s). Continue?';
    } else {
      msg = currentLang === 'th'
        ? 'ยืนยันยกเลิกการจอง? เครดิตจะคืนเข้าบัญชีของคุณ'
        : 'Cancel this booking? Your credit will be refunded.';
    }

    if (!window.confirm(msg)) return;

    link.style.pointerEvents = 'none';

    fetch('/bookings/' + id + '/cancel', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(function(r){ return r.json().then(function(d){ return { ok: r.ok, data: d }; }); })
    .then(function(res){
      showToast(res.data.message, !res.ok);
      if (res.ok) { setTimeout(function(){ window.location.reload(); }, 900); }
      else { link.style.pointerEvents = ''; }
    })
    .catch(function(){
      link.style.pointerEvents = '';
      showToast(currentLang === 'th' ? 'เกิดข้อผิดพลาด' : 'Something went wrong', true);
    });
  });
});
@endsection

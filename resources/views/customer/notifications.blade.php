@extends('customer.layout')
@section('title', __t('การแจ้งเตือน', 'Notifications'))

@section('extra-style')
  .notif-item{
    display:flex; gap:.85rem; align-items:flex-start;
    background:var(--panel); border:1px solid var(--line); border-radius:14px;
    padding:.9rem 1rem; margin-bottom:.6rem; text-decoration:none; color:var(--ink);
    transition:border-color .15s;
  }
  .notif-item:hover{ border-color:var(--accent); color:var(--ink); }
  .notif-item.unread{ border-left:3px solid var(--accent); background:var(--accent-soft); }
  .notif-ic{
    width:40px; height:40px; border-radius:12px; flex:0 0 auto;
    display:flex; align-items:center; justify-content:center; font-size:1.05rem;
    background:var(--sage-soft); color:var(--sage);
  }
  .notif-ic.ok{ background:#D8ECD9; color:#2F6B33; }
  .notif-ic.warn{ background:#F6D9D9; color:#9B3232; }
  .notif-ic.promo{ background:#F4E3C7; color:#8A6112; }
  .notif-body{ flex:1; min-width:0; }
  .notif-body h4{ font-size:.9rem; font-weight:700; margin:0; }
  .notif-body p{ font-size:.82rem; color:var(--ink-soft); margin:.15rem 0 0; }
  .notif-body time{ font-size:.72rem; color:var(--ink-soft); }
  .notif-dot{ width:8px; height:8px; border-radius:50%; background:var(--accent); flex:0 0 auto; margin-top:.5rem; }
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-end">
  <div>
    <h1>{{ __t('การแจ้งเตือน', 'Notifications') }}</h1>
    <p>{{ __t('อัปเดตการจอง คิวสำรอง และการชำระเงิน', 'Updates on bookings, waitlist and payments') }}</p>
  </div>
  @if($notifications->getCollection()->contains(fn ($n) => is_null($n->read_at)))
    <form method="POST" action="{{ route('customer.notifications.readAll') }}">
      @csrf
      <button class="btn btn-sm" type="submit"
              style="background:var(--ground);border:1px solid var(--line);color:var(--accent-deep);border-radius:999px;font-size:.76rem;font-weight:700;white-space:nowrap;">
        <i class="bi bi-check2-all"></i> {{ __t('อ่านทั้งหมด', 'Mark all read') }}
      </button>
    </form>
  @endif
</div>

@if(session('status'))
  <div class="alert alert-success py-2 small">{{ session('status') }}</div>
@endif

@forelse($notifications as $n)
  @php
    $icon = match($n->type) {
      'booking_confirmed' => ['bi-check-circle-fill', 'ok'],
      'waitlist_promoted' => ['bi-arrow-up-circle-fill', 'ok'],
      'order_paid'        => ['bi-bag-check-fill', 'ok'],
      'class_cancelled'   => ['bi-x-octagon-fill', 'warn'],
      'class_reminder'    => ['bi-alarm-fill', 'promo'],
      'credit_expiring'   => ['bi-hourglass-split', 'promo'],
      default             => ['bi-bell-fill', ''],
    };
  @endphp
  <a href="{{ route('customer.notifications.read', $n) }}" class="notif-item {{ $n->read_at ? '' : 'unread' }}">
    <span class="notif-ic {{ $icon[1] }}"><i class="bi {{ $icon[0] }}"></i></span>
    <div class="notif-body">
      <h4>{{ $n->title }}</h4>
      @if($n->body)<p>{{ $n->body }}</p>@endif
      <time>{{ $n->created_at->locale(app()->getLocale())->diffForHumans() }}</time>
    </div>
    @unless($n->read_at)<span class="notif-dot"></span>@endunless
  </a>
@empty
  <div class="no-class-note">
    <i class="bi bi-bell-slash" style="font-size:1.8rem;opacity:.5;"></i>
    <div class="mt-2">{{ __t('ยังไม่มีการแจ้งเตือน', 'No notifications yet') }}</div>
  </div>
@endforelse

@if($notifications->hasPages())
  <div class="mt-3">{{ $notifications->links() }}</div>
@endif
@endsection

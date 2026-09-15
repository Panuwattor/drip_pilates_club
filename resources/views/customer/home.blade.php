@extends('customer.layout')
@section('title', __t('หน้าแรก', 'Home'))

@section('extra-style')
  .announce-card{
    display:flex; align-items:flex-start; gap:.85rem;
    background:linear-gradient(135deg,var(--c-purple),#B58BF2);
    color:#fff; border-radius:22px; padding:1.1rem 1.25rem;
    box-shadow:0 10px 24px rgba(124,107,240,.24);
  }
  .announce-card .announce-icon{
    width:40px; height:40px; border-radius:14px; flex:0 0 auto;
    background:rgba(255,255,255,.22); display:flex; align-items:center; justify-content:center; font-size:1.1rem;
  }
  .announce-card strong{ font-size:.98rem; }

  /* อวาตาร์ครูผู้สอน ไล่สีวนไปให้ดูสนุก */
  .instructors-grid > *:nth-child(6n+1) .avatar-round{ background:var(--c-purple-soft); color:var(--c-purple-ink); }
  .instructors-grid > *:nth-child(6n+2) .avatar-round{ background:var(--c-orange-soft); color:var(--c-orange-ink); }
  .instructors-grid > *:nth-child(6n+3) .avatar-round{ background:var(--c-green-soft);  color:var(--c-green-ink); }
  .instructors-grid > *:nth-child(6n+4) .avatar-round{ background:var(--c-blue-soft);   color:var(--c-blue-ink); }
  .instructors-grid > *:nth-child(6n+5) .avatar-round{ background:var(--c-pink-soft);   color:var(--c-pink-ink); }
  .instructors-grid > *:nth-child(6n+6) .avatar-round{ background:var(--c-amber-soft);  color:var(--c-amber-ink); }
@endsection

@section('content')
<div class="page-header">
  <div class="brandmark">{{ __t('ภาพรวม', 'Overview') }}</div>
  <h1>
    @if($customer)
      {{ __t('สวัสดี คุณ' . ($customer->nickname ?: $customer->first_name), 'Hello, ' . ($customer->nickname ?: $customer->first_name)) }} 👋
    @else
      {{ __t('ยินดีต้อนรับ', 'Welcome') }} 👋
    @endif
  </h1>
  <p>{{ now()->locale(app()->getLocale())->isoFormat(app()->getLocale() === 'th' ? 'dddd D MMMM' : 'dddd, D MMMM') }}</p>
</div>

@foreach($announcements as $ann)
  <div class="announce-card mb-3">
    <div class="announce-icon"><i class="bi bi-megaphone-fill"></i></div>
    <div class="min-width-0">
      <strong>{{ $ann->title }}</strong>
      @if($ann->body)<div class="small mt-1 opacity-90">{{ \Illuminate\Support\Str::limit(strip_tags($ann->body), 140) }}</div>@endif
    </div>
  </div>
@endforeach

<div class="row g-3 mb-2">
  <div class="col-md-6">
    <div class="credit-card">
      <div>
        <div class="text-uppercase small opacity-75" style="font-size:.68rem;letter-spacing:.08em;">Class Credits</div>
        <div class="cc-count">{{ $hasUnlimited ? '∞' : $totalCredits }}</div>
        @php $nearest = $packages->where('status', 'active')->sortBy('expires_at')->first(); @endphp
        <div class="small opacity-75">
          @if($hasUnlimited)
            {{ __t('เหมาจ่าย', 'Unlimited') }}@if($nearest) · {{ __t('ถึง', 'until') }} {{ $nearest->expires_at->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}@endif
          @elseif($nearest)
            {{ __t('หมดอายุ', 'Expires') }} {{ $nearest->expires_at->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}
          @else
            {{ __t('ยังไม่มีแพ็กเกจ', 'No active package') }}
          @endif
        </div>
      </div>
      <a href="{{ route('customer.purchase.index') }}" class="cc-btn text-decoration-none">{{ __t('เติมแพ็กเกจ', 'Top Up') }}</a>
    </div>
  </div>

  <div class="col-md-6">
    @php $next = $upcoming->first(); @endphp
    @if($next)
      <a href="{{ route('customer.bookings') }}" class="class-card mb-0 h-100">
        <div class="class-time-rail">
          {{ $next->classSession->start_at->format('H:i') }}
          <small>
            @if($next->classSession->start_at->isToday()) {{ __t('วันนี้', 'Today') }}
            @elseif($next->classSession->start_at->isTomorrow()) {{ __t('พรุ่งนี้', 'Tomorrow') }}
            @else {{ $next->classSession->start_at->locale(app()->getLocale())->isoFormat('D MMM') }}
            @endif
          </small>
        </div>
        <div class="class-info d-flex align-items-center justify-content-between gap-2">
          <div>
            <h4>{{ $next->classSession->classType->name }}</h4>
            <p class="meta">
              @php $nt = $next->classSession->actualTrainer(); @endphp
              @if($nt?->avatar)<img src="{{ asset($nt->avatar) }}" alt="" class="coach-avatar">@endif
              <span>{{ $nt?->nickname ?: $nt?->name }}</span> ·
              <span>{{ $next->classSession->branch->short_name_th ? $next->classSession->branch->trans('short_name') : $next->classSession->branch->name }}</span>
            </p>
          </div>
          <span class="text-secondary"><i class="bi bi-chevron-right"></i></span>
        </div>
      </a>
    @else
      <div class="class-card mb-0 h-100 align-items-center justify-content-center" style="padding:1.5rem;">
        <div class="text-center w-100">
          <div class="text-secondary small mb-2">{{ __t('ยังไม่มีคลาสที่จองไว้', 'No upcoming classes') }}</div>
          <a href="{{ route('customer.schedule') }}" class="btn btn-book btn-sm">{{ __t('จองคลาสเลย', 'Book a class') }}</a>
        </div>
      </div>
    @endif
  </div>
</div>

<div class="section-title mt-4">{{ __t('บริการด่วน', 'Quick Actions') }}</div>
<div class="row row-cols-2 row-cols-md-4 g-3 mb-2 quick-grid">
  <div class="col"><a href="{{ route('customer.schedule') }}" class="quick-item"><div class="qi-icon"><i class="bi bi-plus-lg"></i></div><span>{{ __t('จองคลาส', 'Book Class') }}</span></a></div>
  <div class="col"><a href="{{ route('customer.bookings') }}" class="quick-item"><div class="qi-icon"><i class="bi bi-arrow-repeat"></i></div><span>{{ __t('การจองของฉัน', 'My Bookings') }}</span></a></div>
  <div class="col"><a href="{{ route('customer.purchase.index') }}" class="quick-item"><div class="qi-icon"><i class="bi bi-ticket-perforated"></i></div><span>{{ __t('ซื้อแพ็กเกจ', 'Buy Package') }}</span></a></div>
  <div class="col">
    @php $branch = $branches->firstWhere('id', $currentBranchId); @endphp
    <a href="{{ $branch?->google_map_url ?: '#' }}" @if($branch?->google_map_url) target="_blank" @endif class="quick-item"><div class="qi-icon"><i class="bi bi-geo-alt"></i></div><span>{{ __t('แผนที่สาขา', 'Find Us') }}</span></a>
  </div>
</div>

<div class="section-title mt-4">{{ __t('ครูผู้สอน', 'Instructors') }}</div>
<div class="row row-cols-4 row-cols-md-6 g-3 instructors-grid">
  @foreach($trainers as $t)
    <div class="col text-center">
      <div class="avatar-round mx-auto mb-2">
        @if($t->avatar)<img src="{{ asset($t->avatar) }}" alt="{{ $t->name }}">@else<i class="bi bi-person"></i>@endif
      </div>
      <div class="instructor-name">{{ $t->nickname ?: $t->name }}</div>
    </div>
  @endforeach
</div>
@endsection

@extends('customer.layout')
@section('title', __t('โปรไฟล์', 'Profile'))

@section('extra-style')
  .stat-box.tint-green{ background:var(--c-green-soft); border-color:transparent; }
  .stat-box.tint-blue{ background:var(--c-blue-soft); border-color:transparent; }
  .stat-box.tint-orange{ background:var(--c-orange-soft); border-color:transparent; }

  /* ไอคอนในเมนู ไล่สีเป็นชุดให้ดูมีมิติ */
  .menu-list .menu-row:nth-child(5n+1) .mi{ background:var(--c-purple-soft); color:var(--c-purple-ink); }
  .menu-list .menu-row:nth-child(5n+2) .mi{ background:var(--c-blue-soft);   color:var(--c-blue-ink); }
  .menu-list .menu-row:nth-child(5n+3) .mi{ background:var(--c-green-soft);  color:var(--c-green-ink); }
  .menu-list .menu-row:nth-child(5n+4) .mi{ background:var(--c-orange-soft); color:var(--c-orange-ink); }
  .menu-list .menu-row:nth-child(5n+5) .mi{ background:var(--c-pink-soft);   color:var(--c-pink-ink); }
@endsection

@section('content')
<div class="page-header">
  <h1>{{ __t('โปรไฟล์', 'Profile') }}</h1>
  <p>{{ __t('บัญชีและแพ็กเกจของคุณ', 'Your account and packages') }}</p>
</div>

<div class="profile-hero d-flex align-items-center gap-3 mb-4">
  <div class="avatar-lg">
    @if($customer->avatar)<img src="{{ asset($customer->avatar) }}" alt="">@else<i class="bi bi-person"></i>@endif
  </div>
  <div class="flex-grow-1 min-width-0">
    <h3 class="mb-1" style="font-size:1.25rem;font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;">{{ $customer->full_name }}</h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="member-badge"><i class="bi bi-gem"></i> {{ $customer->code }}</span>
      <span class="small opacity-75">
        {{ __t('สมาชิกตั้งแต่', 'Member since') }}
        {{ $customer->created_at->locale(app()->getLocale())->isoFormat('MMM YYYY') }}
      </span>
    </div>
  </div>
</div>

<div class="row row-cols-3 g-2 mb-4">
  <div class="col">
    <div class="stat-box tint-green">
      <div class="stat-icon" style="background:var(--c-green);color:#fff;"><i class="bi bi-activity"></i></div>
      <div class="num">{{ $customer->bookings()->where('status', 'attended')->count() }}</div>
      <div class="lbl">{{ __t('คลาสทั้งหมด', 'Classes') }}</div>
    </div>
  </div>
  <div class="col">
    <div class="stat-box tint-blue">
      <div class="stat-icon" style="background:var(--c-blue);color:#fff;"><i class="bi bi-ticket-perforated"></i></div>
      <div class="num">{{ $hasUnlimited ? '∞' : $totalCredits }}</div>
      <div class="lbl">{{ __t('เครดิตคงเหลือ', 'Credits') }}</div>
    </div>
  </div>
  <div class="col">
    <div class="stat-box tint-orange">
      <div class="stat-icon" style="background:var(--c-orange);color:#fff;"><i class="bi bi-calendar-check"></i></div>
      <div class="num">{{ $upcoming->count() }}</div>
      <div class="lbl">{{ __t('จองไว้', 'Upcoming') }}</div>
    </div>
  </div>
</div>

<div class="section-title">{{ __t('แพ็กเกจของฉัน', 'My Packages') }}</div>
<div class="row g-3 mb-4">
  @forelse($packages->whereIn('status', ['active', 'frozen']) as $cp)
    <div class="col-md-6">
      <div class="class-card mb-0 h-100">
        <div class="class-time-rail">
          {{ $cp->isUnlimited() ? '∞' : $cp->credit_remaining }}
          <small>{{ __t('เหลือ', 'left') }}</small>
        </div>
        <div class="class-info">
          <h4>{{ $cp->package->name }}</h4>
          <p class="meta">
            {{ __t('หมดอายุ', 'Expires') }}
            {{ $cp->expires_at->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}
            @php $days = $cp->daysUntilExpiry(); @endphp
            @if($days >= 0 && $days <= 14)
              <span class="badge rounded-pill" style="background:#F4E3C7;color:#8A6112;">
                {{ __t("เหลือ {$days} วัน", "{$days} days") }}
              </span>
            @endif
            @if($cp->status === 'frozen')
              <span class="badge rounded-pill" style="background:var(--sage-soft);color:var(--sage);">
                {{ __t('ฟรีซอยู่', 'Frozen') }}
              </span>
            @endif
          </p>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12">
      <div class="no-class-note text-center">
        <div class="mb-2">{{ __t('ยังไม่มีแพ็กเกจที่ใช้งานได้', 'No active packages') }}</div>
        <a href="{{ route('customer.purchase.index') }}" class="btn btn-accent btn-sm">
          <i class="bi bi-ticket-perforated"></i> {{ __t('เลือกแพ็กเกจ', 'Choose a package') }}
        </a>
      </div>
    </div>
  @endforelse
</div>

@if($packages->whereIn('status', ['active', 'frozen'])->isNotEmpty())
  <div class="text-center mb-4">
    <a href="{{ route('customer.purchase.index') }}" class="btn btn-waitlist btn-sm border">
      <i class="bi bi-plus-lg"></i> {{ __t('เติมแพ็กเกจ', 'Top up packages') }}
    </a>
  </div>
@endif

<div class="row g-4">
  <div class="col-md-6">
    <div class="section-title">{{ __t('บัญชี', 'Account') }}</div>
    <div class="menu-list mb-4">
      <div class="menu-row"><div class="mi"><i class="bi bi-telephone"></i></div><span>{{ $customer->phone }}</span></div>
      @if($customer->email)
        <div class="menu-row"><div class="mi"><i class="bi bi-envelope"></i></div><span>{{ $customer->email }}</span></div>
      @endif
      @if($customer->homeBranch)
        <div class="menu-row"><div class="mi"><i class="bi bi-geo-alt"></i></div><span>{{ $customer->homeBranch->name }}</span></div>
      @endif
      <a href="{{ route('customer.profile.edit') }}" class="menu-row">
        <div class="mi"><i class="bi bi-pencil"></i></div>
        <span>{{ __t('แก้ไขข้อมูล', 'Edit profile') }}</span>
        <div class="chev"><i class="bi bi-chevron-right"></i></div>
      </a>
    </div>
  </div>

  <div class="col-md-6">
    <div class="section-title">{{ __t('อื่นๆ', 'Other') }}</div>
    <div class="menu-list">
      <a href="{{ route('customer.purchase.index') }}" class="menu-row">
        <div class="mi"><i class="bi bi-bag-plus"></i></div>
        <span>{{ __t('ซื้อแพ็กเกจ', 'Buy Packages') }}</span>
        <div class="chev"><i class="bi bi-chevron-right"></i></div>
      </a>
      <a href="{{ route('customer.purchase.orders') }}" class="menu-row">
        <div class="mi"><i class="bi bi-receipt"></i></div>
        <span>{{ __t('คำสั่งซื้อของฉัน', 'My Orders') }}</span>
        <div class="chev"><i class="bi bi-chevron-right"></i></div>
      </a>
      <a href="{{ route('customer.guide') }}" class="menu-row">
        <div class="mi"><i class="bi bi-book"></i></div>
        <span>{{ __t('คู่มือการใช้งาน', 'How to Use') }}</span>
        <div class="chev"><i class="bi bi-chevron-right"></i></div>
      </a>
      @if($branches->firstWhere('id', $currentBranchId)?->phone)
        <a href="tel:{{ $branches->firstWhere('id', $currentBranchId)->phone }}" class="menu-row">
          <div class="mi"><i class="bi bi-headset"></i></div>
          <span>{{ __t('ติดต่อสาขา', 'Contact Studio') }}</span>
          <div class="chev"><i class="bi bi-chevron-right"></i></div>
        </a>
      @endif
      <form method="POST" action="{{ route('customer.logout') }}">
        @csrf
        <button class="menu-row w-100 border-0 bg-transparent text-start" type="submit">
          <div class="mi"><i class="bi bi-box-arrow-right"></i></div>
          <span>{{ __t('ออกจากระบบ', 'Log Out') }}</span>
          <div class="chev"><i class="bi bi-chevron-right"></i></div>
        </button>
      </form>
    </div>
  </div>
</div>
@endsection

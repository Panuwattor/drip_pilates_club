@extends('customer.layout')
@section('title', __t('โปรไฟล์', 'Profile'))

@section('content')
<div class="page-header">
  <h1>{{ __t('โปรไฟล์', 'Profile') }}</h1>
  <p>{{ __t('บัญชีและแพ็กเกจของคุณ', 'Your account and packages') }}</p>
</div>

@guest('customer')
  <div class="no-class-note">
    {{ __t('เข้าสู่ระบบเพื่อจัดการบัญชีของคุณ', 'Log in to manage your account') }}
    <div class="mt-3 d-flex gap-2 justify-content-center">
      <a href="{{ route('customer.login') }}" class="btn btn-book btn-sm">{{ __t('เข้าสู่ระบบ', 'Log in') }}</a>
      <a href="{{ route('customer.register') }}" class="btn btn-waitlist btn-sm border">{{ __t('สมัครสมาชิก', 'Sign up') }}</a>
    </div>
  </div>
@else
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
      <div class="stat-box">
        <div class="stat-icon" style="background:var(--accent-soft);color:var(--accent-deep);"><i class="bi bi-activity"></i></div>
        <div class="num">{{ $customer->bookings()->where('status', 'attended')->count() }}</div>
        <div class="lbl">{{ __t('คลาสทั้งหมด', 'Classes') }}</div>
      </div>
    </div>
    <div class="col">
      <div class="stat-box">
        <div class="stat-icon" style="background:var(--sage-soft);color:var(--sage);"><i class="bi bi-ticket-perforated"></i></div>
        <div class="num">{{ $hasUnlimited ? '∞' : $totalCredits }}</div>
        <div class="lbl">{{ __t('เครดิตคงเหลือ', 'Credits') }}</div>
      </div>
    </div>
    <div class="col">
      <div class="stat-box">
        <div class="stat-icon" style="background:#F4E3C7;color:#8A6112;"><i class="bi bi-calendar-check"></i></div>
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
      <div class="col-12"><div class="no-class-note">{{ __t('ยังไม่มีแพ็กเกจที่ใช้งานได้', 'No active packages') }}</div></div>
    @endforelse
  </div>

  <div class="section-title">{{ __t('แพ็กเกจที่เปิดขาย', 'Available Packages') }}</div>
  <div class="row g-3 mb-4">
    @foreach($shopPackages as $p)
      <div class="col-md-4">
        <div class="panel h-100">
          <h4 style="font-size:1rem;font-weight:700;margin:0 0 .2rem;">{{ $p->name }}</h4>
          @if($p->description)<p class="small text-secondary mb-2">{{ $p->description }}</p>@endif
          <div style="font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;font-size:1.5rem;color:var(--accent-deep);">
            {{ number_format($p->price) }} <span style="font-size:.8rem;">฿</span>
            @if($p->compare_at_price)
              <span class="small text-secondary text-decoration-line-through">{{ number_format($p->compare_at_price) }}</span>
            @endif
          </div>
          <div class="small text-secondary mt-1">
            {{ $p->credit_amount === null ? __t('ไม่จำกัดจำนวนครั้ง', 'Unlimited classes') : __t($p->credit_amount . ' ครั้ง', $p->credit_amount . ' classes') }}
            · {{ __t('ใช้ได้ ' . $p->valid_days . ' วัน', 'valid ' . $p->valid_days . ' days') }}
          </div>
          <div class="small text-secondary mt-2">
            <i class="bi bi-info-circle"></i> {{ __t('ติดต่อเจ้าหน้าที่ที่สาขาเพื่อซื้อ', 'Contact staff at the studio to purchase') }}
          </div>
        </div>
      </div>
    @endforeach
  </div>

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
@endguest
@endsection

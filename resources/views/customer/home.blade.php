@extends('customer.layout')
@section('title', __t('หน้าแรก', 'Home'))

@if($branches->count() > 1)
@section('branch-switcher')
  <div class="branch-switch">
    <button class="branch-btn" id="branchToggle" type="button" aria-haspopup="true" aria-expanded="false">
      <span class="bb-pin"><i class="bi bi-geo-alt-fill"></i></span>
      <span class="bb-name" id="branchBtnName">{{ $branches->firstWhere('id', $currentBranchId)?->name }}</span>
      <span class="bb-caret"><i class="bi bi-chevron-down"></i></span>
    </button>
    <div class="branch-pop" id="branchPop" role="menu">
      <div class="bp-title">{{ __t('เลือกสาขา', 'Select Branch') }}</div>
      <div id="branchOptions"></div>
    </div>
  </div>
@endsection
@endif

@section('extra-style')
  .announce-card{
    display:flex; align-items:flex-start; gap:.85rem;
    background:linear-gradient(135deg,var(--accent-deep),var(--accent));
    color:#fff; border-radius:22px; padding:1.1rem 1.25rem;
    box-shadow:0 10px 24px rgba(140,114,85,.24);
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
    <a href="{{ $branch?->google_map_url ?: '#' }}" @if($branch?->google_map_url) target="_blank" @endif class="quick-item" id="findUsLink"><div class="qi-icon"><i class="bi bi-geo-alt"></i></div><span>{{ __t('แผนที่สาขา', 'Find Us') }}</span></a>
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

@if($branches->count() > 1)
@section('extra-script')
/* ---------- สลับสาขา (ใช้ key เดียวกับหน้าตารางคลาส) ---------- */
var BRANCHES = @json($branchesForJs);
var BRANCH_STORAGE_KEY = 'dripBranch';
var currentBranch = @json((string) $currentBranchId);

function getBranch(id){
  for (var i = 0; i < BRANCHES.length; i++){
    if (BRANCHES[i].id === id) return BRANCHES[i];
  }
  return BRANCHES[0];
}
function branchText(branch, field){
  return branch[field + (currentLang === 'th' ? 'Th' : 'En')];
}

var branchToggle = document.getElementById('branchToggle');
var branchPop = document.getElementById('branchPop');
var branchOptions = document.getElementById('branchOptions');
var branchBtnName = document.getElementById('branchBtnName');
var findUsLink = document.getElementById('findUsLink');

function renderBranchOptions(){
  branchOptions.innerHTML = '';
  BRANCHES.forEach(function(b){
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'branch-option' + (b.id === currentBranch ? ' selected' : '');
    btn.dataset.branch = b.id;

    var pin = document.createElement('span');
    pin.className = 'bo-pin';
    pin.innerHTML = '<i class="bi bi-geo-alt-fill"></i>';

    var copy = document.createElement('span');
    var name = document.createElement('span');
    name.className = 'bo-name';
    name.textContent = branchText(b, 'name');
    var addr = document.createElement('span');
    addr.className = 'bo-addr';
    addr.textContent = branchText(b, 'addr');
    copy.appendChild(name);
    copy.appendChild(addr);

    var check = document.createElement('span');
    check.className = 'bo-check';
    check.innerHTML = '<i class="bi bi-check-lg"></i>';

    btn.appendChild(pin);
    btn.appendChild(copy);
    btn.appendChild(check);
    btn.addEventListener('click', function(){
      selectBranch(this.dataset.branch);
      closeBranchPop();
    });
    branchOptions.appendChild(btn);
  });
}

function updateFindUs(){
  var b = getBranch(currentBranch);
  if (b && b.mapUrl){
    findUsLink.setAttribute('href', b.mapUrl);
    findUsLink.setAttribute('target', '_blank');
  } else {
    findUsLink.setAttribute('href', '#');
    findUsLink.removeAttribute('target');
  }
}

function renderBranchUI(){
  var b = getBranch(currentBranch);
  branchBtnName.textContent = branchText(b, 'name');
  renderBranchOptions();
  updateFindUs();
}

function selectBranch(id){
  if (!id) return;
  currentBranch = id;
  try { localStorage.setItem(BRANCH_STORAGE_KEY, id); } catch (e) { /* storage unavailable */ }
  renderBranchUI();
}

function openBranchPop(){
  renderBranchOptions();
  branchPop.classList.add('open');
  branchToggle.setAttribute('aria-expanded', 'true');
}
function closeBranchPop(){
  branchPop.classList.remove('open');
  branchToggle.setAttribute('aria-expanded', 'false');
}

branchToggle.addEventListener('click', function(e){
  e.stopPropagation();
  if (branchPop.classList.contains('open')) { closeBranchPop(); } else { openBranchPop(); }
});
branchPop.addEventListener('click', function(e){ e.stopPropagation(); });
document.addEventListener('click', closeBranchPop);

/* ดึงสาขาที่เคยเลือกไว้จากหน้าอื่น (เช่น หน้าตารางคลาส) มาใช้ต่อ */
try {
  var saved = localStorage.getItem(BRANCH_STORAGE_KEY);
  if (saved && getBranch(saved) && getBranch(saved).id === saved) { currentBranch = saved; }
} catch (e) { /* storage unavailable */ }

renderBranchUI();
@endsection
@endif

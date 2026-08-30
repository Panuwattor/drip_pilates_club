@extends('customer.layout')
@section('title', __t('ตารางคลาส', 'Schedule'))

@section('branch-switcher')
  <div class="branch-switch">
    <button class="branch-btn" id="branchToggle" type="button" aria-haspopup="true" aria-expanded="false">
      <span class="bb-pin"><i class="bi bi-geo-alt-fill"></i></span>
      <span class="bb-name" id="branchBtnName">{{ $branches->firstWhere('id', $currentBranchId)?->name }}</span>
      <span class="bb-caret"><i class="bi bi-chevron-down"></i></span>
    </button>
    <div class="branch-pop" id="branchPop" role="menu">
      <div class="bp-title" data-th="เลือกสาขา" data-en="Select Branch">เลือกสาขา</div>
      <div id="branchOptions"></div>
    </div>
  </div>
@endsection

@section('content')
<div class="page-header">
  <h1 data-th="ตารางคลาส" data-en="Class Schedule">ตารางคลาส</h1>
  <p data-th="เลือกสาขาและวันเพื่อดูคลาสที่เปิดจอง" data-en="Pick a branch and day to view open classes">เลือกสาขาและวันเพื่อดูคลาสที่เปิดจอง</p>
</div>

<div class="branch-tabs" id="branchTabs"></div>

<div class="branch-bar">
  <div class="bbar-pin"><i class="bi bi-geo-alt-fill"></i></div>
  <div class="bbar-copy">
    <div class="bbar-label" data-th="สาขาที่เลือก" data-en="Selected Branch">สาขาที่เลือก</div>
    <div class="bbar-name" id="branchBarName">{{ $branches->firstWhere('id', $currentBranchId)?->name }}</div>
    <div class="bbar-addr" id="branchBarAddr"></div>
  </div>
  <button class="bbar-switch" id="branchBarSwitch" type="button" data-th="สลับสาขา" data-en="Switch">สลับสาขา</button>
</div>

<div class="d-flex align-items-center justify-content-between mb-2 position-relative">
  <div class="month-label" id="monthLabel">&nbsp;</div>
  <button class="today-btn" id="calendarBtn" type="button" title="Choose date" aria-label="Choose date"><i class="bi bi-calendar3"></i></button>
  <div class="calendar-pop" id="calendarPop">
    <div class="cal-head">
      <button class="cal-nav" id="calPrevMonth" type="button" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button>
      <span id="calMonthLabel"></span>
      <button class="cal-nav" id="calNextMonth" type="button" aria-label="Next month"><i class="bi bi-chevron-right"></i></button>
    </div>
    <div class="cal-dow" id="calDow"></div>
    <div class="cal-grid" id="calGrid"></div>
  </div>
</div>
<div class="d-flex align-items-center gap-2 mb-4">
  <button class="day-nav-btn" id="dayPrev" type="button" aria-label="Previous days"><i class="bi bi-chevron-left"></i></button>
  <div class="d-flex gap-2 overflow-auto pb-2 flex-grow-1" id="dayStrip"></div>
  <button class="day-nav-btn" id="dayNext" type="button" aria-label="Next days"><i class="bi bi-chevron-right"></i></button>
</div>

<div class="section-title" id="selectedDateLabel" data-th="อังคาร 5 ส.ค." data-en="Tuesday, Aug 5">อังคาร 5 ส.ค.</div>

<div class="row row-cols-1 row-cols-md-2 g-3" id="classList">
  @include('partials.class-cards', ['sessions' => $sessions, 'myBookings' => $myBookings])
</div>

<div class="no-class-note {{ $sessions->isEmpty() ? '' : 'hidden' }}" id="noClassNote"
     data-th="ยังไม่มีคลาสเปิดจองในสาขานี้สำหรับวันที่เลือก"
     data-en="No open classes at this branch for the selected day">ยังไม่มีคลาสเปิดจองในสาขานี้สำหรับวันที่เลือก</div>
@endsection

@section('extra-script')
var CANCEL_DEADLINE_HOURS = @json($cancelDeadlineHours);

/* ---------- Branches (ข้อมูลจริงจากฐานข้อมูล) ---------- */
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
var branchTabs = document.getElementById('branchTabs');
var branchBtnName = document.getElementById('branchBtnName');
var branchBarName = document.getElementById('branchBarName');
var branchBarAddr = document.getElementById('branchBarAddr');
var classList = document.getElementById('classList');
var noClassNote = document.getElementById('noClassNote');

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

function renderBranchTabs(){
  branchTabs.innerHTML = '';
  BRANCHES.forEach(function(b){
    var tab = document.createElement('button');
    tab.type = 'button';
    tab.className = 'branch-tab' + (b.id === currentBranch ? ' active' : '');
    tab.dataset.branch = b.id;
    tab.innerHTML = '<i class="bi bi-geo-alt"></i>';
    var label = document.createElement('span');
    label.textContent = branchText(b, 'short');
    tab.appendChild(label);
    tab.addEventListener('click', function(){ selectBranch(this.dataset.branch); });
    branchTabs.appendChild(tab);
  });
}

function renderBranchUI(){
  var b = getBranch(currentBranch);
  branchBtnName.textContent = branchText(b, 'name');
  branchBarName.textContent = branchText(b, 'name');
  branchBarAddr.textContent = branchText(b, 'addr');
  renderBranchOptions();
  renderBranchTabs();
}

function selectBranch(id){
  if (!id) return;
  currentBranch = id;
  try { localStorage.setItem(BRANCH_STORAGE_KEY, id); } catch (e) { /* storage unavailable */ }
  renderBranchUI();
  loadSessions();
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

document.getElementById('branchBarSwitch').addEventListener('click', function(){
  var idx = BRANCHES.findIndex(function(b){ return b.id === currentBranch; });
  selectBranch(BRANCHES[(idx + 1) % BRANCHES.length].id);
});

/* ---------- โหลดตารางคลาสตามสาขาและวันที่เลือก ---------- */
var loadToken = 0;

function currentDateString(){
  var d = new Date(scheduleToday);
  d.setDate(d.getDate() + selectedOffset);
  return d.getFullYear() + '-'
    + String(d.getMonth() + 1).padStart(2, '0') + '-'
    + String(d.getDate()).padStart(2, '0');
}

function loadSessions(){
  var token = ++loadToken;
  classList.style.opacity = '.45';

  fetch('{{ route('api.sessions') }}?branch=' + encodeURIComponent(currentBranch)
        + '&date=' + encodeURIComponent(currentDateString()), {
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(function(r){ return r.json(); })
  .then(function(data){
    if (token !== loadToken) return;
    classList.innerHTML = data.html;
    noClassNote.classList.toggle('hidden', data.count > 0);
    classList.style.opacity = '';
  })
  .catch(function(){
    if (token !== loadToken) return;
    classList.style.opacity = '';
  });
}

/* ---------- จองและยกเลิก ---------- */
classList.addEventListener('click', function(e){
  var bookBtn = e.target.closest('.js-book');
  if (bookBtn) { doBook(bookBtn); return; }

  var cancelBtn = e.target.closest('.js-cancel');
  if (cancelBtn) { doCancel(cancelBtn); }
});

function doBook(btn){
  if (!IS_LOGGED_IN) {
    window.location.href = '{{ route('customer.login') }}';
    return;
  }

  btn.disabled = true;

  fetch('/sessions/' + btn.dataset.session + '/book', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': CSRF,
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(function(r){ return r.json().then(function(d){ return { ok: r.ok, data: d }; }); })
  .then(function(res){
    btn.disabled = false;
    if (res.data.need_login) { window.location.href = '{{ route('customer.login') }}'; return; }
    showToast(res.data.message, !res.ok);
    if (res.ok) { loadSessions(); }
  })
  .catch(function(){
    btn.disabled = false;
    showToast(currentLang === 'th' ? 'เกิดข้อผิดพลาด กรุณาลองใหม่' : 'Something went wrong', true);
  });
}

function doCancel(btn){
  var id = btn.dataset.booking;

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

    btn.disabled = true;

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
      btn.disabled = false;
      showToast(res.data.message, !res.ok);
      if (res.ok) { loadSessions(); }
    })
    .catch(function(){
      btn.disabled = false;
      showToast(currentLang === 'th' ? 'เกิดข้อผิดพลาด' : 'Something went wrong', true);
    });
  });
}

var dowTh = ['อา','จ','อ','พ','พฤ','ศ','ส'];
var dowEn = ['Su','Mo','Tu','We','Th','Fr','Sa'];
var monthTh = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
var monthEn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var dowFullTh = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
var dowFullEn = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

var scheduleToday = new Date(@json((int) now()->year), @json((int) now()->month - 1), @json((int) now()->day));
var dayStrip = document.getElementById('dayStrip');
var selectedDateLabel = document.getElementById('selectedDateLabel');
var RANGE_PAST_DAYS = @json($rangePastDays);
var RANGE_FUTURE_DAYS = @json($rangeFutureDays);
var selectedOffset = 0;

var monthLabel = document.getElementById('monthLabel');

function buildDayStrip(){
  dayStrip.innerHTML = '';
  for(var i = -RANGE_PAST_DAYS; i <= RANGE_FUTURE_DAYS; i++){
    var d = new Date(scheduleToday);
    d.setDate(d.getDate() + i);
    var pill = document.createElement('div');
    pill.className = 'day-pill' + (i === selectedOffset ? ' active' : '') + (i === 0 ? ' is-today' : '') + (i < 0 ? ' is-past' : '');
    pill.dataset.offset = i;
    pill.dataset.month = d.getMonth();
    pill.dataset.year = d.getFullYear();
    var dow = document.createElement('span');
    dow.className = 'dow';
    dow.setAttribute('data-th', dowTh[d.getDay()]);
    dow.setAttribute('data-en', dowEn[d.getDay()]);
    dow.textContent = currentLang === 'th' ? dowTh[d.getDay()] : dowEn[d.getDay()];
    var dom = document.createElement('span');
    dom.className = 'dom';
    dom.textContent = d.getDate();
    pill.appendChild(dow);
    pill.appendChild(dom);
    pill.addEventListener('click', function(){ selectDay(parseInt(this.dataset.offset, 10)); });
    dayStrip.appendChild(pill);
  }
}

function updateMonthLabel(){
  var midIndex = Math.round(dayStrip.scrollLeft + dayStrip.clientWidth / 2);
  var pills = dayStrip.querySelectorAll('.day-pill');
  var closest = null, closestDist = Infinity;
  pills.forEach(function(p){
    var dist = Math.abs((p.offsetLeft + p.offsetWidth / 2) - midIndex);
    if(dist < closestDist){ closestDist = dist; closest = p; }
  });
  if(!closest) return;
  var m = parseInt(closest.dataset.month, 10);
  var y = closest.dataset.year;
  monthLabel.textContent = (currentLang === 'th' ? monthTh[m] : monthEn[m]) + ' ' + y;
}

dayStrip.addEventListener('scroll', function(){
  window.requestAnimationFrame(updateMonthLabel);
});

function updateDateLabel(){
  var d = new Date(scheduleToday);
  d.setDate(d.getDate() + selectedOffset);
  var th = dowFullTh[d.getDay()] + ' ' + d.getDate() + ' ' + monthTh[d.getMonth()];
  var en = dowFullEn[d.getDay()] + ', ' + monthEn[d.getMonth()] + ' ' + d.getDate();
  selectedDateLabel.setAttribute('data-th', th);
  selectedDateLabel.setAttribute('data-en', en);
  selectedDateLabel.textContent = currentLang === 'th' ? th : en;
}

function selectDay(offset){
  selectedOffset = offset;
  document.querySelectorAll('.day-pill').forEach(function(p){
    p.classList.toggle('active', parseInt(p.dataset.offset, 10) === offset);
  });
  var activePill = dayStrip.querySelector('.day-pill.active');
  if(activePill){
    activePill.scrollIntoView({ behavior:'smooth', inline:'center', block:'nearest' });
    setTimeout(updateMonthLabel, 350);
  }
  updateDateLabel();
  loadSessions();
}

document.getElementById('dayPrev').addEventListener('click', function(){
  dayStrip.scrollBy({ left:-200, behavior:'smooth' });
});
document.getElementById('dayNext').addEventListener('click', function(){
  dayStrip.scrollBy({ left:200, behavior:'smooth' });
});

function daysBetween(a, b){
  var msPerDay = 24 * 60 * 60 * 1000;
  var utcA = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate());
  var utcB = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate());
  return Math.round((utcB - utcA) / msPerDay);
}

var calendarPop = document.getElementById('calendarPop');
var calendarBtn = document.getElementById('calendarBtn');
var calMonthLabel = document.getElementById('calMonthLabel');
var calDow = document.getElementById('calDow');
var calGrid = document.getElementById('calGrid');
var calViewDate = new Date(scheduleToday);
calViewDate.setDate(calViewDate.getDate() + selectedOffset);
calViewDate.setDate(1);

var minSelectableDate = new Date(scheduleToday);
minSelectableDate.setDate(minSelectableDate.getDate() - RANGE_PAST_DAYS);
var maxSelectableDate = new Date(scheduleToday);
maxSelectableDate.setDate(maxSelectableDate.getDate() + RANGE_FUTURE_DAYS);

function renderCalDow(){
  calDow.innerHTML = '';
  var labels = currentLang === 'th' ? dowTh : dowEn;
  labels.forEach(function(l){
    var el = document.createElement('span');
    el.textContent = l;
    calDow.appendChild(el);
  });
}

function renderCalendar(){
  calMonthLabel.textContent = (currentLang === 'th' ? monthTh[calViewDate.getMonth()] : monthEn[calViewDate.getMonth()]) + ' ' + calViewDate.getFullYear();
  renderCalDow();
  calGrid.innerHTML = '';

  var firstDow = calViewDate.getDay();
  var daysInMonth = new Date(calViewDate.getFullYear(), calViewDate.getMonth() + 1, 0).getDate();
  var selectedDate = new Date(scheduleToday);
  selectedDate.setDate(selectedDate.getDate() + selectedOffset);

  for(var i = 0; i < firstDow; i++){
    var empty = document.createElement('span');
    empty.className = 'cal-day cal-empty';
    calGrid.appendChild(empty);
  }

  for(var day = 1; day <= daysInMonth; day++){
    var d = new Date(calViewDate.getFullYear(), calViewDate.getMonth(), day);
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'cal-day';
    btn.textContent = day;
    if(daysBetween(scheduleToday, d) === 0){ btn.classList.add('cal-today'); }
    if(d.getFullYear() === selectedDate.getFullYear() && d.getMonth() === selectedDate.getMonth() && d.getDate() === selectedDate.getDate()){
      btn.classList.add('cal-selected');
    }
    if(d < minSelectableDate || d > maxSelectableDate){
      btn.disabled = true;
    } else {
      btn.addEventListener('click', function(){
        var picked = new Date(this.dataset.y, this.dataset.m, this.dataset.d);
        selectDay(daysBetween(scheduleToday, picked));
        closeCalendar();
      });
      btn.dataset.y = d.getFullYear();
      btn.dataset.m = d.getMonth();
      btn.dataset.d = d.getDate();
    }
    calGrid.appendChild(btn);
  }

  var prevMonthEnd = new Date(calViewDate.getFullYear(), calViewDate.getMonth(), 0);
  document.getElementById('calPrevMonth').disabled = prevMonthEnd < minSelectableDate;
  var nextMonthStart = new Date(calViewDate.getFullYear(), calViewDate.getMonth() + 1, 1);
  document.getElementById('calNextMonth').disabled = nextMonthStart > maxSelectableDate;
}

function openCalendar(){
  var selectedDate = new Date(scheduleToday);
  selectedDate.setDate(selectedDate.getDate() + selectedOffset);
  calViewDate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
  renderCalendar();
  calendarPop.classList.add('open');
}
function closeCalendar(){
  calendarPop.classList.remove('open');
}

calendarBtn.addEventListener('click', function(e){
  e.stopPropagation();
  if(calendarPop.classList.contains('open')){ closeCalendar(); } else { openCalendar(); }
});
calendarPop.addEventListener('click', function(e){ e.stopPropagation(); });
document.addEventListener('click', function(){ closeCalendar(); closeBranchPop(); });

document.getElementById('calPrevMonth').addEventListener('click', function(){
  calViewDate.setMonth(calViewDate.getMonth() - 1);
  renderCalendar();
});
document.getElementById('calNextMonth').addEventListener('click', function(){
  calViewDate.setMonth(calViewDate.getMonth() + 1);
  renderCalendar();
});

renderBranchUI();
buildDayStrip();
updateDateLabel();
window.requestAnimationFrame(function(){
  var initialActivePill = dayStrip.querySelector('.day-pill.active');
  if(initialActivePill){ initialActivePill.scrollIntoView({ inline:'center', block:'nearest' }); }
  window.requestAnimationFrame(updateMonthLabel);
});
@endsection

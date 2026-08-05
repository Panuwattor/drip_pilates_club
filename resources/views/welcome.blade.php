<!doctype html>
<html lang="th" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Drip Pilates</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root{
    --ground:#EEF1F6;
    --panel:#FFFFFF;
    --ink:#2B3242;
    --ink-soft:#6B7690;
    --accent:#7C93B8;
    --accent-deep:#5E7699;
    --accent-soft:#DCE3EF;
    --sage:#8C9DBE;
    --sage-soft:#E4E9F2;
    --line:#DCE1EB;
  }
  [data-bs-theme="dark"]{
    --ground:#161A22;
    --panel:#20252F;
    --ink:#E9ECF3;
    --ink-soft:#A0ABC2;
    --accent:#9BB0D1;
    --accent-deep:#B7C6E2;
    --accent-soft:#2C3547;
    --sage:#8FA3C4;
    --sage-soft:#2A3243;
    --line:#333B4C;
  }
  body{
    background:var(--ground);
    color:var(--ink);
    font-family:'Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
  }

  .app-topbar{
    position:fixed; top:0; left:0; right:0; z-index:20;
    background:var(--panel); border-bottom:1px solid var(--line);
    padding:.75rem 0;
  }
  body{ padding-top:62px; }
  .app-topbar .brandmark{
    font-family:Georgia,serif; font-weight:700; font-size:1.15rem; color:var(--ink);
  }
  .brand-lockup{ display:flex; align-items:center; gap:.55rem; }
  .brand-logo{
    width:34px; height:34px; border-radius:50%; object-fit:cover;
    border:1px solid var(--line);
  }
  .app-topbar .theme-btn{
    background:var(--ground); border:1px solid var(--line); color:var(--ink-soft);
    border-radius:999px; font-size:.78rem; padding:.4rem .9rem;
  }
  .app-topbar .lang-btn{
    background:var(--ground); border:1px solid var(--line);
    border-radius:999px; width:34px; height:34px; padding:0;
    display:flex; align-items:center; justify-content:center; overflow:hidden;
  }
  .app-topbar .lang-btn img{ width:20px; height:20px; border-radius:50%; object-fit:cover; }

  body{ padding-bottom:78px; }

  .footer-nav{
    position:fixed; bottom:0; left:0; right:0; z-index:20;
    background:var(--panel); border-top:1px solid var(--line);
    box-shadow:0 -4px 16px rgba(0,0,0,.06);
  }
  .footer-nav .nav-inner{
    max-width:960px; margin:0 auto;
    display:flex; padding:.55rem .5rem calc(.7rem + env(safe-area-inset-bottom));
  }
  .footer-nav .nav-link{
    flex:1; background:none; border:none; display:flex; flex-direction:column; align-items:center;
    gap:3px; color:var(--ink-soft); font-size:.68rem; font-weight:600; padding:.4rem 0;
    border-radius:12px; margin:0 .2rem;
  }
  .footer-nav .nav-link .ic{ font-size:1.15rem; line-height:1; }
  .footer-nav .nav-link.active{ color:var(--accent-deep); background:var(--accent-soft); font-weight:700; }

  .page-header{ padding:1.75rem 0 1.25rem; }
  .page-header h1{ font-family:Georgia,serif; font-weight:600; font-size:1.75rem; margin:.2rem 0 0; }
  .page-header p{ margin:0; font-size:.9rem; color:var(--ink-soft); }
  .page-header .brandmark{
    font-size:.72rem; letter-spacing:.14em; text-transform:uppercase;
    color:var(--ink-soft); font-weight:600;
  }

  .section-title{
    font-size:.72rem; letter-spacing:.1em; text-transform:uppercase;
    color:var(--ink-soft); margin:0 0 .75rem; font-weight:700;
  }

  .panel{
    background:var(--panel); border:1px solid var(--line); border-radius:16px; padding:1.25rem;
  }

  .credit-card{
    background:linear-gradient(155deg,var(--accent-deep),var(--accent));
    border-radius:18px; padding:1.5rem; color:#FBF3F0;
    display:flex; justify-content:space-between; align-items:flex-end; height:100%;
  }
  .credit-card .cc-count{ font-family:Georgia,serif; font-size:2.6rem; line-height:1; font-variant-numeric:tabular-nums; }
  .cc-btn{
    background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.4);
    color:#FBF3F0; border-radius:999px; font-size:.78rem; font-weight:600;
    padding:.45rem 1rem; white-space:nowrap;
  }
  .cc-btn:hover{ background:rgba(255,255,255,.28); color:#fff; }

  .quick-item{
    background:var(--panel); border:1px solid var(--line); border-radius:14px;
    padding:1rem .5rem; text-align:center; font-size:.78rem; color:var(--ink-soft);
    text-decoration:none; display:block; height:100%; transition:border-color .15s, transform .15s;
  }
  .quick-item:hover{ border-color:var(--accent); transform:translateY(-2px); color:var(--ink-soft); }
  .quick-item .qi-icon{
    width:42px; height:42px; border-radius:12px; background:var(--accent-soft);
    color:var(--accent-deep); display:flex; align-items:center; justify-content:center;
    margin:0 auto .55rem; font-size:1.15rem;
  }

  .avatar-round{
    width:64px; height:64px; border-radius:50%; background:var(--sage-soft); color:var(--sage);
    font-family:Georgia,serif; font-weight:600; display:flex; align-items:center; justify-content:center;
    border:2px solid var(--panel); box-shadow:0 1px 3px rgba(0,0,0,.08);
    overflow:hidden;
  }
  .avatar-round img{ width:100%; height:100%; object-fit:cover; }
  .instructor-name{ font-size:.78rem; color:var(--ink-soft); font-weight:600; }

  .day-pill{
    flex:0 0 auto; width:52px; padding:.6rem 0; border-radius:14px; text-align:center;
    border:1px solid var(--line); background:var(--panel); cursor:pointer;
  }
  .day-pill .dow{ display:block; font-size:.65rem; color:var(--ink-soft); }
  .day-pill .dom{ display:block; font-size:1rem; font-weight:700; font-variant-numeric:tabular-nums; margin-top:2px; }
  .day-pill.active{ background:var(--accent); border-color:var(--accent); }
  .day-pill.active .dow,.day-pill.active .dom{ color:#FBF3F0; }

  .day-nav-btn{
    flex:0 0 auto; width:34px; height:34px; border-radius:50%;
    border:1px solid var(--line); background:var(--panel); color:var(--ink-soft);
    display:flex; align-items:center; justify-content:center; font-size:.85rem;
  }
  .day-nav-btn:hover{ border-color:var(--accent); color:var(--accent); }

  .month-label{
    font-family:Georgia,serif; font-weight:700; font-size:1rem; color:var(--ink);
  }
  .today-btn{
    background:var(--accent-soft); border:1px solid var(--accent-soft); color:var(--accent-deep);
    border-radius:50%; width:30px; height:30px; padding:0; font-size:.85rem;
    display:flex; align-items:center; justify-content:center; flex:0 0 auto;
  }
  .today-btn:hover{ border-color:var(--accent); }

  .calendar-pop{
    display:none; position:absolute; top:calc(100% + 8px); right:0; z-index:30;
    background:var(--panel); border:1px solid var(--line); border-radius:16px;
    box-shadow:0 12px 32px rgba(0,0,0,.14); padding:.9rem; width:280px;
  }
  .calendar-pop.open{ display:block; }
  .cal-head{
    display:flex; align-items:center; justify-content:space-between;
    font-family:Georgia,serif; font-weight:700; font-size:.92rem; color:var(--ink);
    margin-bottom:.6rem;
  }
  .cal-nav{
    background:none; border:none; color:var(--ink-soft); width:28px; height:28px;
    border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.8rem;
  }
  .cal-nav:hover{ background:var(--ground); color:var(--accent); }
  .cal-nav:disabled{ opacity:.3; pointer-events:none; }
  .cal-dow{
    display:grid; grid-template-columns:repeat(7,1fr); text-align:center;
    font-size:.62rem; color:var(--ink-soft); font-weight:700; margin-bottom:.3rem;
  }
  .cal-grid{ display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
  .cal-day{
    aspect-ratio:1; display:flex; align-items:center; justify-content:center;
    border:none; background:none; border-radius:50%; font-size:.78rem; color:var(--ink);
    font-variant-numeric:tabular-nums;
  }
  .cal-day:hover:not(:disabled){ background:var(--sage-soft); }
  .cal-day:disabled{ color:var(--ink-soft); opacity:.35; }
  .cal-day.cal-empty{ visibility:hidden; }
  .cal-day.cal-today{ color:var(--accent); font-weight:700; }
  .cal-day.cal-selected{ background:var(--accent); color:#FBF3F0; font-weight:700; }

  .day-pill{ scroll-snap-align:center; position:relative; }
  #dayStrip{ scroll-snap-type:x proximity; }
  .day-pill.is-past{ opacity:.4; pointer-events:none; cursor:default; }
  .day-pill.is-today:not(.active)::after{
    content:''; position:absolute; bottom:5px; left:50%; transform:translateX(-50%);
    width:4px; height:4px; border-radius:50%; background:var(--accent);
  }

  .class-card{
    display:flex; border:1px solid var(--line); border-radius:16px; overflow:hidden;
    background:var(--panel); margin-bottom:.75rem; transition:border-color .15s;
  }
  a.class-card{ color:inherit; }
  a.class-card:hover{ border-color:var(--accent); color:inherit; }
  .class-time-rail{
    width:76px; flex:none; background:var(--accent-soft); color:var(--accent-deep);
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    font-size:.9rem; font-weight:700; font-variant-numeric:tabular-nums; padding:.5rem;
  }
  .class-time-rail small{ font-weight:500; font-size:.65rem; opacity:.8; }
  .class-info{ padding:.9rem 1.1rem; flex:1; }
  .class-info h4{ font-size:.95rem; font-weight:700; margin:0; }
  .class-info .meta{ font-size:.78rem; color:var(--ink-soft); margin:2px 0 0; display:flex; align-items:center; gap:.4rem; }
  .coach-avatar{
    width:32px; height:32px; border-radius:50%; object-fit:cover; flex:0 0 auto;
  }

  .btn-book{ background:var(--accent); border-color:var(--accent); color:#FBF3F0; font-size:.78rem; font-weight:700; }
  .btn-book:hover{ background:var(--accent-deep); border-color:var(--accent-deep); color:#fff; }
  .btn-waitlist{ border-color:var(--line); color:var(--ink-soft); font-size:.78rem; font-weight:600; }

  .booking-actions{ display:flex; gap:.5rem; }
  .booking-actions a{
    font-size:.76rem; font-weight:600; text-decoration:none;
    border-radius:999px; padding:.35rem .85rem; border:1px solid var(--accent);
    color:var(--accent);
  }
  .booking-actions a:hover{ background:var(--accent); color:#FBF3F0; }
  .booking-actions a.muted{ color:var(--ink-soft); border-color:var(--line); }
  .booking-actions a.muted:hover{ background:var(--ground); color:var(--ink-soft); }

  .stat-box{ border:1px solid var(--line); border-radius:14px; padding:1rem; text-align:center; background:var(--panel); }
  .stat-box .stat-icon{
    width:36px; height:36px; border-radius:10px; margin:0 auto .5rem;
    display:flex; align-items:center; justify-content:center; font-size:1rem;
  }
  .stat-box .num{ font-family:Georgia,serif; font-size:1.6rem; color:var(--ink); font-variant-numeric:tabular-nums; }
  .stat-box .lbl{ font-size:.68rem; color:var(--ink-soft); text-transform:uppercase; letter-spacing:.05em; margin-top:4px; }

  .profile-hero{
    background:linear-gradient(155deg,var(--accent-deep),var(--accent));
    border-radius:20px; padding:1.5rem; color:#FBF3F0; position:relative; overflow:hidden;
  }
  .profile-hero .avatar-lg{
    width:72px; height:72px; border-radius:50%; background:rgba(255,255,255,.18); color:#FFF;
    font-family:Georgia,serif; font-size:1.5rem; font-weight:600;
    display:flex; align-items:center; justify-content:center;
    border:2px solid rgba(255,255,255,.4); overflow:hidden; flex:0 0 auto;
  }
  .profile-hero .avatar-lg img{ width:100%; height:100%; object-fit:cover; }
  .profile-hero h3{ font-family:Georgia,serif; }
  .member-badge{
    background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.4);
    color:#FBF3F0; border-radius:999px; font-size:.68rem; font-weight:700;
    padding:.25rem .7rem; display:inline-flex; align-items:center; gap:.3rem;
    letter-spacing:.03em; text-transform:uppercase;
  }

  .menu-list{ border:1px solid var(--line); border-radius:14px; overflow:hidden; }
  .menu-row{
    display:flex; align-items:center; gap:.85rem; padding:.9rem 1.1rem;
    border-bottom:1px solid var(--line); font-size:.88rem; color:var(--ink); text-decoration:none;
  }
  .menu-row:hover{ background:var(--ground); }
  .menu-row:last-child{ border-bottom:none; }
  .menu-row .mi{
    width:32px; height:32px; border-radius:8px; background:var(--sage-soft); color:var(--sage);
    display:flex; align-items:center; justify-content:center; font-size:.9rem;
  }
  .menu-row .chev{ margin-left:auto; color:var(--ink-soft); }

  .avatar-lg{
    width:68px; height:68px; border-radius:50%; background:var(--accent-soft); color:var(--accent-deep);
    font-family:Georgia,serif; font-size:1.4rem; font-weight:600;
    display:flex; align-items:center; justify-content:center;
  }

  .tab-pane-view{ display:none; }
  .tab-pane-view.active{ display:block; }
</style>
</head>
<body>

<nav class="app-topbar">
  <div class="container-lg d-flex align-items-center justify-content-between">
    <span class="brand-lockup"><img src="{{ asset('images/logo.jpg') }}" alt="Drip Pilates" class="brand-logo"><span class="brandmark">Drip Pilates</span></span>
    <div class="d-flex align-items-center gap-2">
      <button class="theme-btn" id="themeToggle" type="button"><i class="bi bi-circle-half"></i> <span data-th="โหมดมืด/สว่าง" data-en="Dark/Light">โหมดมืด/สว่าง</span></button>
      <button class="lang-btn" id="langToggle" type="button" title="Change language"><img id="langFlag" src="{{ asset('images/th.png') }}" alt="TH"></button>
    </div>
  </div>
</nav>

<div class="container-lg pb-5">

  <!-- HOME -->
  <div class="tab-pane-view active" id="pane-home">
    <div class="page-header">
      <div class="brandmark" data-th="ภาพรวม" data-en="Overview">ภาพรวม</div>
      <h1 data-th="สวัสดี คุณมิว 👋" data-en="Hello, Miw 👋">สวัสดี คุณมิว 👋</h1>
      <p data-th="วันอังคารที่ 5 สิงหาคม" data-en="Tuesday, August 5">วันอังคารที่ 5 สิงหาคม</p>
    </div>

    <div class="row g-3 mb-2">
      <div class="col-md-6">
        <div class="credit-card">
          <div>
            <div class="text-uppercase small opacity-75" style="font-size:.68rem;letter-spacing:.08em;">Class Credits</div>
            <div class="cc-count">6</div>
            <div class="small opacity-75" data-th="หมดอายุ 30 ก.ย. 2569" data-en="Expires Sep 30, 2026">หมดอายุ 30 ก.ย. 2569</div>
          </div>
          <button class="cc-btn" type="button" data-th="เติมแพ็กเกจ" data-en="Top Up">เติมแพ็กเกจ</button>
        </div>
      </div>
      <div class="col-md-6">
        <a href="#" class="class-card mb-0 h-100 text-decoration-none">
          <div class="class-time-rail">08:00<small data-th="เช้านี้" data-en="This morning">เช้านี้</small></div>
          <div class="class-info d-flex align-items-center justify-content-between gap-2">
            <div>
              <h4>Reformer Flow</h4>
              <p class="meta"><img src="{{ asset('images/01.jpg') }}" alt="Nan" class="coach-avatar"><span data-th="ครูแนน" data-en="Coach Nan">ครูแนน</span> · <span data-th="สตูดิโอ 2" data-en="Studio 2">สตูดิโอ 2</span></p>
            </div>
            <span class="text-secondary"><i class="bi bi-chevron-right"></i></span>
          </div>
        </a>
      </div>
    </div>

    <div class="section-title mt-4" data-th="บริการด่วน" data-en="Quick Actions">บริการด่วน</div>
    <div class="row row-cols-2 row-cols-md-4 g-3 mb-2">
      <div class="col"><a href="#" class="quick-item"><div class="qi-icon"><i class="bi bi-plus-lg"></i></div><span data-th="จองคลาส" data-en="Book Class">จองคลาส</span></a></div>
      <div class="col"><a href="#" class="quick-item"><div class="qi-icon"><i class="bi bi-arrow-repeat"></i></div><span data-th="เลื่อนคลาส" data-en="Reschedule">เลื่อนคลาส</span></a></div>
      <div class="col"><a href="#" class="quick-item"><div class="qi-icon"><i class="bi bi-ticket-perforated"></i></div><span data-th="ซื้อแพ็กเกจ" data-en="Buy Package">ซื้อแพ็กเกจ</span></a></div>
      <div class="col"><a href="#" class="quick-item"><div class="qi-icon"><i class="bi bi-envelope"></i></div><span data-th="ชวนเพื่อน" data-en="Invite Friend">ชวนเพื่อน</span></a></div>
    </div>

    <div class="section-title mt-4" data-th="ครูผู้สอน" data-en="Instructors">ครูผู้สอน</div>
    <div class="row row-cols-4 row-cols-md-6 g-3">
      <div class="col text-center"><div class="avatar-round mx-auto mb-2"><img src="{{ asset('images/01.jpg') }}" alt="Nan"></div><div class="instructor-name" data-th="ครูแนน" data-en="Coach Nan">ครูแนน</div></div>
      <div class="col text-center"><div class="avatar-round mx-auto mb-2"><img src="{{ asset('images/02.jpg') }}" alt="Ta"></div><div class="instructor-name" data-th="ครูต้า" data-en="Coach Ta">ครูต้า</div></div>
      <div class="col text-center"><div class="avatar-round mx-auto mb-2"><img src="{{ asset('images/03.jpg') }}" alt="Fah"></div><div class="instructor-name" data-th="ครูฟ้า" data-en="Coach Fah">ครูฟ้า</div></div>
      <div class="col text-center"><div class="avatar-round mx-auto mb-2"><img src="{{ asset('images/04.jpg') }}" alt="Get"></div><div class="instructor-name" data-th="ครูเก็ท" data-en="Coach Get">ครูเก็ท</div></div>
    </div>

  </div>

  <!-- SCHEDULE -->
  <div class="tab-pane-view" id="pane-schedule">
    <div class="page-header">
      <h1 data-th="ตารางคลาส" data-en="Class Schedule">ตารางคลาส</h1>
      <p data-th="เลือกวันเพื่อดูคลาสที่เปิดจอง" data-en="Pick a day to view open classes">เลือกวันเพื่อดูคลาสที่เปิดจอง</p>
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

    <div class="row row-cols-1 row-cols-md-2 g-3">
      <div class="col">
        <div class="class-card mb-0 h-100">
          <div class="class-time-rail">08:00<small data-th="50 นาที" data-en="50 min">50 นาที</small></div>
          <div class="class-info">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div><h4>Reformer Flow</h4><p class="meta"><img src="{{ asset('images/01.jpg') }}" alt="Nan" class="coach-avatar"><span data-th="ครูแนน" data-en="Coach Nan">ครูแนน</span> · <span data-th="สตูดิโอ 2" data-en="Studio 2">สตูดิโอ 2</span></p></div>
              <span class="badge rounded-pill" style="background:var(--sage-soft);color:var(--sage);" data-th="เหลือ 4 ที่" data-en="4 spots left">เหลือ 4 ที่</span>
            </div>
            <button class="btn btn-book btn-sm mt-2" type="button" data-th="จองเลย" data-en="Book Now">จองเลย</button>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="class-card mb-0 h-100">
          <div class="class-time-rail">09:15<small data-th="45 นาที" data-en="45 min">45 นาที</small></div>
          <div class="class-info">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div><h4>Mat Pilates Basic</h4><p class="meta"><img src="{{ asset('images/03.jpg') }}" alt="Fah" class="coach-avatar"><span data-th="ครูฟ้า" data-en="Coach Fah">ครูฟ้า</span> · <span data-th="สตูดิโอ 1" data-en="Studio 1">สตูดิโอ 1</span></p></div>
              <span class="badge rounded-pill" style="background:#F4E3C7;color:#8A6112;" data-th="เหลือ 1 ที่" data-en="1 spot left">เหลือ 1 ที่</span>
            </div>
            <button class="btn btn-book btn-sm mt-2" type="button" data-th="จองเลย" data-en="Book Now">จองเลย</button>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="class-card mb-0 h-100">
          <div class="class-time-rail">17:30<small data-th="50 นาที" data-en="50 min">50 นาที</small></div>
          <div class="class-info">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div><h4>Reformer Sculpt</h4><p class="meta"><img src="{{ asset('images/02.jpg') }}" alt="Ta" class="coach-avatar"><span data-th="ครูต้า" data-en="Coach Ta">ครูต้า</span> · <span data-th="สตูดิโอ 2" data-en="Studio 2">สตูดิโอ 2</span></p></div>
              <span class="badge rounded-pill" style="background:var(--accent-soft);color:var(--accent-deep);" data-th="เต็ม" data-en="Full">เต็ม</span>
            </div>
            <button class="btn btn-waitlist btn-sm border mt-2" type="button" data-th="เข้าคิว Waitlist" data-en="Join Waitlist">เข้าคิว Waitlist</button>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="class-card mb-0 h-100">
          <div class="class-time-rail">19:00<small data-th="50 นาที" data-en="50 min">50 นาที</small></div>
          <div class="class-info">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div><h4>Prenatal Pilates</h4><p class="meta"><img src="{{ asset('images/04.jpg') }}" alt="Get" class="coach-avatar"><span data-th="ครูเก็ท" data-en="Coach Get">ครูเก็ท</span> · <span data-th="สตูดิโอ 1" data-en="Studio 1">สตูดิโอ 1</span></p></div>
              <span class="badge rounded-pill" style="background:var(--sage-soft);color:var(--sage);" data-th="เหลือ 6 ที่" data-en="6 spots left">เหลือ 6 ที่</span>
            </div>
            <button class="btn btn-book btn-sm mt-2" type="button" data-th="จองเลย" data-en="Book Now">จองเลย</button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- BOOKINGS -->
  <div class="tab-pane-view" id="pane-bookings">
    <div class="page-header">
      <h1 data-th="การจองของฉัน" data-en="My Bookings">การจองของฉัน</h1>
      <p data-th="คลาสที่กำลังจะถึงและประวัติ" data-en="Upcoming classes and history">คลาสที่กำลังจะถึงและประวัติ</p>
    </div>

    <div class="row g-4">
      <div class="col-md-8">
        <div class="section-title" data-th="กำลังจะถึง" data-en="Upcoming">กำลังจะถึง</div>

        <div class="class-card mb-3">
          <div class="class-time-rail">08:00<small data-th="พรุ่งนี้" data-en="Tomorrow">พรุ่งนี้</small></div>
          <div class="class-info">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div><h4>Reformer Flow</h4><p class="meta"><img src="{{ asset('images/01.jpg') }}" alt="Nan" class="coach-avatar"><span data-th="ครูแนน" data-en="Coach Nan">ครูแนน</span> · <span data-th="สตูดิโอ 2" data-en="Studio 2">สตูดิโอ 2</span></p></div>
              <span class="badge rounded-pill" style="background:var(--sage-soft);color:var(--sage);" data-th="ยืนยันแล้ว" data-en="Confirmed">ยืนยันแล้ว</span>
            </div>
            <div class="booking-actions mt-2">
              <a href="#" data-th="เลื่อนคลาส" data-en="Reschedule">เลื่อนคลาส</a>
              <a href="#" class="muted" data-th="ยกเลิก" data-en="Cancel">ยกเลิก</a>
            </div>
          </div>
        </div>

        <div class="class-card mb-3">
          <div class="class-time-rail">09:15<small data-th="พฤ 7 ส.ค." data-en="Thu, Aug 7">พฤ 7 ส.ค.</small></div>
          <div class="class-info">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div><h4>Mat Pilates Basic</h4><p class="meta"><img src="{{ asset('images/03.jpg') }}" alt="Fah" class="coach-avatar"><span data-th="ครูฟ้า" data-en="Coach Fah">ครูฟ้า</span> · <span data-th="สตูดิโอ 1" data-en="Studio 1">สตูดิโอ 1</span></p></div>
              <span class="badge rounded-pill" style="background:var(--sage-soft);color:var(--sage);" data-th="ยืนยันแล้ว" data-en="Confirmed">ยืนยันแล้ว</span>
            </div>
            <div class="booking-actions mt-2">
              <a href="#" data-th="เลื่อนคลาส" data-en="Reschedule">เลื่อนคลาส</a>
              <a href="#" class="muted" data-th="ยกเลิก" data-en="Cancel">ยกเลิก</a>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="section-title" data-th="ประวัติที่ผ่านมา" data-en="Past History">ประวัติที่ผ่านมา</div>
        <div class="class-card mb-2">
          <div class="class-time-rail">17:30<small data-th="จันทร์ 28 ก.ค." data-en="Mon, Jul 28">จันทร์ 28 ก.ค.</small></div>
          <div class="class-info">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div><h4>Reformer Sculpt</h4><p class="meta"><img src="{{ asset('images/02.jpg') }}" alt="Ta" class="coach-avatar"><span data-th="ครูต้า" data-en="Coach Ta">ครูต้า</span></p></div>
              <span class="badge rounded-pill" style="background:var(--accent-soft);color:var(--accent-deep);" data-th="เข้าเรียนแล้ว" data-en="Attended">เข้าเรียนแล้ว</span>
            </div>
          </div>
        </div>

        <div class="text-center small text-secondary border rounded-4 py-4 px-2" style="border-style:dashed !important;border-color:var(--line) !important;" data-th="ดูประวัติย้อนหลังทั้งหมดได้ที่โปรไฟล์" data-en="View full history in Profile">
          ดูประวัติย้อนหลังทั้งหมดได้ที่โปรไฟล์
        </div>
      </div>
    </div>

  </div>

  <!-- PROFILE -->
  <div class="tab-pane-view" id="pane-profile">
    <div class="page-header">
      <h1 data-th="โปรไฟล์" data-en="Profile">โปรไฟล์</h1>
      <p data-th="บัญชีและแพ็กเกจของคุณ" data-en="Your account and packages">บัญชีและแพ็กเกจของคุณ</p>
    </div>

    <div class="profile-hero d-flex align-items-center gap-3 mb-4">
      <div class="avatar-lg"><img src="{{ asset('images/customer.jpg') }}" alt="มิว จันทร์เพ็ญ"></div>
      <div class="flex-grow-1">
        <h3 class="mb-1" style="font-size:1.25rem;" data-th="มิว จันทร์เพ็ญ" data-en="Miw Chanphen">มิว จันทร์เพ็ญ</h3>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="member-badge"><i class="bi bi-gem"></i> <span data-th="สมาชิกโกลด์" data-en="Gold Member">สมาชิกโกลด์</span></span>
          <span class="small opacity-75" data-th="สมาชิกตั้งแต่ ม.ค. 2568" data-en="Member since Jan 2025">สมาชิกตั้งแต่ ม.ค. 2568</span>
        </div>
      </div>
    </div>

    <div class="row row-cols-3 g-2 mb-4">
      <div class="col">
        <div class="stat-box">
          <div class="stat-icon" style="background:var(--accent-soft);color:var(--accent-deep);"><i class="bi bi-activity"></i></div>
          <div class="num">42</div><div class="lbl" data-th="คลาสทั้งหมด" data-en="Total Classes">คลาสทั้งหมด</div>
        </div>
      </div>
      <div class="col">
        <div class="stat-box">
          <div class="stat-icon" style="background:var(--sage-soft);color:var(--sage);"><i class="bi bi-ticket-perforated"></i></div>
          <div class="num">6</div><div class="lbl" data-th="เครดิตคงเหลือ" data-en="Credits Left">เครดิตคงเหลือ</div>
        </div>
      </div>
      <div class="col">
        <div class="stat-box">
          <div class="stat-icon" style="background:#F4E3C7;color:#8A6112;"><i class="bi bi-fire"></i></div>
          <div class="num">3</div><div class="lbl" data-th="สัปดาห์ติดต่อกัน" data-en="Week Streak">สัปดาห์ติดต่อกัน</div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-md-6">
        <div class="section-title" data-th="บัญชี" data-en="Account">บัญชี</div>
        <div class="menu-list mb-4">
          <a href="#" class="menu-row"><div class="mi"><i class="bi bi-ticket-perforated"></i></div><span data-th="แพ็กเกจของฉัน" data-en="My Packages">แพ็กเกจของฉัน</span><div class="chev"><i class="bi bi-chevron-right"></i></div></a>
          <a href="#" class="menu-row"><div class="mi"><i class="bi bi-credit-card"></i></div><span data-th="วิธีการชำระเงิน" data-en="Payment Methods">วิธีการชำระเงิน</span><div class="chev"><i class="bi bi-chevron-right"></i></div></a>
          <a href="#" class="menu-row"><div class="mi"><i class="bi bi-bell"></i></div><span data-th="การแจ้งเตือน" data-en="Notifications">การแจ้งเตือน</span><div class="chev"><i class="bi bi-chevron-right"></i></div></a>
          <a href="#" class="menu-row"><div class="mi"><i class="bi bi-person"></i></div><span data-th="แก้ไขข้อมูลส่วนตัว" data-en="Edit Profile">แก้ไขข้อมูลส่วนตัว</span><div class="chev"><i class="bi bi-chevron-right"></i></div></a>
        </div>
      </div>

      <div class="col-md-6">
        <div class="section-title" data-th="อื่นๆ" data-en="Other">อื่นๆ</div>
        <div class="menu-list">
          <a href="#" class="menu-row"><div class="mi"><i class="bi bi-question-circle"></i></div><span data-th="ศูนย์ช่วยเหลือ" data-en="Help Center">ศูนย์ช่วยเหลือ</span><div class="chev"><i class="bi bi-chevron-right"></i></div></a>
          <a href="#" class="menu-row"><div class="mi"><i class="bi bi-box-arrow-right"></i></div><span data-th="ออกจากระบบ" data-en="Log Out">ออกจากระบบ</span><div class="chev"><i class="bi bi-chevron-right"></i></div></a>
        </div>
      </div>
    </div>

  </div>

</div>

<nav class="footer-nav">
  <div class="nav-inner">
    <button class="nav-link active" data-pane="pane-home" type="button"><span class="ic"><i class="bi bi-house"></i></span><span data-th="หน้าแรก" data-en="Home">หน้าแรก</span></button>
    <button class="nav-link" data-pane="pane-schedule" type="button"><span class="ic"><i class="bi bi-grid-3x3-gap"></i></span><span data-th="ตารางคลาส" data-en="Schedule">ตารางคลาส</span></button>
    <button class="nav-link" data-pane="pane-bookings" type="button"><span class="ic"><i class="bi bi-check-circle"></i></span><span data-th="การจอง" data-en="Bookings">การจอง</span></button>
    <button class="nav-link" data-pane="pane-profile" type="button"><span class="ic"><i class="bi bi-person-circle"></i></span><span data-th="โปรไฟล์" data-en="Profile">โปรไฟล์</span></button>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.nav-link[data-pane]').forEach(function(btn){
  btn.addEventListener('click', function(){
    document.querySelectorAll('.nav-link[data-pane]').forEach(function(b){ b.classList.remove('active'); });
    document.querySelectorAll('.tab-pane-view').forEach(function(v){ v.classList.remove('active'); });
    btn.classList.add('active');
    document.getElementById(btn.dataset.pane).classList.add('active');
    window.scrollTo(0,0);
  });
});

var currentLang = 'th';
var dowTh = ['อา','จ','อ','พ','พฤ','ศ','ส'];
var dowEn = ['Su','Mo','Tu','We','Th','Fr','Sa'];
var monthTh = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
var monthEn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var dowFullTh = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
var dowFullEn = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

var scheduleToday = new Date(2026, 7, 5);
var dayStrip = document.getElementById('dayStrip');
var selectedDateLabel = document.getElementById('selectedDateLabel');
var RANGE_PAST_DAYS = 2;
var RANGE_FUTURE_DAYS = 90;
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
document.addEventListener('click', function(){ closeCalendar(); });

document.getElementById('calPrevMonth').addEventListener('click', function(){
  calViewDate.setMonth(calViewDate.getMonth() - 1);
  renderCalendar();
});
document.getElementById('calNextMonth').addEventListener('click', function(){
  calViewDate.setMonth(calViewDate.getMonth() + 1);
  renderCalendar();
});

buildDayStrip();
updateDateLabel();
window.requestAnimationFrame(function(){
  var initialActivePill = dayStrip.querySelector('.day-pill.active');
  if(initialActivePill){ initialActivePill.scrollIntoView({ inline:'center', block:'nearest' }); }
  window.requestAnimationFrame(updateMonthLabel);
});

document.getElementById('themeToggle').addEventListener('click', function(){
  var html = document.documentElement;
  html.setAttribute('data-bs-theme', html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
});

document.getElementById('langToggle').addEventListener('click', function(){
  currentLang = currentLang === 'th' ? 'en' : 'th';
  document.documentElement.setAttribute('lang', currentLang);
  document.getElementById('langFlag').src = '{{ asset('images') }}/' + currentLang + '.png';
  document.getElementById('langFlag').alt = currentLang.toUpperCase();
  document.querySelectorAll('[data-th]').forEach(function(el){
    el.textContent = el.getAttribute('data-' + currentLang);
  });
  updateMonthLabel();
  if(calendarPop.classList.contains('open')){ renderCalendar(); }
});
</script>
</body>
</html>

<!doctype html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Drip Pilates</title>
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#7C93B8">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
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
  body{ padding-top:62px; overflow-x:hidden; }

  /* Bootstrap ไม่มีคลาสนี้ ต้องประกาศเอง กันข้อความยาวดันความกว้างจนล้น */
  .min-width-0{ min-width:0; }
  .class-info{ min-width:0; }
  .class-info h4{ overflow-wrap:anywhere; }
  .class-info .meta{ flex-wrap:wrap; }
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

  @media (max-width:575.98px){
    .app-topbar .brandmark{ display:none; }
    .app-topbar .theme-btn span{ display:none; }
    .app-topbar .theme-btn{ width:34px; height:34px; padding:0; display:flex; align-items:center; justify-content:center; }
    .branch-btn{ max-width:150px; }
  }

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

  .branch-switch{ position:relative; flex:0 0 auto; }
  .branch-btn{
    background:var(--ground); border:1px solid var(--line); color:var(--ink);
    border-radius:999px; padding:.35rem .75rem; display:flex; align-items:center; gap:.4rem;
    font-size:.78rem; font-weight:600; max-width:190px;
  }
  .branch-btn:hover{ border-color:var(--accent); }
  .branch-btn .bb-pin{
    width:22px; height:22px; border-radius:50%; background:var(--accent-soft); color:var(--accent-deep);
    display:flex; align-items:center; justify-content:center; font-size:.72rem; flex:0 0 auto;
  }
  .branch-btn .bb-name{ white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .branch-btn .bb-caret{ color:var(--ink-soft); font-size:.65rem; flex:0 0 auto; }

  .branch-pop{
    display:none; position:absolute; top:calc(100% + 8px); right:0; z-index:40;
    background:var(--panel); border:1px solid var(--line); border-radius:16px;
    box-shadow:0 12px 32px rgba(0,0,0,.14); padding:.5rem; width:270px;
  }
  .branch-pop.open{ display:block; }
  .branch-pop .bp-title{
    font-size:.66rem; letter-spacing:.1em; text-transform:uppercase;
    color:var(--ink-soft); font-weight:700; padding:.4rem .6rem .5rem;
  }
  .branch-option{
    width:100%; display:flex; align-items:center; gap:.7rem; text-align:left;
    background:none; border:1px solid transparent; border-radius:12px; padding:.6rem;
    color:var(--ink);
  }
  .branch-option:hover{ background:var(--ground); }
  .branch-option .bo-pin{
    width:34px; height:34px; border-radius:10px; background:var(--sage-soft); color:var(--sage);
    display:flex; align-items:center; justify-content:center; font-size:.9rem; flex:0 0 auto;
  }
  .branch-option .bo-name{ font-size:.85rem; font-weight:700; display:block; }
  .branch-option .bo-addr{ font-size:.72rem; color:var(--ink-soft); display:block; margin-top:1px; }
  .branch-option .bo-check{ margin-left:auto; color:var(--accent); font-size:.9rem; opacity:0; flex:0 0 auto; }
  .branch-option.selected{ border-color:var(--accent); background:var(--accent-soft); }
  .branch-option.selected .bo-pin{ background:var(--accent); color:#FBF3F0; }
  .branch-option.selected .bo-check{ opacity:1; }

  .branch-bar{
    display:flex; align-items:center; gap:.75rem;
    background:var(--panel); border:1px solid var(--line); border-radius:16px;
    padding:.8rem 1rem; margin-bottom:1rem;
  }
  .branch-bar .bbar-pin{
    width:38px; height:38px; border-radius:12px; background:var(--accent-soft); color:var(--accent-deep);
    display:flex; align-items:center; justify-content:center; font-size:1rem; flex:0 0 auto;
  }
  .branch-bar .bbar-copy{ flex:1; min-width:0; }
  .branch-bar .bbar-label{
    font-size:.66rem; letter-spacing:.1em; text-transform:uppercase; color:var(--ink-soft); font-weight:700;
  }
  .branch-bar .bbar-name{ font-size:.95rem; font-weight:700; color:var(--ink); }
  .branch-bar .bbar-addr{ font-size:.74rem; color:var(--ink-soft); }
  .branch-bar .bbar-switch{
    background:var(--ground); border:1px solid var(--line); color:var(--accent-deep);
    border-radius:999px; font-size:.74rem; font-weight:700; padding:.4rem .85rem; white-space:nowrap;
  }
  .branch-bar .bbar-switch:hover{ border-color:var(--accent); }

  .branch-tabs{ display:flex; gap:.5rem; margin-bottom:1rem; }
  .branch-tab{
    flex:1; background:var(--panel); border:1px solid var(--line); border-radius:14px;
    padding:.6rem .5rem; color:var(--ink-soft); font-size:.8rem; font-weight:700;
    display:flex; align-items:center; justify-content:center; gap:.4rem;
  }
  .branch-tab:hover{ border-color:var(--accent); }
  .branch-tab.active{ background:var(--accent); border-color:var(--accent); color:#FBF3F0; }

  .no-class-note{
    text-align:center; font-size:.85rem; color:var(--ink-soft);
    border:1px dashed var(--line); border-radius:16px; padding:2rem 1rem;
  }
  .no-class-note.hidden{ display:none; }

  .install-banner{
    position:fixed;
    left:12px;
    right:12px;
    bottom:88px;
    z-index:30;
    background:rgba(255,255,255,0.98);
    border:1px solid rgba(0,0,0,0.08);
    border-radius:18px;
    box-shadow:0 12px 36px rgba(0,0,0,0.12);
    padding:1rem 1rem 0.8rem;
    display:flex;
    align-items:flex-start;
    gap:0.85rem;
  }
  .install-banner.hidden{ display:none; }
  .install-banner .install-icon{
    width:44px;
    height:44px;
    border-radius:14px;
    background:#7C93B8;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.2rem;
    flex-shrink:0;
  }
  .install-banner .install-copy{
    flex:1;
    min-width:0;
  }
  .install-banner .install-copy strong{ display:block; margin-bottom:0.15rem; }
  .install-banner .install-actions{
    display:flex;
    gap:0.5rem;
    flex-wrap:wrap;
    margin-top:0.6rem;
  }
  .install-banner .btn-install{
    background:#7C93B8;
    color:#fff;
    border:none;
    border-radius:999px;
    padding:0.55rem 1rem;
  }
  .install-banner .btn-openchrome{
    background:#4B5563;
    color:#fff;
    border:none;
    border-radius:999px;
    padding:0.55rem 1rem;
  }
  .install-banner .btn-dismiss{
    background:transparent;
    color:#6B7690;
    border:none;
    padding:0.55rem 1rem;
    border-radius:999px;
  }
  .app-toast{
    position:fixed; left:50%; transform:translateX(-50%) translateY(20px);
    bottom:92px; z-index:60; max-width:calc(100vw - 32px);
    background:var(--ink); color:var(--panel);
    border-radius:999px; padding:.7rem 1.25rem; font-size:.85rem; font-weight:600;
    box-shadow:0 8px 28px rgba(0,0,0,.22);
    opacity:0; pointer-events:none; transition:opacity .2s, transform .2s;
  }
  .app-toast.show{ opacity:1; transform:translateX(-50%) translateY(0); }
  .app-toast.error{ background:#9B3232; color:#fff; }
</style>
</head>
<body>

<nav class="app-topbar">
  <div class="container-lg d-flex align-items-center justify-content-between">
    <span class="brand-lockup"><img src="{{ asset('images/logo.jpg') }}" alt="Drip Pilates" class="brand-logo"><span class="brandmark">Drip Pilates</span></span>
    <div class="d-flex align-items-center gap-2">
      <div class="branch-switch">
        <button class="branch-btn" id="branchToggle" type="button" aria-haspopup="true" aria-expanded="false">
          <span class="bb-pin"><i class="bi bi-geo-alt-fill"></i></span>
          <span class="bb-name" id="branchBtnName">สาขาสุขุมวิท</span>
          <span class="bb-caret"><i class="bi bi-chevron-down"></i></span>
        </button>
        <div class="branch-pop" id="branchPop" role="menu">
          <div class="bp-title" data-th="เลือกสาขา" data-en="Select Branch">เลือกสาขา</div>
          <div id="branchOptions"></div>
        </div>
      </div>
      <button class="theme-btn" id="themeToggle" type="button"><i class="bi bi-circle-half"></i> <span data-th="โหมดมืด/สว่าง" data-en="Dark/Light">โหมดมืด/สว่าง</span></button>
      <button class="lang-btn" id="langToggle" type="button" title="Change language"><img id="langFlag" src="{{ asset('images/' . app()->getLocale() . '.png') }}" alt="{{ strtoupper(app()->getLocale()) }}"></button>
    </div>
  </div>
</nav>

<div class="container-lg pb-5">
  <div id="installBanner" class="install-banner hidden" role="dialog" aria-live="polite">
    <div class="install-icon"><i class="bi bi-phone"></i></div>
    <div class="install-copy">
      <strong id="installTitle" data-th="ติดตั้ง Drip Pilates" data-en="Install Drip Pilates">ติดตั้ง Drip Pilates</strong>
      <div id="installMessage">เพิ่มลงหน้าจอโฮม เปิดใช้งานได้เร็วเหมือนแอป</div>
      <div id="installInstructions" class="install-instructions hidden"></div>
      <div class="install-actions">
        <button id="installBtn" class="btn-install" type="button" data-th="ติดตั้ง" data-en="Install">ติดตั้ง</button>
        <button id="openInChromeBtn" class="btn-openchrome hidden" type="button" data-th="เปิดใน Chrome" data-en="Open in Chrome">เปิดใน Chrome</button>
        <button id="dismissInstallBtn" class="btn-dismiss" type="button" data-th="ปิด" data-en="Dismiss">ปิด</button>
      </div>
    </div>
  </div>

  <!-- HOME -->
  <div class="tab-pane-view active" id="pane-home">
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
      <div class="mb-3 p-3 rounded-4" style="background:var(--accent-soft);color:var(--accent-deep);">
        <strong>{{ $ann->title }}</strong>
        @if($ann->body)<div class="small mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($ann->body), 140) }}</div>@endif
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
          <a href="#" class="cc-btn text-decoration-none js-goto-profile">{{ __t('เติมแพ็กเกจ', 'Top Up') }}</a>
        </div>
      </div>

      <div class="col-md-6">
        @php $next = $upcoming->first(); @endphp
        @if($next)
          <div class="class-card mb-0 h-100">
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
          </div>
        @else
          <div class="class-card mb-0 h-100 align-items-center justify-content-center" style="padding:1.5rem;">
            <div class="text-center w-100">
              <div class="text-secondary small mb-2">{{ __t('ยังไม่มีคลาสที่จองไว้', 'No upcoming classes') }}</div>
              <button class="btn btn-book btn-sm js-goto-schedule" type="button">{{ __t('จองคลาสเลย', 'Book a class') }}</button>
            </div>
          </div>
        @endif
      </div>
    </div>

    <div class="section-title mt-4">{{ __t('บริการด่วน', 'Quick Actions') }}</div>
    <div class="row row-cols-2 row-cols-md-4 g-3 mb-2">
      <div class="col"><button class="quick-item w-100 border-0 js-goto-schedule" type="button"><div class="qi-icon"><i class="bi bi-plus-lg"></i></div><span>{{ __t('จองคลาส', 'Book Class') }}</span></button></div>
      <div class="col"><button class="quick-item w-100 border-0 js-goto-bookings" type="button"><div class="qi-icon"><i class="bi bi-arrow-repeat"></i></div><span>{{ __t('การจองของฉัน', 'My Bookings') }}</span></button></div>
      <div class="col"><button class="quick-item w-100 border-0 js-goto-profile" type="button"><div class="qi-icon"><i class="bi bi-ticket-perforated"></i></div><span>{{ __t('ซื้อแพ็กเกจ', 'Buy Package') }}</span></button></div>
      <div class="col">
        @php $branch = $branches->firstWhere('id', $currentBranchId); @endphp
        <a href="{{ $branch?->google_map_url ?: '#' }}" @if($branch?->google_map_url) target="_blank" @endif class="quick-item"><div class="qi-icon"><i class="bi bi-geo-alt"></i></div><span>{{ __t('แผนที่สาขา', 'Find Us') }}</span></a>
      </div>
    </div>

    <div class="section-title mt-4">{{ __t('ครูผู้สอน', 'Instructors') }}</div>
    <div class="row row-cols-4 row-cols-md-6 g-3">
      @foreach($trainers as $t)
        <div class="col text-center">
          <div class="avatar-round mx-auto mb-2">
            @if($t->avatar)<img src="{{ asset($t->avatar) }}" alt="{{ $t->name }}">@else<i class="bi bi-person"></i>@endif
          </div>
          <div class="instructor-name">{{ $t->nickname ?: $t->name }}</div>
        </div>
      @endforeach
    </div>
  </div>

  <!-- SCHEDULE -->
  <div class="tab-pane-view" id="pane-schedule">
    <div class="page-header">
      <h1 data-th="ตารางคลาส" data-en="Class Schedule">ตารางคลาส</h1>
      <p data-th="เลือกสาขาและวันเพื่อดูคลาสที่เปิดจอง" data-en="Pick a branch and day to view open classes">เลือกสาขาและวันเพื่อดูคลาสที่เปิดจอง</p>
    </div>

    <div class="branch-tabs" id="branchTabs"></div>

    <div class="branch-bar">
      <div class="bbar-pin"><i class="bi bi-geo-alt-fill"></i></div>
      <div class="bbar-copy">
        <div class="bbar-label" data-th="สาขาที่เลือก" data-en="Selected Branch">สาขาที่เลือก</div>
        <div class="bbar-name" id="branchBarName">สาขาสุขุมวิท</div>
        <div class="bbar-addr" id="branchBarAddr">สุขุมวิท 24 · โทร 02-111-2233</div>
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

  </div>

  <!-- BOOKINGS -->
  <div class="tab-pane-view" id="pane-bookings">
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
                <button class="btn btn-book btn-sm js-goto-schedule" type="button">{{ __t('ดูตารางคลาส', 'View schedule') }}</button>
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
  </div>

  <!-- PROFILE -->
  <div class="tab-pane-view" id="pane-profile">
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
          <h3 class="mb-1" style="font-size:1.25rem;">{{ $customer->full_name }}</h3>
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
              <div style="font-family:Georgia,serif;font-size:1.5rem;color:var(--accent-deep);">
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
  </div>

</div>

<div class="app-toast" id="appToast" role="status" aria-live="polite"></div>

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

var currentLang = @json(app()->getLocale());
var IS_LOGGED_IN = @json((bool) $customer);
var CSRF = document.querySelector('meta[name="csrf-token"]').content;
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
    // ถ้าผู้ใช้กดเปลี่ยนวันเร็วๆ ให้ใช้ผลของคำขอล่าสุดเท่านั้น
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
function showToast(message, isError){
  var el = document.getElementById('appToast');
  el.textContent = message;
  el.className = 'app-toast show' + (isError ? ' error' : '');
  clearTimeout(el._timer);
  el._timer = setTimeout(function(){ el.className = 'app-toast'; }, 3600);
}

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
    if (res.ok) { loadSessions(); refreshCredits(); }
  })
  .catch(function(){
    btn.disabled = false;
    showToast(currentLang === 'th' ? 'เกิดข้อผิดพลาด กรุณาลองใหม่' : 'Something went wrong', true);
  });
}

function doCancel(btn){
  var id = btn.dataset.booking;

  // เช็คก่อนว่ายกเลิกตอนนี้จะเสียเครดิตไหม แล้วค่อยถามยืนยัน
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
      if (res.ok) { loadSessions(); refreshCredits(); }
    })
    .catch(function(){
      btn.disabled = false;
      showToast(currentLang === 'th' ? 'เกิดข้อผิดพลาด' : 'Something went wrong', true);
    });
  });
}

// อัปเดตยอดเครดิตบนหน้าแรกหลังจอง/ยกเลิก
function refreshCredits(){
  if (!IS_LOGGED_IN) return;
  setTimeout(function(){ window.location.reload(); }, 900);
}

/* ---------- ปุ่มลัดไปแท็บต่างๆ ---------- */
function gotoPane(paneId){
  var btn = document.querySelector('.nav-link[data-pane="' + paneId + '"]');
  if (btn) btn.click();
}

document.addEventListener('click', function(e){
  if (e.target.closest('.js-goto-schedule')) { e.preventDefault(); gotoPane('pane-schedule'); }
  else if (e.target.closest('.js-goto-bookings')) { e.preventDefault(); gotoPane('pane-bookings'); }
  else if (e.target.closest('.js-goto-profile')) { e.preventDefault(); gotoPane('pane-profile'); }
});

/* ---------- ยกเลิกจากหน้าการจอง ---------- */
document.addEventListener('click', function(e){
  var link = e.target.closest('.js-cancel-booking');
  if (!link) return;
  e.preventDefault();
  doCancel(link);
});

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

document.getElementById('themeToggle').addEventListener('click', function(){
  var html = document.documentElement;
  html.setAttribute('data-bs-theme', html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
});

document.getElementById('langToggle').addEventListener('click', function(){
  // เนื้อหาหลักเรนเดอร์จากเซิร์ฟเวอร์ ต้องโหลดหน้าใหม่เพื่อให้ได้ทุกส่วนครบ
  var next = currentLang === 'th' ? 'en' : 'th';
  window.location.href = '/locale/' + next;
});

var INSTALL_PROMPT_DISABLED = true; /* ปิดแบนเนอร์ชวนติดตั้งแอปไว้ก่อน ตั้ง false เพื่อเปิดใช้อีกครั้ง */
var deferredPrompt;
var installBanner = document.getElementById('installBanner');
var installBtn = document.getElementById('installBtn');
var openInChromeBtn = document.getElementById('openInChromeBtn');
var dismissInstallBtn = document.getElementById('dismissInstallBtn');
var installMessage = document.getElementById('installMessage');
var installInstructions = document.getElementById('installInstructions');

function isIos(){
  return /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
}

function isAndroid(){
  return /android/.test(window.navigator.userAgent.toLowerCase());
}

function isLineBrowser(){
  return /line\//.test(window.navigator.userAgent.toLowerCase());
}

function isInStandaloneMode(){
  return (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true);
}

/* แสดงคำเชิญติดตั้งครั้งเดียวพอ ถ้าผู้ใช้ไม่ติดตั้งก็ไม่ถามซ้ำอีก */
var INSTALL_PROMPT_KEY = 'dripInstallPromptSeen';

function installPromptSeen(){
  try { return localStorage.getItem(INSTALL_PROMPT_KEY) === '1'; }
  catch(e){ return false; }
}

function markInstallPromptSeen(){
  try { localStorage.setItem(INSTALL_PROMPT_KEY, '1'); } catch(e){ /* storage unavailable */ }
}

/* ข้อความของแบนเนอร์แยกตามแพลตฟอร์ม: message บอกว่าได้อะไร, steps บอกว่าต้องทำอะไร ไม่พูดซ้ำกัน */
var INSTALL_COPY = {
  ios: {
    messageTh: 'เพิ่ม Drip Pilates ไว้บนหน้าจอโฮม เปิดจองคลาสได้เร็วขึ้น',
    messageEn: 'Add Drip Pilates to your Home Screen for faster booking',
    stepsTh: 'แตะปุ่มแชร์ด้านล่าง แล้วเลือก “เพิ่มไปยังหน้าจอโฮม”',
    stepsEn: 'Tap the Share button below, then choose “Add to Home Screen”'
  },
  android: {
    messageTh: 'เพิ่ม Drip Pilates ไว้บนหน้าจอโฮม เปิดจองคลาสได้เร็วขึ้น',
    messageEn: 'Add Drip Pilates to your Home Screen for faster booking',
    stepsTh: '',
    stepsEn: ''
  },
  'android-fallback': {
    messageTh: 'เพิ่ม Drip Pilates ไว้บนหน้าจอโฮม เปิดจองคลาสได้เร็วขึ้น',
    messageEn: 'Add Drip Pilates to your Home Screen for faster booking',
    stepsTh: 'แตะปุ่มเมนู ⋮ ของ Chrome แล้วเลือก “ติดตั้งแอป”',
    stepsEn: 'Tap Chrome’s ⋮ menu, then choose “Install app”'
  },
  linebrowser: {
    messageTh: 'ติดตั้งจากใน LINE ไม่ได้ ต้องเปิดหน้านี้ใน Chrome ก่อน',
    messageEn: 'Installing from LINE isn’t supported — open this page in Chrome first',
    stepsTh: 'แตะ “เปิดใน Chrome” แล้วเลือก “ติดตั้งแอป” จากเมนู ⋮',
    stepsEn: 'Tap “Open in Chrome”, then choose “Install app” from the ⋮ menu'
  }
};

var installPlatform = null;

function renderInstallCopy(){
  if(!installPlatform) return;
  var copy = INSTALL_COPY[installPlatform];
  if(!copy) return;
  var suffix = currentLang === 'th' ? 'Th' : 'En';
  installMessage.textContent = copy['message' + suffix];
  var steps = copy['steps' + suffix];
  installInstructions.textContent = steps;
  installInstructions.classList.toggle('hidden', !steps);
}

function showInstallBanner(platform){
  if(isInStandaloneMode()) return;
  if(installPromptSeen()) return;
  if(!INSTALL_COPY[platform]) return;
  markInstallPromptSeen();

  installPlatform = platform;
  installBanner.classList.remove('hidden');
  /* มีปุ่มติดตั้งจริงเฉพาะตอน Chrome ให้ prompt มาเท่านั้น นอกนั้นบอกเป็นขั้นตอนแทน */
  installBtn.classList.toggle('hidden', platform !== 'android');
  openInChromeBtn.classList.toggle('hidden', platform !== 'linebrowser');
  renderInstallCopy();
}

function openInChrome(){
  var url = window.location.href;
  var chromeIntent = 'intent://' + window.location.host + window.location.pathname + window.location.search + '#Intent;scheme=https;package=com.android.chrome;end';
  window.location.href = chromeIntent;
  setTimeout(function(){
    window.location.href = url;
  }, 1200);
}

window.addEventListener('beforeinstallprompt', function(e){
  e.preventDefault();
  if(INSTALL_PROMPT_DISABLED) return;
  deferredPrompt = e;
  /* ถ้าแบนเนอร์สำรองเปิดค้างอยู่ ให้เปลี่ยนเป็นปุ่มติดตั้งจริงแทน */
  if(!installBanner.classList.contains('hidden')){
    installPlatform = 'android';
    installBtn.classList.remove('hidden');
    openInChromeBtn.classList.add('hidden');
    renderInstallCopy();
    return;
  }
  showInstallBanner('android');
});

window.addEventListener('load', function(){
  if(INSTALL_PROMPT_DISABLED) return;
  if(isInStandaloneMode()) return;
  if(isLineBrowser()){
    showInstallBanner('linebrowser');
    return;
  }
  if(isIos()){
    showInstallBanner('ios');
    return;
  }
  if(isAndroid()){
    setTimeout(function(){
      if(!deferredPrompt){
        showInstallBanner('android-fallback');
      }
    }, 1200);
  }
});

installBtn && installBtn.addEventListener('click', function(){
  if(!deferredPrompt) return;
  deferredPrompt.prompt();
  deferredPrompt.userChoice.then(function(choiceResult){
    installBanner.classList.add('hidden');
    deferredPrompt = null;
  });
});

openInChromeBtn && openInChromeBtn.addEventListener('click', function(){
  openInChrome();
});

dismissInstallBtn && dismissInstallBtn.addEventListener('click', function(){
  installBanner.classList.add('hidden');
});

window.addEventListener('appinstalled', function(){
  installBanner.classList.add('hidden');
});

if('serviceWorker' in navigator){
  window.addEventListener('load', function(){
    navigator.serviceWorker.register('/sw.js').catch(function(err){
      console.warn('SW registration failed:', err);
    });
  });
}
</script>
</body>
</html>

<!doctype html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Drip Pilates') · Drip Pilates</title>
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#7C6BF0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --ground:#F1EEFB;
    --panel:#FFFFFF;
    --ink:#241F3A;
    --ink-soft:#6E6A86;
    --accent:#7C6BF0;
    --accent-deep:#5B47D6;
    --accent-soft:#E7E2FC;
    --sage:#8C9DBE;
    --sage-soft:#E4E9F2;
    --line:#E9E5F5;

    /* พาสเทลหลากสี — ใช้กับการ์ด hero / stat tile / หมวดต่างๆ */
    --c-purple:#8B7BF2;      --c-purple-soft:#E9E4FD;   --c-purple-ink:#4A38C4;
    --c-orange:#FF9F5A;      --c-orange-soft:#FFEBD9;   --c-orange-ink:#B85E1C;
    --c-blue:#5AB4F0;        --c-blue-soft:#DCF0FE;     --c-blue-ink:#1C6FAD;
    --c-green:#5CC98B;       --c-green-soft:#DBF4E6;    --c-green-ink:#1F7D4C;
    --c-pink:#F27BB0;        --c-pink-soft:#FDE2EF;     --c-pink-ink:#B83A76;
    --c-amber:#F3C34B;       --c-amber-soft:#FBEFCC;    --c-amber-ink:#8A6112;
  }
  [data-bs-theme="dark"]{
    --ground:#15121F;
    --panel:#211C31;
    --ink:#EEEAF7;
    --ink-soft:#A59FBE;
    --accent:#9E8FF5;
    --accent-deep:#B7ABF8;
    --accent-soft:#2E2748;
    --sage:#8FA3C4;
    --sage-soft:#2A3243;
    --line:#332C48;

    --c-purple:#9E8FF5;      --c-purple-soft:#2E2748;   --c-purple-ink:#C7BDFA;
    --c-orange:#FF9F5A;      --c-orange-soft:#3A2A1B;   --c-orange-ink:#FFC59A;
    --c-blue:#5AB4F0;        --c-blue-soft:#152F42;     --c-blue-ink:#9DD6F8;
    --c-green:#5CC98B;       --c-green-soft:#16321F;    --c-green-ink:#9AE3B9;
    --c-pink:#F27BB0;        --c-pink-soft:#3A1F2E;     --c-pink-ink:#F8B4D3;
    --c-amber:#F3C34B;       --c-amber-soft:#332A16;    --c-amber-ink:#F5D98A;
  }
  body{
    background:var(--ground);
    color:var(--ink);
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
  }

  .app-topbar{
    position:fixed; top:0; left:0; right:0; z-index:20;
    background:var(--panel); border-bottom:1px solid var(--line);
    padding:.75rem 0;
  }
  body{ padding-top:62px; overflow-x:hidden; }

  .min-width-0{ min-width:0; }
  .class-info{ min-width:0; }
  .class-info h4{ overflow-wrap:anywhere; }
  .class-info .meta{ flex-wrap:wrap; }
  .app-topbar .brandmark{
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-weight:700; font-size:1.15rem; color:var(--ink);
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
    border-radius:12px; margin:0 .2rem; text-decoration:none;
  }
  .footer-nav .nav-link .ic{ font-size:1.15rem; line-height:1; }
  .footer-nav .nav-link.active{ color:var(--accent-deep); background:var(--accent-soft); font-weight:700; }
  .footer-nav .nav-link.active .ic{ transform:translateY(-1px); }

  .page-header{ padding:1.75rem 0 1.25rem; }
  .page-header h1{ font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-weight:700; font-size:1.9rem; margin:.2rem 0 0; letter-spacing:-.01em; }
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
    background:var(--panel); border:1px solid var(--line); border-radius:22px; padding:1.25rem;
  }

  .credit-card{
    background:linear-gradient(140deg,#7C6BF0,#9E8FF5 60%,#B58BF2);
    border-radius:24px; padding:1.5rem; color:#FBF3F0;
    display:flex; justify-content:space-between; align-items:flex-end; height:100%;
    position:relative; overflow:hidden; box-shadow:0 12px 28px rgba(124,107,240,.28);
  }
  .credit-card::before{
    content:''; position:absolute; top:-40px; right:-30px;
    width:150px; height:150px; border-radius:50%;
    background:rgba(255,255,255,.14); pointer-events:none;
  }
  .credit-card::after{
    content:''; position:absolute; bottom:-55px; right:40px;
    width:110px; height:110px; border-radius:50%;
    background:rgba(255,255,255,.10); pointer-events:none;
  }
  .credit-card > *{ position:relative; z-index:1; }
  .credit-card .cc-count{ font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-size:2.6rem; line-height:1; font-variant-numeric:tabular-nums; }
  .cc-btn{
    background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.4);
    color:#FBF3F0; border-radius:999px; font-size:.78rem; font-weight:600;
    padding:.45rem 1rem; white-space:nowrap;
  }
  .cc-btn:hover{ background:rgba(255,255,255,.28); color:#fff; }

  .quick-item{
    background:var(--panel); border:1px solid var(--line); border-radius:20px;
    padding:1.1rem .5rem; text-align:center; font-size:.78rem; color:var(--ink-soft); font-weight:600;
    text-decoration:none; display:block; height:100%; transition:box-shadow .18s, transform .18s;
  }
  .quick-item:hover{ transform:translateY(-3px); color:var(--ink-soft); box-shadow:0 10px 22px rgba(90,71,214,.12); }
  .quick-item .qi-icon{
    width:46px; height:46px; border-radius:16px; background:var(--accent-soft);
    color:var(--accent-deep); display:flex; align-items:center; justify-content:center;
    margin:0 auto .55rem; font-size:1.2rem;
  }
  /* ไอคอนบริการด่วน ไล่สีทีละใบให้ดูมีชีวิตชีวา */
  .quick-grid > *:nth-child(4n+1) .qi-icon{ background:var(--c-purple-soft); color:var(--c-purple-ink); }
  .quick-grid > *:nth-child(4n+2) .qi-icon{ background:var(--c-orange-soft); color:var(--c-orange-ink); }
  .quick-grid > *:nth-child(4n+3) .qi-icon{ background:var(--c-green-soft);  color:var(--c-green-ink); }
  .quick-grid > *:nth-child(4n+4) .qi-icon{ background:var(--c-blue-soft);   color:var(--c-blue-ink); }

  .avatar-round{
    width:64px; height:64px; border-radius:50%; background:var(--sage-soft); color:var(--sage);
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-weight:600; display:flex; align-items:center; justify-content:center;
    border:2px solid var(--panel); box-shadow:0 1px 3px rgba(0,0,0,.08);
    overflow:hidden;
  }
  .avatar-round img{ width:100%; height:100%; object-fit:cover; }
  .instructor-name{ font-size:.78rem; color:var(--ink-soft); font-weight:600; }

  .day-pill{
    flex:0 0 auto; width:52px; padding:.6rem 0; border-radius:18px; text-align:center;
    border:1px solid var(--line); background:var(--panel); cursor:pointer; transition:transform .15s;
  }
  .day-pill:hover:not(.is-past){ transform:translateY(-2px); }
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
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-weight:700; font-size:1rem; color:var(--ink);
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
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-weight:700; font-size:.92rem; color:var(--ink);
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
    display:flex; border:1px solid var(--line); border-radius:20px; overflow:hidden;
    background:var(--panel); margin-bottom:.75rem; transition:box-shadow .18s, transform .18s;
  }
  a.class-card{ color:inherit; }
  a.class-card:hover{ color:inherit; transform:translateY(-2px); box-shadow:0 10px 22px rgba(90,71,214,.12); }
  .class-time-rail{
    width:76px; flex:none;
    background:linear-gradient(160deg,var(--c-purple-soft),var(--accent-soft)); color:var(--accent-deep);
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

  .stat-box{ border:1px solid var(--line); border-radius:20px; padding:1.1rem 1rem; text-align:center; background:var(--panel); }
  .stat-box .stat-icon{
    width:40px; height:40px; border-radius:14px; margin:0 auto .5rem;
    display:flex; align-items:center; justify-content:center; font-size:1.05rem;
  }
  .stat-box .num{ font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-size:1.6rem; color:var(--ink); font-variant-numeric:tabular-nums; }
  .stat-box .lbl{ font-size:.68rem; color:var(--ink-soft); text-transform:uppercase; letter-spacing:.05em; margin-top:4px; }

  .profile-hero{
    background:linear-gradient(140deg,#7C6BF0,#9E8FF5 55%,#F27BB0);
    border-radius:26px; padding:1.5rem; color:#FBF3F0; position:relative; overflow:hidden;
    box-shadow:0 14px 30px rgba(124,107,240,.26);
  }
  .profile-hero::before{
    content:''; position:absolute; top:-50px; right:-30px;
    width:170px; height:170px; border-radius:50%;
    background:rgba(255,255,255,.13); pointer-events:none;
  }
  .profile-hero::after{
    content:''; position:absolute; bottom:-60px; left:-20px;
    width:130px; height:130px; border-radius:50%;
    background:rgba(255,255,255,.10); pointer-events:none;
  }
  .profile-hero > *{ position:relative; z-index:1; }
  .profile-hero .avatar-lg{
    width:72px; height:72px; border-radius:50%; background:rgba(255,255,255,.18); color:#FFF;
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-size:1.5rem; font-weight:600;
    display:flex; align-items:center; justify-content:center;
    border:2px solid rgba(255,255,255,.4); overflow:hidden; flex:0 0 auto;
  }
  .profile-hero .avatar-lg img{ width:100%; height:100%; object-fit:cover; }
  .profile-hero h3{ font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; }
  .member-badge{
    background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.4);
    color:#FBF3F0; border-radius:999px; font-size:.68rem; font-weight:700;
    padding:.25rem .7rem; display:inline-flex; align-items:center; gap:.3rem;
    letter-spacing:.03em; text-transform:uppercase;
  }

  .menu-list{ border:1px solid var(--line); border-radius:20px; overflow:hidden; background:var(--panel); }
  .menu-row{
    display:flex; align-items:center; gap:.85rem; padding:.9rem 1.1rem;
    border-bottom:1px solid var(--line); font-size:.88rem; color:var(--ink); text-decoration:none;
  }
  .menu-row:hover{ background:var(--ground); }
  .menu-row:last-child{ border-bottom:none; }
  .menu-row .mi{
    width:36px; height:36px; border-radius:12px; background:var(--accent-soft); color:var(--accent-deep);
    display:flex; align-items:center; justify-content:center; font-size:.95rem;
  }
  .menu-row .chev{ margin-left:auto; color:var(--ink-soft); }

  .avatar-lg{
    width:68px; height:68px; border-radius:50%; background:var(--accent-soft); color:var(--accent-deep);
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-size:1.4rem; font-weight:600;
    display:flex; align-items:center; justify-content:center;
  }

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
    background:var(--panel); border:1px solid var(--line); border-radius:20px;
    padding:.8rem 1rem; margin-bottom:1rem;
  }
  .branch-bar .bbar-pin{
    width:42px; height:42px; border-radius:14px; background:var(--c-blue-soft); color:var(--c-blue-ink);
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
    flex:1; background:var(--panel); border:1px solid var(--line); border-radius:16px;
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
    background:#7C6BF0;
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
    background:#7C6BF0;
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

  .form-label{ font-size:.8rem; font-weight:600; color:var(--ink-soft); margin-bottom:.3rem; }
  .form-control, .form-select{
    background:var(--panel); border-color:var(--line); color:var(--ink);
    font-size:.9rem; padding:.6rem .8rem;
  }
  .form-control:focus, .form-select:focus{
    background:var(--panel); color:var(--ink);
    border-color:var(--accent); box-shadow:0 0 0 .2rem rgba(124,107,240,.18);
  }
  .btn-accent{
    background:var(--accent); border:1px solid var(--accent); color:#FBF3F0;
    font-weight:600; padding:.6rem; border-radius:.5rem;
  }
  .btn-accent:hover{ background:var(--accent-deep); border-color:var(--accent-deep); color:#fff; }

  .btn-line{
    background:#06C755; border:1px solid #06C755; color:#fff;
    font-weight:600; padding:.6rem; border-radius:.5rem;
    display:flex; align-items:center; justify-content:center; gap:.5rem;
  }
  .btn-line:hover{ background:#05B34C; border-color:#05B34C; color:#fff; }
  .btn-line svg{ width:20px; height:20px; fill:currentColor; }
  .or-divider{
    display:flex; align-items:center; gap:.75rem;
    color:var(--ink-soft); font-size:.78rem; margin:1.1rem 0;
  }
  .or-divider::before, .or-divider::after{
    content:''; flex:1; height:1px; background:var(--line);
  }
  .otp-input{
    letter-spacing:.5em; text-align:center; font-size:1.3rem;
    font-weight:600; padding:.7rem .5rem;
  }

  .notif-bell{
    position:relative; background:var(--ground); border:1px solid var(--line);
    border-radius:999px; width:34px; height:34px; display:flex; align-items:center;
    justify-content:center; color:var(--ink-soft); text-decoration:none; flex:0 0 auto;
  }
  .notif-bell:hover{ border-color:var(--accent); color:var(--accent); }
  .notif-badge{
    position:absolute; top:-4px; right:-4px; min-width:16px; height:16px; padding:0 4px;
    border-radius:999px; background:#9B3232; color:#fff; font-size:.62rem; font-weight:700;
    line-height:16px; text-align:center; border:2px solid var(--panel);
  }
  @yield('extra-style')
</style>
</head>
<body>

<nav class="app-topbar">
  <div class="container-lg d-flex align-items-center justify-content-between">
    <a href="{{ route('home') }}" class="brand-lockup text-decoration-none">
      <img src="{{ asset('images/logo.jpg') }}" alt="Drip Pilates" class="brand-logo">
      <span class="brandmark">Drip Pilates</span>
    </a>
    <div class="d-flex align-items-center gap-2">
      @hasSection('branch-switcher')
        @yield('branch-switcher')
      @endif
      @auth('customer')
        <a href="{{ route('customer.notifications.index') }}" class="notif-bell" id="notifBell" title="{{ __t('การแจ้งเตือน', 'Notifications') }}">
          <i class="bi bi-bell"></i>
          <span class="notif-badge" id="notifBadge" hidden>0</span>
        </a>
      @endauth
      <button class="theme-btn" id="themeToggle" type="button"><i class="bi bi-circle-half"></i> <span>{{ __t('โหมดมืด/สว่าง', 'Dark/Light') }}</span></button>
      <button class="lang-btn" id="langToggle" type="button" title="Change language"><img id="langFlag" src="{{ asset('images/' . app()->getLocale() . '.png') }}" alt="{{ strtoupper(app()->getLocale()) }}"></button>
    </div>
  </div>
</nav>

<div class="container-lg pb-5">
  <div id="installBanner" class="install-banner hidden" role="dialog" aria-live="polite">
    <div class="install-icon"><i class="bi bi-phone"></i></div>
    <div class="install-copy">
      <strong id="installTitle">{{ __t('ติดตั้ง Drip Pilates', 'Install Drip Pilates') }}</strong>
      <div id="installMessage">เพิ่มลงหน้าจอโฮม เปิดใช้งานได้เร็วเหมือนแอป</div>
      <div id="installInstructions" class="install-instructions hidden"></div>
      <div class="install-actions">
        <button id="installBtn" class="btn-install" type="button">{{ __t('ติดตั้ง', 'Install') }}</button>
        <button id="openInChromeBtn" class="btn-openchrome hidden" type="button">{{ __t('เปิดใน Chrome', 'Open in Chrome') }}</button>
        <button id="dismissInstallBtn" class="btn-dismiss" type="button">{{ __t('ปิด', 'Dismiss') }}</button>
      </div>
    </div>
  </div>

  @yield('content')
</div>

<div class="app-toast" id="appToast" role="status" aria-live="polite"></div>

<nav class="footer-nav">
  <div class="nav-inner">
    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
      <span class="ic"><i class="bi bi-house"></i></span><span>{{ __t('หน้าแรก', 'Home') }}</span>
    </a>
    <a class="nav-link {{ request()->routeIs('customer.schedule') ? 'active' : '' }}" href="{{ route('customer.schedule') }}">
      <span class="ic"><i class="bi bi-grid-3x3-gap"></i></span><span>{{ __t('ตารางคลาส', 'Schedule') }}</span>
    </a>
    <a class="nav-link {{ request()->routeIs('customer.bookings') ? 'active' : '' }}" href="{{ route('customer.bookings') }}">
      <span class="ic"><i class="bi bi-check-circle"></i></span><span>{{ __t('การจอง', 'Bookings') }}</span>
    </a>
    <a class="nav-link {{ request()->routeIs('customer.profile.*') ? 'active' : '' }}" href="{{ route('customer.profile.index') }}">
      <span class="ic"><i class="bi bi-person-circle"></i></span><span>{{ __t('โปรไฟล์', 'Profile') }}</span>
    </a>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
var currentLang = @json(app()->getLocale());
var IS_LOGGED_IN = @json((bool) auth('customer')->user());
var CSRF = document.querySelector('meta[name="csrf-token"]').content;

function showToast(message, isError){
  var el = document.getElementById('appToast');
  el.textContent = message;
  el.className = 'app-toast show' + (isError ? ' error' : '');
  clearTimeout(el._timer);
  el._timer = setTimeout(function(){ el.className = 'app-toast'; }, 3600);
}

document.getElementById('themeToggle').addEventListener('click', function(){
  var html = document.documentElement;
  html.setAttribute('data-bs-theme', html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
});

document.getElementById('langToggle').addEventListener('click', function(){
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
  return /\bline\//i.test(window.navigator.userAgent);
}

function isInStandaloneMode(){
  return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

var INSTALL_PROMPT_KEY = 'dripInstallPromptSeen';

function installPromptSeen(){
  try { return localStorage.getItem(INSTALL_PROMPT_KEY) === '1'; }
  catch(e){ return false; }
}

function markInstallPromptSeen(){
  try { localStorage.setItem(INSTALL_PROMPT_KEY, '1'); } catch(e){ /* storage unavailable */ }
}

var INSTALL_COPY = {
  android: {
    messageTh: 'เพิ่มลงหน้าจอโฮม เปิดใช้งานได้เร็วเหมือนแอป',
    messageEn: 'Add to your home screen for quick access',
    stepsTh: '', stepsEn: ''
  },
  'android-fallback': {
    messageTh: 'แตะเมนู ⋮ ของ Chrome แล้วเลือก “ติดตั้งแอป”',
    messageEn: 'Tap Chrome’s ⋮ menu, then choose “Install app”',
    stepsTh: 'แตะเมนู ⋮ ของ Chrome แล้วเลือก “ติดตั้งแอป”',
    stepsEn: 'Tap Chrome’s ⋮ menu, then choose “Install app”'
  },
  linebrowser: {
    messageTh: 'ติดตั้งจาก LINE ไม่ได้ กรุณาเปิดหน้านี้ใน Chrome ก่อน',
    messageEn: 'Installing from LINE isn’t supported — open this page in Chrome first',
    stepsTh: 'แตะ “เปิดใน Chrome” แล้วเลือก “ติดตั้งแอป” จากเมนู ⋮',
    stepsEn: 'Tap “Open in Chrome”, then choose “Install app” from the ⋮ menu'
  },
  ios: {
    messageTh: 'แตะปุ่มแชร์ แล้วเลือก “เพิ่มไปยังหน้าจอโฮม”',
    messageEn: 'Tap the Share button, then “Add to Home Screen”',
    stepsTh: '', stepsEn: ''
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
  if(INSTALL_PROMPT_DISABLED) return;
  if(installPromptSeen()) return;
  if(!INSTALL_COPY[platform]) return;
  markInstallPromptSeen();

  installPlatform = platform;
  installBanner.classList.remove('hidden');
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

openInChromeBtn && openInChromeBtn.addEventListener('click', openInChrome);

dismissInstallBtn && dismissInstallBtn.addEventListener('click', function(){
  installBanner.classList.add('hidden');
});

window.addEventListener('appinstalled', function(){
  installBanner.classList.add('hidden');
});

if('serviceWorker' in navigator){
  window.addEventListener('load', function(){
    navigator.serviceWorker.register('/sw.js').catch(function(err){
      console.warn('SW registration failed', err);
    });
  });
}

// อัปเดตตัวเลขแจ้งเตือนที่ยังไม่อ่านบนกระดิ่ง
if(IS_LOGGED_IN){
  (function(){
    var badge = document.getElementById('notifBadge');
    if(!badge) return;

    function refreshNotifCount(){
      fetch('{{ route('customer.notifications.count') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(function(r){ return r.ok ? r.json() : null; })
      .then(function(data){
        if(!data) return;
        var n = data.count || 0;
        if(n > 0){
          badge.textContent = n > 99 ? '99+' : n;
          badge.hidden = false;
        } else {
          badge.hidden = true;
        }
      })
      .catch(function(){ /* เงียบไว้ ไม่ต้องรบกวนผู้ใช้ */ });
    }

    refreshNotifCount();
    // เช็คซ้ำเป็นระยะ เผื่อได้คิว waitlist ระหว่างเปิดหน้าค้างไว้
    setInterval(refreshNotifCount, 60000);
  })();
}

@yield('extra-script')
</script>
</body>
</html>

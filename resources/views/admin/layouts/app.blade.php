<!doctype html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'จัดการระบบ') · Drip Pilates</title>
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=noto-sans-thai:400,500,600,700|fraunces:600,700" rel="stylesheet">
<style>
  :root{
    /* พื้นผิวและตัวอักษร */
    --ground:#F4F6FA; --ground-2:#EBEFF6;
    --panel:#FFFFFF; --panel-2:#FAFBFD;
    --ink:#232936; --ink-soft:#69748C; --ink-faint:#96A0B5;

    /* สีหลัก */
    --accent:#6C88B4; --accent-deep:#4E6A96; --accent-soft:#E5EBF6; --accent-ring:108,136,180;
    --sage:#7E97A8; --sage-soft:#E4EDF1;

    /* สีสถานะ */
    --ok:#3D7A4A; --ok-soft:#DFF0E2;
    --warn:#916014; --warn-soft:#F8EAD0;
    --danger:#A8383C; --danger-soft:#F9DEDF;

    --line:#E3E8F0; --line-soft:#EDF1F7;

    --shadow-sm:0 1px 2px rgba(28,38,60,.05), 0 1px 3px rgba(28,38,60,.04);
    --shadow-md:0 2px 6px rgba(28,38,60,.05), 0 8px 20px -8px rgba(28,38,60,.12);
    --shadow-lg:0 12px 40px -12px rgba(28,38,60,.22);

    --r-sm:9px; --r-md:12px; --r-lg:16px; --r-xl:20px;
    --sidebar-w:246px;
    --font-display:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
  }
  [data-bs-theme="dark"]{
    --ground:#14171F; --ground-2:#1B1F29;
    --panel:#1E222C; --panel-2:#232833;
    --ink:#E8ECF4; --ink-soft:#9BA6BC; --ink-faint:#727E96;

    --accent:#8FA8CE; --accent-deep:#B2C4E0; --accent-soft:#2A3446; --accent-ring:143,168,206;
    --sage:#8FA8B8; --sage-soft:#26313A;

    --ok:#84C795; --ok-soft:#22362A;
    --warn:#DFB56A; --warn-soft:#3A2F1B;
    --danger:#E58F92; --danger-soft:#3B2326;

    --line:#2E3542; --line-soft:#272D38;

    --shadow-sm:0 1px 2px rgba(0,0,0,.3);
    --shadow-md:0 2px 6px rgba(0,0,0,.25), 0 10px 24px -10px rgba(0,0,0,.5);
    --shadow-lg:0 16px 48px -14px rgba(0,0,0,.6);
  }

  *{ scrollbar-width:thin; scrollbar-color:var(--line) transparent; }
  *::-webkit-scrollbar{ width:9px; height:9px; }
  *::-webkit-scrollbar-thumb{ background:var(--line); border-radius:99px; border:2px solid transparent; background-clip:content-box; }
  *::-webkit-scrollbar-thumb:hover{ background:var(--ink-faint); background-clip:content-box; }

  body{
    background:var(--ground); color:var(--ink);
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,sans-serif;
    font-size:.9rem; line-height:1.6;
    -webkit-font-smoothing:antialiased;
  }
  h1,h2,h3,h4,h5,h6{ letter-spacing:-.01em; }
  a{ transition:color .15s ease; }
  ::selection{ background:var(--accent-soft); color:var(--accent-deep); }

  /* ── แถบเมนูด้านข้าง ───────────────────────────── */
  .admin-sidebar{
    position:fixed; top:0; left:0; bottom:0; width:var(--sidebar-w); z-index:30;
    background:var(--panel); border-right:1px solid var(--line);
    display:flex; flex-direction:column; overflow-y:auto; overscroll-behavior:contain;
  }
  .admin-brand{
    padding:1.05rem 1.15rem; display:flex; align-items:center; gap:.7rem;
    position:sticky; top:0; z-index:2;
    background:var(--panel); border-bottom:1px solid var(--line-soft);
  }
  .admin-brand .bm{
    font-family:var(--font-display); font-weight:700; font-size:1.02rem;
    letter-spacing:-.015em; line-height:1.15;
  }
  .admin-brand .bs{ display:block; font-family:'Noto Sans Thai',sans-serif; font-size:.62rem; font-weight:600; color:var(--ink-faint); letter-spacing:.08em; text-transform:uppercase; }
  .admin-brand img{
    width:34px; height:34px; border-radius:11px; object-fit:cover;
    border:1px solid var(--line); box-shadow:var(--shadow-sm); flex:0 0 auto;
  }

  .admin-nav{ padding:.7rem .6rem 1.2rem; flex:1; }
  .admin-nav .grp{
    font-size:.6rem; letter-spacing:.13em; text-transform:uppercase;
    color:var(--ink-faint); font-weight:700; padding:1rem .65rem .4rem;
  }
  .admin-nav a{
    position:relative; display:flex; align-items:center; gap:.7rem;
    padding:.52rem .65rem; border-radius:var(--r-sm);
    color:var(--ink-soft); text-decoration:none;
    font-size:.845rem; font-weight:500; margin-bottom:2px;
    transition:background .15s ease, color .15s ease;
  }
  .admin-nav a i{ font-size:1.02rem; width:19px; text-align:center; flex:0 0 auto; opacity:.85; }
  .admin-nav a:hover{ background:var(--ground-2); color:var(--ink); }
  .admin-nav a.active{
    background:var(--accent-soft); color:var(--accent-deep); font-weight:600;
  }
  .admin-nav a.active i{ opacity:1; }
  .admin-nav a.active::before{
    content:''; position:absolute; left:-.6rem; top:50%; transform:translateY(-50%);
    width:3px; height:18px; border-radius:0 3px 3px 0; background:var(--accent);
  }
  .admin-nav .badge-count{
    margin-left:auto; background:var(--danger); color:#fff; border-radius:999px;
    font-size:.63rem; padding:.05rem .42rem; font-weight:700; line-height:1.5;
    font-variant-numeric:tabular-nums;
  }

  /* ── พื้นที่หลัก ───────────────────────────────── */
  .admin-main{ margin-left:var(--sidebar-w); min-height:100vh; display:flex; flex-direction:column; }
  .admin-topbar{
    background:color-mix(in srgb, var(--panel) 82%, transparent);
    backdrop-filter:saturate(180%) blur(12px);
    -webkit-backdrop-filter:saturate(180%) blur(12px);
    border-bottom:1px solid var(--line);
    padding:.8rem 1.5rem; display:flex; align-items:center; gap:.8rem;
    position:sticky; top:0; z-index:20;
  }
  .admin-topbar h1{
    font-family:var(--font-display); font-size:1.16rem; margin:0; font-weight:600;
    letter-spacing:-.015em;
  }
  .admin-content{ padding:1.5rem; flex:1; max-width:1560px; width:100%; }

  /* ── ปุ่มไอคอนกลม ─────────────────────────────── */
  .icon-btn{
    background:transparent; border:1px solid var(--line); color:var(--ink-soft);
    border-radius:999px; width:35px; height:35px; padding:0;
    display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto;
    transition:all .15s ease;
  }
  .icon-btn:hover{ background:var(--accent-soft); border-color:var(--accent-soft); color:var(--accent-deep); }
  .icon-btn:focus-visible{ outline:none; box-shadow:0 0 0 3px rgba(var(--accent-ring),.25); }

  /* ── การ์ด ────────────────────────────────────── */
  .card-panel{
    background:var(--panel); border:1px solid var(--line);
    border-radius:var(--r-lg); padding:1.25rem;
    box-shadow:var(--shadow-sm);
  }
  .card-panel .ttl{
    font-size:.67rem; letter-spacing:.11em; text-transform:uppercase;
    color:var(--ink-faint); font-weight:700; margin-bottom:.85rem;
    display:flex; align-items:center; gap:.5rem;
  }
  .card-panel .ttl > .btn, .card-panel .ttl > a{ margin-left:auto; }

  /* ── ไทล์สถิติ ────────────────────────────────── */
  .stat-tile{
    position:relative; overflow:hidden;
    background:var(--panel); border:1px solid var(--line);
    border-radius:var(--r-lg); padding:1.1rem;
    box-shadow:var(--shadow-sm);
    transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  }
  .stat-tile:hover{ transform:translateY(-2px); box-shadow:var(--shadow-md); border-color:var(--line); }
  .stat-tile .si{
    width:38px; height:38px; border-radius:11px; margin-bottom:.7rem;
    display:flex; align-items:center; justify-content:center; font-size:1rem;
    background:var(--accent-soft); color:var(--accent-deep);
  }
  .stat-tile .num{
    font-family:var(--font-display); font-size:1.72rem; line-height:1.1; font-weight:600;
    font-variant-numeric:tabular-nums; letter-spacing:-.02em;
  }
  .stat-tile .lbl{ font-size:.72rem; color:var(--ink-soft); margin-top:3px; line-height:1.45; }

  /* ── ตาราง ────────────────────────────────────── */
  .table{ --bs-table-bg:transparent; color:var(--ink); font-size:.85rem; margin-bottom:0; }
  .table > :not(caption) > * > *{
    border-color:var(--line-soft); padding:.68rem .7rem; background:transparent;
  }
  .table thead th{
    font-size:.65rem; letter-spacing:.08em; text-transform:uppercase;
    color:var(--ink-faint); font-weight:700; border-bottom:1px solid var(--line);
    white-space:nowrap; padding-top:.45rem; padding-bottom:.45rem;
  }
  /* หัวตารางที่กดเรียงได้ */
  .table thead th .sort-link{
    color:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:.3rem;
    white-space:nowrap;
  }
  .table thead th .sort-link:hover{ color:var(--accent-deep); }
  .table thead th .sort-link.active{ color:var(--accent-deep); }
  /* ไอคอนจาง ๆ ให้รู้ว่ากดได้ แต่ไม่แย่งสายตาคอลัมน์ที่เรียงอยู่ */
  .table thead th .sort-idle{ opacity:.35; }
  .table thead th .sort-link:hover .sort-idle{ opacity:.7; }

  .table tbody tr{ transition:background .12s ease; }
  .table tbody tr:hover{ background:var(--panel-2); }
  .table tbody tr:last-child > *{ border-bottom:0; }
  .table-wrap{ overflow-x:auto; margin:0 -.35rem; padding:0 .35rem; }
  .num-cell{ font-variant-numeric:tabular-nums; }

  /* ── ฟอร์ม ────────────────────────────────────── */
  .form-label{ font-size:.775rem; font-weight:600; color:var(--ink-soft); margin-bottom:.35rem; }
  .form-control, .form-select{
    background:var(--panel); border-color:var(--line); color:var(--ink);
    font-size:.87rem; border-radius:var(--r-sm); padding:.48rem .7rem;
    transition:border-color .15s ease, box-shadow .15s ease;
  }
  .form-control:hover:not(:focus), .form-select:hover:not(:focus){ border-color:var(--ink-faint); }
  .form-control:focus, .form-select:focus{
    background:var(--panel); color:var(--ink);
    border-color:var(--accent); box-shadow:0 0 0 3px rgba(var(--accent-ring),.18);
  }
  .form-control::placeholder{ color:var(--ink-faint); opacity:.75; }
  .form-control-sm, .form-select-sm{ font-size:.82rem; padding:.35rem .6rem; border-radius:8px; }
  .input-group-text{ background:var(--ground-2); border-color:var(--line); color:var(--ink-soft); font-size:.82rem; border-radius:var(--r-sm); }
  .form-check-input{ border-color:var(--line); }
  .form-check-input:checked{ background-color:var(--accent); border-color:var(--accent); }
  .form-check-input:focus{ border-color:var(--accent); box-shadow:0 0 0 3px rgba(var(--accent-ring),.18); }
  .form-control.is-invalid, .form-select.is-invalid{ border-color:var(--danger); }
  .form-text, .text-secondary{ color:var(--ink-soft) !important; }

  /* ── ปุ่ม ──────────────────────────────────────── */
  .btn{ border-radius:var(--r-sm); transition:all .15s ease; }
  .btn:focus-visible{ box-shadow:0 0 0 3px rgba(var(--accent-ring),.25); }
  .btn-primary{
    background:var(--accent); border-color:var(--accent); color:#fff;
    font-weight:600; box-shadow:var(--shadow-sm);
  }
  .btn-primary:hover, .btn-primary:focus{ background:var(--accent-deep); border-color:var(--accent-deep); color:#fff; }
  .btn-primary:active{ transform:translateY(1px); }
  .btn-outline-secondary{ border-color:var(--line); color:var(--ink-soft); background:var(--panel); }
  .btn-outline-secondary:hover{ background:var(--ground-2); border-color:var(--accent); color:var(--accent-deep); }
  .btn-danger{ background:var(--danger); border-color:var(--danger); font-weight:600; }
  .btn-danger:hover{ filter:brightness(.92); background:var(--danger); border-color:var(--danger); }
  .btn-outline-danger{ border-color:var(--danger-soft); color:var(--danger); }
  .btn-outline-danger:hover{ background:var(--danger); border-color:var(--danger); color:#fff; }
  .btn-sm{ font-size:.79rem; padding:.32rem .7rem; }
  .btn-sm i{ font-size:.85rem; }

  /* ── แท็บสลับภาษาในฟอร์ม ──────────────────────── */
  .lang-tabs{ display:inline-flex; gap:2px; margin-bottom:.5rem; background:var(--ground-2); padding:2px; border-radius:999px; }
  .lang-tab{
    background:transparent; border:0; color:var(--ink-soft);
    border-radius:999px; font-size:.7rem; font-weight:700; padding:.22rem .75rem;
    transition:all .15s ease;
  }
  .lang-tab:hover{ color:var(--ink); }
  .lang-tab.active{ background:var(--panel); color:var(--accent-deep); box-shadow:var(--shadow-sm); }
  .lang-pane{ display:none; }
  .lang-pane.active{ display:block; }

  /* ── ป้ายสถานะ ────────────────────────────────── */
  .badge-soft{
    display:inline-flex; align-items:center; gap:.3rem;
    background:var(--ground-2); color:var(--ink-soft); border-radius:999px;
    font-size:.7rem; font-weight:600; padding:.2rem .6rem; line-height:1.6;
    white-space:nowrap;
  }
  .badge-accent{ background:var(--accent-soft); color:var(--accent-deep); }
  .badge-warn{ background:var(--warn-soft); color:var(--warn); }
  .badge-danger{ background:var(--danger-soft); color:var(--danger); }
  .badge-ok{ background:var(--ok-soft); color:var(--ok); }
  .badge-soft::before{
    content:''; width:5px; height:5px; border-radius:50%; background:currentColor; opacity:.65; flex:0 0 auto;
  }
  .badge-soft.no-dot::before{ display:none; }

  /* ── สถานะว่าง ────────────────────────────────── */
  .empty-note{
    text-align:center; color:var(--ink-faint); font-size:.85rem;
    border:1px dashed var(--line); border-radius:var(--r-md);
    padding:2.6rem 1rem; background:var(--panel-2);
  }
  .empty-note i{ display:block; font-size:1.6rem; opacity:.45; margin-bottom:.5rem; }

  /* ── หน้าถัดไป ────────────────────────────────── */
  .pagination{
    --bs-pagination-bg:var(--panel); --bs-pagination-border-color:var(--line);
    --bs-pagination-color:var(--ink-soft); --bs-pagination-active-bg:var(--accent);
    --bs-pagination-active-border-color:var(--accent); --bs-pagination-hover-bg:var(--ground-2);
    --bs-pagination-hover-color:var(--accent-deep); --bs-pagination-hover-border-color:var(--line);
    --bs-pagination-disabled-bg:var(--panel); --bs-pagination-disabled-color:var(--ink-faint);
    --bs-pagination-disabled-border-color:var(--line); --bs-pagination-border-radius:var(--r-sm);
    --bs-pagination-focus-box-shadow:0 0 0 3px rgba(var(--accent-ring),.2);
    --bs-pagination-focus-bg:var(--ground-2); --bs-pagination-focus-color:var(--accent-deep);
    font-size:.84rem; gap:3px;
  }
  .pagination .page-link{ border-radius:var(--r-sm) !important; min-width:34px; text-align:center; }

  /* ── กล่องข้อความ ─────────────────────────────── */
  .modal-content{
    background:var(--panel); border:1px solid var(--line);
    border-radius:var(--r-xl); color:var(--ink); box-shadow:var(--shadow-lg);
  }
  .modal-header, .modal-footer{ border-color:var(--line-soft); }
  .modal-title{ font-family:var(--font-display); font-size:1.05rem; font-weight:600; }
  .modal-backdrop.show{ opacity:.4; }

  .dropdown-menu{
    background:var(--panel); border:1px solid var(--line); border-radius:var(--r-md);
    box-shadow:var(--shadow-md); font-size:.86rem; padding:.35rem;
    --bs-dropdown-link-color:var(--ink);
    --bs-dropdown-link-hover-bg:var(--ground-2); --bs-dropdown-link-hover-color:var(--ink);
    --bs-dropdown-link-active-bg:var(--accent-soft); --bs-dropdown-link-active-color:var(--accent-deep);
  }
  .dropdown-item{ border-radius:7px; padding:.42rem .6rem; }
  .dropdown-divider{ border-color:var(--line-soft); }

  /* ── แถบแจ้งเตือน ─────────────────────────────── */
  .alert-soft{
    background:var(--accent-soft); color:var(--accent-deep);
    border:1px solid transparent; border-radius:var(--r-md);
    font-size:.85rem; padding:.72rem 1rem;
    display:flex; align-items:center; gap:.55rem;
  }
  .alert-soft.alert-warn{ background:var(--warn-soft); color:var(--warn); }
  .alert-soft.alert-ok{ background:var(--ok-soft); color:var(--ok); }
  .alert-soft .btn-primary{ box-shadow:none; }
  .alert{ border-radius:var(--r-md); font-size:.86rem; }
  .alert-danger{
    background:var(--danger-soft); color:var(--danger); border-color:transparent;
  }

  /* ── กล่องยืนยัน/แจ้งเตือน (SweetAlert2) ────────
     ใช้ตัวแปรสีชุดเดียวกับหลังบ้าน จึงตามโหมดมืดเองอัตโนมัติ */
  .swal2-popup.swal-admin{
    background:var(--panel); color:var(--ink);
    border:1px solid var(--line); border-radius:var(--r-lg, 14px);
    box-shadow:var(--shadow-lg); font-size:.92rem; padding:1.6rem 1.5rem 1.35rem;
  }
  .swal2-popup.swal-admin .swal2-title{ color:var(--ink); font-size:1.08rem; font-weight:700; }
  .swal2-popup.swal-admin .swal2-html-container{ color:var(--ink-soft); font-size:.9rem; margin-top:.5rem; }
  .swal2-popup.swal-admin .swal2-actions{ gap:.5rem; margin-top:1.25rem; }
  .swal2-popup.swal-admin .swal2-styled{
    border-radius:999px; padding:.5rem 1.35rem; font-weight:700; font-size:.88rem;
    box-shadow:none; margin:0;
  }
  .swal2-popup.swal-admin .swal2-styled:focus{ box-shadow:0 0 0 3px rgba(var(--accent-ring), .35); }
  .swal2-popup.swal-admin .swal2-confirm{ background:var(--accent); color:#fff; }
  .swal2-popup.swal-admin .swal2-confirm:hover{ background:var(--accent-deep); }
  .swal2-popup.swal-admin .swal2-confirm.swal-confirm-danger{ background:var(--danger); }
  .swal2-popup.swal-admin .swal2-cancel{ background:transparent; color:var(--ink-soft); border:1px solid var(--line); }
  .swal2-popup.swal-admin .swal2-cancel:hover{ background:var(--ground-2); color:var(--ink); }
  .swal2-popup.swal-admin .swal2-icon{ margin:.4rem auto .2rem; }
  /* กล่องข้อความยาว ๆ ต้องตัดคำ ไม่งั้นดันกล่องกว้างเกิน */
  .swal2-popup.swal-admin .swal2-title,
  .swal2-popup.swal-admin .swal2-html-container{ overflow-wrap:break-word; word-break:break-word; }
  .swal2-container .swal2-toast{ background:var(--panel); color:var(--ink); border:1px solid var(--line); box-shadow:var(--shadow-md); }
  .swal2-container .swal2-toast .swal2-title{ color:var(--ink); font-size:.88rem; }

  .divider{ height:1px; background:var(--line-soft); margin:1rem 0; border:0; }

  /* ── จอเล็ก ───────────────────────────────────── */
  @media (max-width: 991.98px){
    .admin-sidebar{ transform:translateX(-100%); transition:transform .22s cubic-bezier(.4,0,.2,1); }
    .admin-sidebar.open{ transform:none; box-shadow:var(--shadow-lg); }
    .admin-main{ margin-left:0; }
    .sidebar-toggle{ display:inline-flex !important; }
    .sidebar-backdrop{
      display:none; position:fixed; inset:0; background:rgba(15,20,30,.45); z-index:25;
      backdrop-filter:blur(2px);
    }
    .sidebar-backdrop.open{ display:block; animation:fadeIn .18s ease; }
    .admin-content{ padding:1rem; }
    .admin-topbar{ padding:.7rem 1rem; }
    .admin-topbar h1{ font-size:1.02rem; }
  }
  .sidebar-toggle{ display:none; }

  @keyframes fadeIn{ from{ opacity:0 } to{ opacity:1 } }
  @keyframes riseIn{ from{ opacity:0; transform:translateY(6px) } to{ opacity:1; transform:none } }
  .admin-content > *{ animation:riseIn .28s cubic-bezier(.2,.7,.3,1) both; }

  @media (prefers-reduced-motion: reduce){
    *{ animation:none !important; transition:none !important; }
  }
  @media print{
    .admin-sidebar, .admin-topbar, .sidebar-backdrop{ display:none !important; }
    .admin-main{ margin-left:0; }
    .card-panel, .stat-tile{ box-shadow:none; break-inside:avoid; }
  }
</style>
@stack('styles')
</head>
<body>

<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-brand">
    <img src="{{ asset('images/logo.jpg') }}" alt="Drip Pilates">
    <span>
      <span class="bm">Drip Pilates</span>
      <span class="bs">Admin</span>
    </span>
  </div>

  <nav class="admin-nav">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <i class="bi bi-speedometer2"></i> แดชบอร์ด
    </a>

    <div class="grp">ตารางและการจอง</div>
    <a href="{{ route('admin.sessions.index') }}" class="{{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
      <i class="bi bi-calendar3"></i> รอบเรียน
    </a>
    <a href="{{ route('admin.schedules.index') }}" class="{{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
      <i class="bi bi-arrow-repeat"></i> ตารางประจำสัปดาห์
    </a>
    <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
      <i class="bi bi-check2-square"></i> การจอง
    </a>

    <div class="grp">ลูกค้าและการขาย</div>
    <a href="{{ route('admin.counter.index') }}" class="{{ request()->routeIs('admin.counter.*') ? 'active' : '' }}">
      <i class="bi bi-shop"></i> เคาน์เตอร์
    </a>
    <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
      <i class="bi bi-people"></i> ลูกค้า
    </a>
    <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
      <i class="bi bi-receipt"></i> คำสั่งซื้อ
      @if(($pendingPayments ?? 0) > 0)
        <span class="badge-count">{{ $pendingPayments }}</span>
      @endif
    </a>
    <a href="{{ route('admin.packages.index') }}" class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
      <i class="bi bi-ticket-perforated"></i> แพ็กเกจ
    </a>

    <div class="grp">ข้อมูลหลัก</div>
    <a href="{{ route('admin.branches.index') }}" class="{{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
      <i class="bi bi-geo-alt"></i> สาขาและห้อง
    </a>
    <a href="{{ route('admin.trainers.index') }}" class="{{ request()->routeIs('admin.trainers.*') ? 'active' : '' }}">
      <i class="bi bi-person-badge"></i> ครูผู้สอน
    </a>
    <a href="{{ route('admin.class-types.index') }}" class="{{ request()->routeIs('admin.class-types.*') ? 'active' : '' }}">
      <i class="bi bi-grid-3x3-gap"></i> ประเภทคลาส
    </a>
    <a href="{{ route('admin.holidays.index') }}" class="{{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
      <i class="bi bi-calendar-x"></i> วันหยุด
    </a>

    <div class="grp">อื่นๆ</div>
    <a href="{{ route('admin.announcements.index') }}" class="{{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
      <i class="bi bi-megaphone"></i> บทความ/ประกาศ
    </a>
    <a href="{{ route('admin.videos.index') }}" class="{{ request()->routeIs('admin.videos.*') ? 'active' : '' }}">
      <i class="bi bi-play-btn"></i> คลิปวิดีโอ
    </a>
    <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
      <i class="bi bi-graph-up"></i> รายงาน
    </a>
    <a href="{{ route('admin.manual.index') }}" class="{{ request()->routeIs('admin.manual.*') ? 'active' : '' }}">
      <i class="bi bi-book"></i> คู่มือการใช้งาน
    </a>
    @if(auth()->user()?->isOwner())
    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
      <i class="bi bi-shield-lock"></i> ผู้ใช้งานระบบ
    </a>
    <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
      <i class="bi bi-sliders"></i> ตั้งค่าระบบ
    </a>
    @endif
  </nav>
</aside>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="admin-main">
  <div class="admin-topbar">
    <button class="icon-btn sidebar-toggle" id="sidebarToggle" type="button"><i class="bi bi-list"></i></button>
    <h1>@yield('title', 'จัดการระบบ')</h1>

    <div class="ms-auto d-flex align-items-center gap-2">
      @yield('topbar-actions')
      <a class="icon-btn" href="{{ route('locale.set', app()->getLocale() === 'th' ? 'en' : 'th') }}"
         title="{{ __t('เปลี่ยนเป็นภาษาอังกฤษ', 'Switch to Thai') }}"
         style="width:auto; padding-inline:.6rem; font-size:.72rem; font-weight:700; letter-spacing:.04em;">
        {{ app()->getLocale() === 'th' ? 'EN' : 'ไทย' }}
      </a>
      <button class="icon-btn" id="themeToggle" type="button" title="สลับโหมดสว่าง/มืด"><i class="bi bi-circle-half"></i></button>
      <div class="dropdown">
        <button class="icon-btn" data-bs-toggle="dropdown" type="button" title="{{ auth()->user()?->name }}">
          <i class="bi bi-person-circle"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-item-text small">
            <strong>{{ auth()->user()?->name }}</strong><br>
            <span class="text-secondary">{{ auth()->user()?->email }}</span>
          </span></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="POST" action="{{ route('admin.logout') }}">
              @csrf
              <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="admin-content">
    @if(session('status'))
      <div class="alert-soft mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger py-2 mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-octagon-fill"></i> {{ session('error') }}
      </div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger py-2 mb-3">
        <strong><i class="bi bi-exclamation-triangle-fill"></i> กรอกข้อมูลไม่ครบหรือไม่ถูกต้อง</strong>
        <ul class="mb-0 mt-1 small">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    @yield('content')
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<script>
// สลับโหมดสว่าง/มืด จำค่าไว้
(function(){
  // localStorage โยน error ได้ถ้าเบราว์เซอร์บล็อกคุกกี้/เปิดโหมดส่วนตัว
  function read(){ try { return localStorage.getItem('adminTheme'); } catch(err){ return null; } }
  function save(v){ try { localStorage.setItem('adminTheme', v); } catch(err){} }

  var saved = read();
  if(saved){ document.documentElement.setAttribute('data-bs-theme', saved); }

  var btn = document.getElementById('themeToggle');
  btn && btn.addEventListener('click', function(){
    var html = document.documentElement;
    var next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-bs-theme', next);
    save(next);
  });
})();

// เมนูข้างบนจอเล็ก
(function(){
  var sb = document.getElementById('adminSidebar');
  var bd = document.getElementById('sidebarBackdrop');
  var tg = document.getElementById('sidebarToggle');
  function close(){ sb && sb.classList.remove('open'); bd && bd.classList.remove('open'); }
  tg && tg.addEventListener('click', function(){ sb.classList.toggle('open'); bd.classList.toggle('open'); });
  bd && bd.addEventListener('click', close);
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape') close(); });
})();

// แท็บสลับภาษาในฟอร์ม
document.addEventListener('click', function(e){
  var tab = e.target.closest('.lang-tab');
  if(!tab) return;
  var group = tab.closest('[data-lang-group]');
  if(!group) return;
  var lang = tab.dataset.lang;
  group.querySelectorAll('.lang-tab').forEach(function(t){ t.classList.toggle('active', t === tab); });
  group.querySelectorAll('.lang-pane').forEach(function(p){ p.classList.toggle('active', p.dataset.lang === lang); });
});

// กล่องยืนยันมาตรฐานของหลังบ้าน
window.adminConfirm = function(opts){
  opts = opts || {};
  // ถ้า CDN โหลดไม่ติด ยังต้องยืนยันได้อยู่ จึงถอยไปใช้ confirm ของเบราว์เซอร์
  if(!window.Swal){
    return Promise.resolve(window.confirm(opts.text || opts.title || 'ยืนยัน?'));
  }
  return Swal.fire({
    title: opts.title || __swalT('ยืนยันการทำรายการ', 'Please confirm'),
    text: opts.text || '',
    icon: opts.icon || 'question',
    showCancelButton: true,
    confirmButtonText: opts.confirmText || __swalT('ยืนยัน', 'Confirm'),
    cancelButtonText: opts.cancelText || __swalT('ยกเลิก', 'Cancel'),
    reverseButtons: true,
    focusCancel: !!opts.danger,
    buttonsStyling: false,
    customClass: {
      popup: 'swal-admin',
      confirmButton: 'swal2-confirm swal2-styled' + (opts.danger ? ' swal-confirm-danger' : ''),
      cancelButton: 'swal2-cancel swal2-styled'
    }
  }).then(function(r){ return r.isConfirmed; });
};

function __swalT(th, en){
  return document.documentElement.lang === 'en' ? en : th;
}

// คำที่บอกว่าเป็นการกระทำที่ย้อนกลับไม่ได้ → ปุ่มยืนยันเป็นสีแดง
var DANGER_WORDS = /ลบ|ยกเลิก|หัก|ไม่มาเรียน|ย้อนกลับ|delete|remove|cancel|revoke/i;

// ยืนยันก่อนทำสิ่งที่ย้อนกลับไม่ได้
document.addEventListener('submit', function(e){
  var form = e.target;
  var msg = form.dataset.confirm;
  // ผ่านด่านมาแล้ว ปล่อยให้ส่งจริง
  if(!msg || form.dataset.confirmed === '1') return;

  // SweetAlert เป็น async จึงต้องหยุดการส่งไว้ก่อน แล้วค่อยสั่งส่งใหม่เมื่อผู้ใช้กดยืนยัน
  e.preventDefault();
  var danger = DANGER_WORDS.test(msg);
  adminConfirm({ text: msg, danger: danger, icon: danger ? 'warning' : 'question' })
    .then(function(ok){
      if(!ok) return;
      form.dataset.confirmed = '1';
      // requestSubmit ทำให้ปุ่มที่กดถูกส่งไปด้วย (submit() ธรรมดาจะตกหล่น)
      if(typeof form.requestSubmit === 'function'){ form.requestSubmit(); }
      else { form.submit(); }
    });
});

// กันกดปุ่มส่งฟอร์มซ้ำ
document.addEventListener('submit', function(e){
  if(e.defaultPrevented) return;
  var btn = e.target.querySelector('button[type="submit"]:not([data-no-lock])');
  if(!btn) return;
  setTimeout(function(){ btn.disabled = true; btn.style.opacity = '.65'; }, 0);
});
</script>
@stack('scripts')
</body>
</html>

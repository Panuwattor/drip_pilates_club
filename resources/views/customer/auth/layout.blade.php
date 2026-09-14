<!doctype html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') · Drip Pilates</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root{
    --ground:#EEF1F6; --panel:#FFFFFF; --ink:#2B3242; --ink-soft:#6B7690;
    --accent:#7C93B8; --accent-deep:#5E7699; --accent-soft:#DCE3EF; --line:#DCE1EB;
  }
  [data-bs-theme="dark"]{
    --ground:#161A22; --panel:#20252F; --ink:#E9ECF3; --ink-soft:#A0ABC2;
    --accent:#9BB0D1; --accent-deep:#B7C6E2; --accent-soft:#2C3547; --line:#333B4C;
  }
  body{
    background:var(--ground); color:var(--ink); min-height:100vh;
    display:flex; align-items:center; justify-content:center; padding:1.5rem;
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
  }
  .auth-card{
    background:var(--panel); border:1px solid var(--line); border-radius:20px;
    padding:2rem; width:100%; max-width:420px; box-shadow:0 12px 40px rgba(0,0,0,.07);
  }
  .auth-brand{ text-align:center; margin-bottom:1.5rem; }
  .auth-brand img{ width:56px; height:56px; border-radius:50%; object-fit:cover; border:1px solid var(--line); }
  .auth-brand .bm{ font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-weight:700; font-size:1.3rem; display:block; margin-top:.6rem; }
  h1{ font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-size:1.4rem; font-weight:600; margin:0 0 .2rem; }
  .sub{ font-size:.85rem; color:var(--ink-soft); margin-bottom:1.25rem; }
  .form-label{ font-size:.8rem; font-weight:600; color:var(--ink-soft); margin-bottom:.3rem; }
  .form-control, .form-select{
    background:var(--panel); border-color:var(--line); color:var(--ink);
    font-size:.9rem; padding:.6rem .8rem;
  }
  .form-control:focus, .form-select:focus{
    background:var(--panel); color:var(--ink);
    border-color:var(--accent); box-shadow:0 0 0 .2rem rgba(124,147,184,.18);
  }
  .btn-accent{
    background:var(--accent); border:1px solid var(--accent); color:#FBF3F0;
    font-weight:600; padding:.6rem; border-radius:.5rem;
  }
  .btn-accent:hover{ background:var(--accent-deep); border-color:var(--accent-deep); color:#fff; }
  a{ color:var(--accent-deep); }

  /* สีเขียว LINE ตามแบรนด์ไกด์ทางการ */
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

  /* ปุ่มสลับภาษา มุมขวาบนของการ์ด */
  .auth-card{ position:relative; }
  .lang-switch{
    position:absolute; top:1rem; right:1rem;
    background:var(--ground); border:1px solid var(--line); border-radius:999px;
    font-size:.72rem; font-weight:700; padding:.3rem .7rem; color:var(--ink);
    text-decoration:none; display:inline-flex; align-items:center; gap:.35rem;
  }
  .lang-switch:hover{ border-color:var(--accent); color:var(--ink); }
</style>
</head>
<body>

<div class="auth-card">
  <a href="{{ route('locale.set', app()->getLocale() === 'th' ? 'en' : 'th') }}" class="lang-switch">
    <img src="{{ asset('images/' . (app()->getLocale() === 'th' ? 'en' : 'th') . '.png') }}"
         width="14" height="14" style="border-radius:50%;object-fit:cover;" alt="">
    {{ app()->getLocale() === 'th' ? 'EN' : 'TH' }}
  </a>

  <div class="auth-brand">
    <img src="{{ asset('images/logo.jpg') }}" alt="Drip Pilates">
    <span class="bm">Drip Pilates</span>
  </div>

  @yield('content')

  <div class="text-center mt-3">
    <a href="{{ route('landing') }}" class="small text-decoration-none" style="color:var(--ink-soft);">
      <i class="bi bi-arrow-left"></i> {{ __t('กลับหน้าแรก', 'Back to home') }}
    </a>
  </div>
</div>

</body>
</html>

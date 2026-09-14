<!doctype html>
<html lang="th" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>เข้าสู่ระบบจัดการ · Drip Pilates</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=noto-sans-thai:400,500,600,700|fraunces:600,700" rel="stylesheet">
<style>
  :root{
    --ground:#F4F6FA; --panel:#FFFFFF; --panel-2:#FAFBFD;
    --ink:#232936; --ink-soft:#69748C; --ink-faint:#96A0B5;
    --accent:#6C88B4; --accent-deep:#4E6A96; --accent-soft:#E5EBF6; --accent-ring:108,136,180;
    --danger:#A8383C; --danger-soft:#F9DEDF;
    --line:#E3E8F0; --line-soft:#EDF1F7;
    --font-display:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
  }
  *{ box-sizing:border-box; }
  body{
    background:var(--ground); color:var(--ink); min-height:100vh; margin:0;
    display:flex; align-items:center; justify-content:center; padding:1.5rem;
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,sans-serif;
    -webkit-font-smoothing:antialiased;
    position:relative; overflow-x:hidden;
  }
  /* ลายพื้นหลังนุ่มๆ */
  body::before, body::after{
    content:''; position:fixed; border-radius:50%; filter:blur(90px); z-index:0; pointer-events:none;
  }
  body::before{
    width:520px; height:520px; top:-190px; left:-140px;
    background:radial-gradient(circle, rgba(108,136,180,.28), transparent 70%);
  }
  body::after{
    width:460px; height:460px; bottom:-180px; right:-130px;
    background:radial-gradient(circle, rgba(126,151,168,.22), transparent 70%);
  }

  .login-card{
    position:relative; z-index:1;
    background:var(--panel); border:1px solid var(--line); border-radius:22px;
    padding:2.25rem 2rem; width:100%; max-width:412px;
    box-shadow:0 1px 2px rgba(28,38,60,.04), 0 24px 60px -20px rgba(28,38,60,.22);
    animation:riseIn .45s cubic-bezier(.2,.7,.3,1) both;
  }
  @keyframes riseIn{ from{ opacity:0; transform:translateY(14px) } to{ opacity:1; transform:none } }

  .login-brand{ text-align:center; margin-bottom:1.75rem; }
  .login-brand img{
    width:60px; height:60px; border-radius:18px; object-fit:cover;
    border:1px solid var(--line); box-shadow:0 6px 18px -6px rgba(28,38,60,.28);
  }
  .login-brand .bm{
    font-family:var(--font-display); font-weight:700; font-size:1.34rem;
    display:block; margin-top:.75rem; letter-spacing:-.02em;
  }
  .login-brand .sub{
    font-size:.75rem; color:var(--ink-faint); letter-spacing:.06em;
    text-transform:uppercase; font-weight:600;
  }

  .form-label{ font-size:.78rem; font-weight:600; color:var(--ink-soft); margin-bottom:.35rem; }
  .form-control{
    border-color:var(--line); font-size:.9rem; padding:.62rem .85rem; border-radius:10px;
    background:var(--panel-2); color:var(--ink);
    transition:border-color .15s ease, box-shadow .15s ease, background .15s ease;
  }
  .form-control:hover:not(:focus){ border-color:var(--ink-faint); }
  .form-control:focus{
    background:var(--panel); color:var(--ink);
    border-color:var(--accent); box-shadow:0 0 0 3px rgba(var(--accent-ring),.18);
  }
  .form-control::placeholder{ color:var(--ink-faint); opacity:.7; }

  .pw-wrap{ position:relative; }
  .pw-wrap .form-control{ padding-right:2.7rem; }
  .pw-toggle{
    position:absolute; top:50%; right:.35rem; transform:translateY(-50%);
    background:none; border:0; color:var(--ink-faint); padding:.35rem .5rem;
    border-radius:8px; line-height:1; cursor:pointer; transition:color .15s ease;
  }
  .pw-toggle:hover{ color:var(--accent-deep); }

  .form-check-input{ border-color:var(--line); }
  .form-check-input:checked{ background-color:var(--accent); border-color:var(--accent); }
  .form-check-input:focus{ border-color:var(--accent); box-shadow:0 0 0 3px rgba(var(--accent-ring),.18); }
  .form-check-label{ color:var(--ink-soft); }

  .btn-primary{
    background:var(--accent); border-color:var(--accent); font-weight:600;
    padding:.65rem; border-radius:10px; font-size:.92rem;
    box-shadow:0 6px 16px -8px rgba(var(--accent-ring),.9);
    transition:all .15s ease;
  }
  .btn-primary:hover, .btn-primary:focus{ background:var(--accent-deep); border-color:var(--accent-deep); }
  .btn-primary:active{ transform:translateY(1px); }
  .btn-primary:focus-visible{ box-shadow:0 0 0 3px rgba(var(--accent-ring),.3); }

  .alert-danger{
    background:var(--danger-soft); color:var(--danger); border:0;
    border-radius:10px; font-size:.83rem; padding:.65rem .85rem;
    display:flex; align-items:center; gap:.5rem;
  }

  .back-link{
    color:var(--ink-faint); font-size:.8rem; text-decoration:none;
    display:inline-flex; align-items:center; gap:.35rem; transition:color .15s ease;
  }
  .back-link:hover{ color:var(--accent-deep); }

  @media (prefers-reduced-motion: reduce){ *{ animation:none !important; transition:none !important; } }
</style>
</head>
<body>

<form class="login-card" method="POST" action="{{ route('admin.login') }}">
  @csrf

  <div class="login-brand">
    <img src="{{ asset('images/logo.jpg') }}" alt="Drip Pilates">
    <span class="bm">Drip Pilates</span>
    <span class="sub">ระบบจัดการหลังบ้าน</span>
  </div>

  @if($errors->any())
    <div class="alert-danger mb-3">
      <i class="bi bi-exclamation-circle-fill"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <div class="mb-3">
    <label class="form-label" for="email">อีเมล</label>
    <input class="form-control" type="email" id="email" name="email" placeholder="you@drippilates.com"
           value="{{ old('email') }}" required autofocus autocomplete="username">
  </div>

  <div class="mb-3">
    <label class="form-label" for="password">รหัสผ่าน</label>
    <div class="pw-wrap">
      <input class="form-control" type="password" id="password" name="password" placeholder="••••••••"
             required autocomplete="current-password">
      <button class="pw-toggle" type="button" id="pwToggle" title="แสดง/ซ่อนรหัสผ่าน" tabindex="-1">
        <i class="bi bi-eye" id="pwIcon"></i>
      </button>
    </div>
  </div>

  <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
    <label class="form-check-label small" for="remember">จดจำการเข้าสู่ระบบ</label>
  </div>

  <button class="btn btn-primary w-100" type="submit">
    <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
  </button>

  <div class="text-center mt-3">
    <a href="{{ route('landing') }}" class="back-link">
      <i class="bi bi-arrow-left"></i> กลับหน้าเว็บลูกค้า
    </a>
  </div>
</form>

<script>
(function(){
  var btn = document.getElementById('pwToggle');
  var inp = document.getElementById('password');
  var ico = document.getElementById('pwIcon');
  btn.addEventListener('click', function(){
    var show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    ico.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    inp.focus();
  });
})();
</script>

</body>
</html>

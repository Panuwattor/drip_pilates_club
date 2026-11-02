<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>DRIP Pilates</title>
<style>
  body{
    margin:0; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1rem;
    background:#F4F1EA; color:#231F20; text-align:center; padding:1.5rem; box-sizing:border-box;
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,sans-serif;
  }
  .spinner{ width:36px; height:36px; border:3px solid #E8E1D5; border-top-color:#B1977A; border-radius:50%; animation:spin .8s linear infinite; }
  @keyframes spin{ to{ transform:rotate(360deg); } }
  .msg{ font-size:.95rem; color:#6E6559; }
  .retry{ display:none; color:#8C7255; }
</style>
</head>
<body>
  <div class="spinner" id="spinner"></div>
  <div class="msg" id="msg">{{ __t('กำลังเข้าสู่ระบบด้วย LINE...', 'Signing in with LINE...') }}</div>
  <a class="retry" id="retry" href="{{ route('customer.login') }}">{{ __t('ไปหน้าเข้าสู่ระบบ', 'Go to login page') }}</a>

<script src="https://static.line-scdn.net/liff/edge/2/sdk.js" charset="utf-8"></script>
<script>
(async () => {
  const fail = (text) => {
    document.getElementById('spinner').style.display = 'none';
    document.getElementById('msg').textContent = text;
    document.getElementById('retry').style.display = 'inline';
  };

  try {
    await liff.init({ liffId: @json($liffId) });

    // เปิดลิงก์ LIFF จากนอกแอป LINE ใช้ OAuth แบบเดิมไป
    if (!liff.isLoggedIn()) {
      if (liff.isInClient()) {
        liff.login({ redirectUri: location.href });
      } else {
        location.replace(@json(route('customer.line.redirect')));
      }
      return;
    }

    // หลัง liff.init() ตัว SDK จะแปลง liff.state กลับเป็น query เดิม จึงอ่าน ?to= ตรงนี้
    const to = new URLSearchParams(location.search).get('to') || @json($to);

    const res = await fetch(@json(route('customer.line.liff.login')), {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ id_token: liff.getIDToken(), to }),
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok || !data.redirect) {
      fail(data.message || @json(__t('เข้าสู่ระบบไม่สำเร็จ กรุณาลองใหม่อีกครั้ง', 'Sign-in failed, please try again')));
      return;
    }

    location.replace(data.redirect);
  } catch (e) {
    fail(@json(__t('เข้าสู่ระบบไม่สำเร็จ กรุณาลองใหม่อีกครั้ง', 'Sign-in failed, please try again')));
  }
})();
</script>
</body>
</html>

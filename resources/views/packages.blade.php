<!doctype html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __t('แพ็กเกจทั้งหมด', 'All Packages') }} · Drip Pilates Club</title>
<meta name="description" content="{{ __t('แพ็กเกจพิลาทิสทั้งหมดของ Drip Pilates Club — Private, Duo, Trio Reformer และบริการฟื้นฟูร่างกาย', 'All Pilates packages at Drip Pilates Club — Private, Duo, Trio Reformer and recovery services.') }}">
<link rel="canonical" href="{{ route('packages.index') }}">
<link rel="alternate" hreflang="th" href="{{ route('packages.index') }}">
<link rel="alternate" hreflang="en" href="{{ route('packages.index') }}">
<link rel="alternate" hreflang="x-default" href="{{ route('packages.index') }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="Drip Pilates Club">
<meta property="og:title" content="{{ __t('แพ็กเกจทั้งหมด', 'All Packages') }} · Drip Pilates Club">
<meta property="og:description" content="{{ __t('แพ็กเกจพิลาทิสทั้งหมดของ Drip Pilates Club', 'All Pilates packages at Drip Pilates Club.') }}">
<meta property="og:image" content="{{ asset('images/01.jpg') }}">
<meta property="og:url" content="{{ route('packages.index') }}">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#7C93B8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

@php $allPackages = $groups->flatten(); @endphp
@if($allPackages->isNotEmpty())
{{-- แต่ละแพ็กเป็น Product/Offer ช่วยให้ราคาขึ้น rich result ได้ --}}
{{-- key '@context'/'@type' ต้องประกอบผ่านตัวแปร ไม่งั้น Blade parse @context เป็น directive แล้ว JSON พัง --}}
@php
    $c = '@' . 'context';
    $t = '@' . 'type';

    $ldPackages = [
        $c => 'https://schema.org',
        $t => 'ItemList',
        'itemListElement' => $allPackages->values()->map(fn ($p, $i) => [
            $t => 'ListItem',
            'position' => $i + 1,
            'item' => [
                $t => 'Product',
                'name' => $p->name,
                'description' => $p->description ?: $p->name,
                'offers' => [
                    $t => 'Offer',
                    'price' => (string) $p->price,
                    'priceCurrency' => 'THB',
                    'availability' => 'https://schema.org/InStock',
                ],
            ],
        ])->all(),
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($ldPackages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif

<style>
  :root{
    --ground:#EEF1F6; --panel:#FFFFFF; --ink:#2B3242; --ink-soft:#6B7690;
    --accent:#7C93B8; --accent-deep:#5E7699; --accent-soft:#DCE3EF; --line:#DCE1EB;
  }
  html{ overflow-x:hidden; }
  body{
    background:var(--ground); color:var(--ink); margin:0;
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
    overflow-x:hidden;
  }
  h1,h2,h3{ font-family:'Playfair Display','Noto Sans Thai',Georgia,serif; }

  .site-nav{
    position:sticky; top:0; z-index:50;
    background:rgba(255,255,255,.9); backdrop-filter:blur(10px);
    border-bottom:1px solid var(--line); padding:.8rem 0;
  }
  .site-nav .brand{ font-weight:700; font-size:1.2rem; color:var(--ink); display:flex; align-items:center; gap:.5rem; text-decoration:none; }
  .site-nav .brand img{ width:36px; height:36px; border-radius:50%; object-fit:cover; }
  /* ปุ่มสลับภาษาหน้าตาเดียวกับหน้า landing */
  .lang-switch{
    background:var(--ground); border:1px solid var(--line); border-radius:999px;
    font-size:.78rem; font-weight:700; padding:.4rem .9rem; color:var(--ink); text-decoration:none;
  }
  .lang-switch:hover{ color:var(--accent-deep); border-color:var(--accent); }
  .btn-nav-cta{
    background:var(--accent); border:1px solid var(--accent); color:#fff;
    border-radius:999px; padding:.5rem 1.2rem; font-weight:700; font-size:.9rem; text-decoration:none;
  }
  .btn-nav-cta:hover{ background:var(--accent-deep); color:#fff; }
  @media (max-width: 575.98px){
    .site-nav .brand span{ display:none; }
  }

  .page-head{ padding:3rem 0 1rem; text-align:center; }
  .page-head .breadcrumb-link{ font-size:.85rem; font-weight:700; color:var(--accent-deep); text-decoration:none; }
  .page-head .eyebrow{ text-transform:uppercase; letter-spacing:.2em; font-size:.72rem; font-weight:700; color:var(--accent-deep); margin:1.5rem 0 .5rem; }
  .page-head h1{ font-size:clamp(1.7rem,3.4vw,2.5rem); font-weight:700; margin:0; }
  .page-head p{ color:var(--ink-soft); max-width:620px; margin:.75rem auto 0; }

  .pkg-group{ padding:2rem 0 1rem; }
  .pkg-group-title{ font-size:1.35rem; font-weight:700; margin:0 0 1.25rem; display:flex; align-items:center; gap:.6rem; }
  .pkg-group-title .dot{ width:10px; height:10px; border-radius:50%; background:var(--accent); }

  .pkg-card{
    background:var(--panel); border:1px solid var(--line); border-radius:20px; padding:1.75rem;
    height:100%; position:relative; transition:transform .2s, box-shadow .2s;
  }
  .pkg-card:hover{ transform:translateY(-4px); box-shadow:0 16px 32px rgba(43,50,66,.08); }
  .pkg-card h3{ font-size:1.1rem; margin:0 0 .3rem; }
  .pkg-card .pkg-price{ font-size:2rem; font-weight:700; color:var(--accent-deep); margin:.5rem 0 0; }
  .pkg-card .pkg-price small{ font-size:.85rem; font-weight:500; color:var(--ink-soft); }
  .pkg-card .pkg-compare{ color:var(--ink-soft); text-decoration:line-through; font-size:.85rem; }
  .pkg-card .pkg-meta{ color:var(--ink-soft); font-size:.85rem; margin:.75rem 0 1.25rem; }
  .pkg-card .btn-accent{
    display:block; text-align:center; background:var(--accent); border:1px solid var(--accent); color:#fff;
    border-radius:999px; padding:.65rem; font-weight:700; text-decoration:none; font-size:.9rem;
  }
  .pkg-card .btn-accent:hover{ background:var(--accent-deep); color:#fff; }

  .empty-note{ text-align:center; color:var(--ink-soft); padding:4rem 0; }

  .site-footer{ background:#1E2432; color:#B9C2D6; padding:2.5rem 0 1.5rem; margin-top:3rem; }
  .site-footer .fbottom{ text-align:center; font-size:.78rem; color:#8B96AC; }
</style>
</head>
<body>

<nav class="site-nav">
  <div class="container-lg d-flex align-items-center justify-content-between">
    <a href="{{ route('landing') }}" class="brand">
      <img src="{{ asset('images/logo.jpg') }}" alt="Drip Pilates Club">
      <span>Drip Pilates Club</span>
    </a>
    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('locale.set', app()->getLocale() === 'th' ? 'en' : 'th') }}" class="lang-switch d-inline-flex align-items-center gap-1">
        <img src="{{ asset('images/' . (app()->getLocale() === 'th' ? 'en' : 'th') . '.png') }}" width="16" height="16" style="border-radius:50%;object-fit:cover;" alt="">
        {{ app()->getLocale() === 'th' ? 'EN' : 'TH' }}
      </a>
      <a href="{{ route('customer.login') }}" class="btn-nav-cta">{{ __t('เข้าสู่ระบบ', 'Log In') }}</a>
    </div>
  </div>
</nav>

<div class="container-lg">
  <div class="page-head">
    <a href="{{ route('landing') }}#packages" class="breadcrumb-link"><i class="bi bi-arrow-left"></i> {{ __t('กลับหน้าแรก', 'Back to Home') }}</a>
    <div class="eyebrow">{{ __t('แพ็กเกจ', 'Packages') }}</div>
    <h1>{{ __t('แพ็กเกจทั้งหมด', 'All Packages') }}</h1>
    <p>{{ __t('เลือกแพ็กเกจที่เหมาะกับไลฟ์สไตล์คุณ', 'Choose the package that fits your lifestyle') }}</p>
  </div>

  @if($allPackages->isEmpty())
    <div class="empty-note"><i class="bi bi-box fs-1 d-block mb-2"></i>{{ __t('ยังไม่มีแพ็กเกจ', 'No packages yet') }}</div>
  @else
    @foreach($groups as $groupName => $items)
      <div class="pkg-group">
        <h2 class="pkg-group-title"><span class="dot"></span>{{ $groupName }}</h2>
        <div class="row g-4">
          @foreach($items as $pkg)
            <div class="col-md-6 col-lg-4">
              <div class="pkg-card">
                <h3>{{ $pkg->name }}</h3>
                @if($pkg->description)<p class="small text-secondary mb-0">{{ $pkg->description }}</p>@endif
                <div class="pkg-price">
                  ฿{{ number_format($pkg->price) }}
                  @if($pkg->compare_at_price)<span class="pkg-compare ms-2">฿{{ number_format($pkg->compare_at_price) }}</span>@endif
                </div>
                <div class="pkg-meta">
                  {{ $pkg->credit_amount === null ? __t('ไม่จำกัดจำนวนครั้ง', 'Unlimited classes') : __t($pkg->credit_amount . ' ครั้ง', $pkg->credit_amount . ' classes') }}
                  · {{ __t('ใช้ได้ ' . $pkg->valid_days . ' วัน', 'valid ' . $pkg->valid_days . ' days') }}
                </div>
                <a href="{{ route('customer.register') }}" class="btn-accent">{{ __t('สมัครเพื่อซื้อแพ็กเกจ', 'Sign Up To Purchase') }}</a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  @endif
</div>

<footer class="site-footer">
  <div class="container-lg">
    <div class="fbottom">&copy; {{ date('Y') }} Drip Pilates Club. {{ __t('สงวนลิขสิทธิ์', 'All rights reserved.') }}</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

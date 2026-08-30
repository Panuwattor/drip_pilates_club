<!doctype html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Drip Pilates Club · {{ __t('สตูดิโอพิลาทิสสำหรับทุกคน', 'Pilates Studio For Everyone') }}</title>
<meta name="description" content="{{ __t('Drip Pilates Club สตูดิโอพิลาทิสบรรยากาศอบอุ่น จองคลาสง่าย ครูผู้สอนมืออาชีพ หลายสาขา', 'Drip Pilates Club — a warm, welcoming Pilates studio. Easy class booking, professional instructors, multiple branches.') }}">
<link rel="canonical" href="{{ url('/') }}">
<link rel="alternate" hreflang="th" href="{{ url('/') }}">
<link rel="alternate" hreflang="en" href="{{ url('/') }}">
<link rel="alternate" hreflang="x-default" href="{{ url('/') }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="Drip Pilates Club">
<meta property="og:title" content="Drip Pilates Club · {{ __t('สตูดิโอพิลาทิสสำหรับทุกคน', 'Pilates Studio For Everyone') }}">
<meta property="og:description" content="{{ __t('จองคลาสพิลาทิสง่ายๆ กับครูมืออาชีพ หลายสาขาทั่วกรุงเทพฯ', 'Book Pilates classes easily with professional instructors across Bangkok.') }}">
<meta property="og:image" content="{{ asset('images/01.jpg') }}">
<meta property="og:url" content="{{ url('/') }}">
<meta name="twitter:card" content="summary_large_image">

<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#7C93B8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

{{-- JSON-LD ช่วย SEO ให้ Google เข้าใจว่านี่คือธุรกิจสตูดิโอออกกำลังกาย มีหลายสาขา --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ExerciseGym',
    'name' => 'Drip Pilates Club',
    'image' => asset('images/01.jpg'),
    'url' => url('/'),
    'telephone' => optional($branches->first())->phone,
    'location' => $branches->map(fn ($b) => [
        '@type' => 'Place',
        'name' => $b->name,
        'address' => $b->address,
    ])->values(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

<style>
  :root{
    --ground:#EEF1F6; --panel:#FFFFFF; --ink:#2B3242; --ink-soft:#6B7690;
    --accent:#7C93B8; --accent-deep:#5E7699; --accent-soft:#DCE3EF;
    --sage:#8C9DBE; --sage-soft:#E4E9F2; --line:#DCE1EB;
  }
  *{ scroll-behavior:smooth; }
  html{ overflow-x:hidden; }
  body{
    background:var(--ground); color:var(--ink); margin:0;
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
    overflow-x:hidden;
  }
  h1,h2,h3,.display-serif{ font-family:'Playfair Display','Noto Sans Thai',Georgia,serif; }

  /* ---------- Nav ---------- */
  .site-nav{
    position:fixed; top:0; left:0; right:0; z-index:50;
    background:rgba(255,255,255,.85); backdrop-filter:blur(10px);
    border-bottom:1px solid var(--line); padding:.8rem 0;
    transition:background .2s;
  }
  .site-nav .brand{ font-weight:700; font-size:1.2rem; color:var(--ink); display:flex; align-items:center; gap:.5rem; }
  .site-nav .brand img{ width:36px; height:36px; border-radius:50%; object-fit:cover; }
  .site-nav .nav-links{ display:flex; align-items:center; gap:1.6rem; }
  .site-nav .nav-links a{ color:var(--ink-soft); text-decoration:none; font-size:.9rem; font-weight:600; }
  .site-nav .nav-links a:hover{ color:var(--accent-deep); }
  .lang-switch{
    background:var(--ground); border:1px solid var(--line); border-radius:999px;
    font-size:.78rem; font-weight:700; padding:.4rem .9rem; color:var(--ink);
  }
  .btn-nav-cta{
    background:var(--accent); border:1px solid var(--accent); color:#fff!important;
    border-radius:999px; padding:.5rem 1.2rem!important; font-weight:700!important;
  }
  .btn-nav-cta:hover{ background:var(--accent-deep); }
  .nav-toggle{ display:none; background:none; border:1px solid var(--line); border-radius:10px; width:40px; height:40px; }

  @media (max-width: 991.98px){
    .site-nav .nav-links{
      position:fixed; top:64px; left:0; right:0; background:var(--panel);
      flex-direction:column; align-items:stretch; gap:0; padding:.5rem 1.25rem 1.25rem;
      border-bottom:1px solid var(--line); box-shadow:0 12px 24px rgba(0,0,0,.06);
      display:none;
    }
    .site-nav .nav-links.open{ display:flex; }
    .nav-links a{ padding:.75rem 0; border-bottom:1px solid var(--line); }
    .nav-links .btn-nav-cta{ margin-top:.75rem; text-align:center; }
    .nav-toggle{ display:flex; align-items:center; justify-content:center; }
  }

  /* ---------- Hero / Slideshow ---------- */
  .hero{ position:relative; height:92vh; min-height:560px; overflow:hidden; }
  .hero-slide{
    position:absolute; inset:0; background-size:cover; background-position:center;
    opacity:0; transition:opacity 1.2s ease-in-out;
  }
  .hero-slide.active{ opacity:1; }
  .hero-overlay{
    position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(30,36,50,.55) 0%, rgba(30,36,50,.35) 45%, rgba(30,36,50,.75) 100%);
  }
  .hero-content{
    position:relative; z-index:2; height:100%; display:flex; flex-direction:column;
    align-items:center; justify-content:center; text-align:center; color:#fff; padding:0 1.25rem;
  }
  .hero-content .eyebrow{
    text-transform:uppercase; letter-spacing:.25em; font-size:.75rem; font-weight:700;
    color:#EAD9C9; margin-bottom:1rem;
  }
  .hero-content h1{ font-size:clamp(2rem, 5vw, 3.6rem); font-weight:700; max-width:820px; margin:0 0 1rem; line-height:1.15; }
  .hero-content p{ font-size:clamp(1rem, 2vw, 1.2rem); max-width:600px; opacity:.92; margin:0 0 2rem; }
  .hero-ctas{ display:flex; gap:.85rem; flex-wrap:wrap; justify-content:center; }
  .btn-hero-primary{
    background:var(--accent); border:1px solid var(--accent); color:#fff;
    border-radius:999px; padding:.85rem 1.9rem; font-weight:700; font-size:.95rem;
  }
  .btn-hero-primary:hover{ background:var(--accent-deep); color:#fff; }
  .btn-hero-outline{
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.6); color:#fff;
    border-radius:999px; padding:.85rem 1.9rem; font-weight:700; font-size:.95rem; backdrop-filter:blur(4px);
  }
  .btn-hero-outline:hover{ background:rgba(255,255,255,.22); color:#fff; }
  .hero-dots{ position:absolute; z-index:3; bottom:28px; left:0; right:0; display:flex; justify-content:center; gap:.5rem; }
  .hero-dots button{
    width:8px; height:8px; border-radius:50%; border:none; background:rgba(255,255,255,.45); padding:0;
  }
  .hero-dots button.active{ background:#fff; width:22px; border-radius:5px; transition:width .25s; }
  .scroll-cue{
    position:absolute; z-index:3; bottom:70px; left:50%; transform:translateX(-50%);
    color:#fff; opacity:.8; font-size:1.4rem; animation:bounce 1.8s infinite;
  }
  @keyframes bounce{ 0%,100%{ transform:translate(-50%,0);} 50%{ transform:translate(-50%,8px);} }

  /* ---------- Sections ---------- */
  section{ padding:5rem 0; scroll-margin-top:70px; }
  .section-eyebrow{
    text-transform:uppercase; letter-spacing:.2em; font-size:.72rem; font-weight:700;
    color:var(--accent-deep); margin-bottom:.6rem; text-align:center;
  }
  .section-title{ text-align:center; font-size:clamp(1.6rem,3vw,2.3rem); font-weight:700; margin-bottom:.75rem; }
  .section-sub{ text-align:center; color:var(--ink-soft); max-width:620px; margin:0 auto 3rem; }

  .about-media{ border-radius:24px; overflow:hidden; box-shadow:0 24px 48px rgba(43,50,66,.14); }
  .about-media img{ width:100%; height:100%; object-fit:cover; display:block; }
  .about-points{ list-style:none; padding:0; margin:1.5rem 0 0; }
  .about-points li{ display:flex; gap:.75rem; margin-bottom:1rem; align-items:flex-start; }
  .about-points .ap-ic{
    width:36px; height:36px; border-radius:10px; background:var(--accent-soft); color:var(--accent-deep);
    display:flex; align-items:center; justify-content:center; flex:0 0 auto; font-size:1rem;
  }

  .branch-card{
    background:var(--panel); border:1px solid var(--line); border-radius:20px; padding:1.5rem;
    height:100%; transition:transform .2s, box-shadow .2s;
  }
  .branch-card:hover{ transform:translateY(-4px); box-shadow:0 16px 32px rgba(43,50,66,.08); }
  .branch-card .bc-ic{
    width:48px; height:48px; border-radius:14px; background:var(--accent-soft); color:var(--accent-deep);
    display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-bottom:1rem;
  }
  .branch-card h3{ font-size:1.15rem; margin:0 0 .4rem; }
  .branch-card p{ color:var(--ink-soft); font-size:.88rem; margin:0 0 1rem; }
  .branch-card a{ font-size:.85rem; font-weight:700; color:var(--accent-deep); text-decoration:none; }

  .pkg-card{
    background:var(--panel); border:1px solid var(--line); border-radius:20px; padding:1.75rem;
    height:100%; position:relative; transition:transform .2s, box-shadow .2s;
  }
  .pkg-card:hover{ transform:translateY(-4px); box-shadow:0 16px 32px rgba(43,50,66,.08); }
  .pkg-card.featured{ border-color:var(--accent); box-shadow:0 16px 32px rgba(124,147,184,.18); }
  .pkg-badge{
    position:absolute; top:-12px; left:1.75rem; background:var(--accent); color:#fff;
    font-size:.68rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase;
    padding:.3rem .8rem; border-radius:999px;
  }
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

  .trainer-card{ text-align:center; }
  .trainer-avatar{
    width:120px; height:120px; border-radius:50%; margin:0 auto 1rem; overflow:hidden;
    background:var(--sage-soft); display:flex; align-items:center; justify-content:center;
    color:var(--sage); font-size:2.5rem; border:3px solid var(--panel); box-shadow:0 8px 20px rgba(43,50,66,.1);
  }
  .trainer-avatar img{ width:100%; height:100%; object-fit:cover; }
  .trainer-card h3{ font-size:1rem; margin:0 0 .2rem; }
  .trainer-card p{ font-size:.8rem; color:var(--ink-soft); margin:0; }

  .article-card{
    background:var(--panel); border:1px solid var(--line); border-radius:18px; overflow:hidden;
    height:100%; transition:transform .2s, box-shadow .2s;
  }
  .article-card:hover{ transform:translateY(-4px); box-shadow:0 16px 32px rgba(43,50,66,.08); }
  .article-card .ac-body{ padding:1.4rem; }
  .article-card .ac-thumb{ height:170px; background-size:cover; background-position:center; }
  .article-card .ac-date{ font-size:.72rem; color:var(--accent-deep); font-weight:700; text-transform:uppercase; letter-spacing:.06em; }
  .article-card h3{ font-size:1.05rem; margin:.5rem 0 .5rem; color:var(--ink); }
  .article-card p{ font-size:.85rem; color:var(--ink-soft); margin:0; }
  .article-card .ac-readmore{ display:inline-block; font-size:.82rem; font-weight:700; color:var(--accent-deep); margin-top:.9rem; }

  .faq-item{ border-bottom:1px solid var(--line); padding:1.25rem 0; }
  .faq-item summary{ cursor:pointer; font-weight:700; font-size:.98rem; list-style:none; display:flex; justify-content:space-between; align-items:center; gap:1rem; }
  .faq-item summary::-webkit-details-marker{ display:none; }
  .faq-item summary .faq-chevron{ transition:transform .2s; color:var(--accent-deep); flex:0 0 auto; }
  .faq-item[open] summary .faq-chevron{ transform:rotate(180deg); }
  .faq-item .faq-answer{ color:var(--ink-soft); font-size:.9rem; margin-top:.75rem; line-height:1.6; }

  .cta-band{
    background:linear-gradient(155deg,var(--accent-deep),var(--accent));
    border-radius:28px; padding:3.5rem 2rem; text-align:center; color:#fff;
    margin:0 1rem;
  }
  .cta-band h2{ color:#fff; }
  .cta-band p{ opacity:.9; max-width:520px; margin:0 auto 1.75rem; }

  .site-footer{ background:#1E2432; color:#B9C2D6; padding:3rem 0 1.5rem; margin-top:2rem; }
  .site-footer h4{ color:#fff; font-size:.95rem; margin-bottom:1rem; }
  .site-footer a{ color:#B9C2D6; text-decoration:none; font-size:.85rem; display:block; margin-bottom:.6rem; }
  .site-footer a:hover{ color:#fff; }
  .site-footer .fbottom{ border-top:1px solid rgba(255,255,255,.1); margin-top:2rem; padding-top:1.5rem; text-align:center; font-size:.78rem; color:#8B96AC; }

  [data-i18n]{ }
</style>
</head>
<body>

<nav class="site-nav">
  <div class="container-lg d-flex align-items-center justify-content-between">
    <a href="{{ route('landing') }}" class="brand text-decoration-none">
      <img src="{{ asset('images/logo.jpg') }}" alt="Drip Pilates Club">
      <span>Drip Pilates Club</span>
    </a>
    <div class="nav-links" id="navLinks">
      <a href="#about">{{ __t('เกี่ยวกับเรา', 'About') }}</a>
      <a href="#branches">{{ __t('สาขา', 'Branches') }}</a>
      <a href="#packages">{{ __t('แพ็กเกจ', 'Packages') }}</a>
      <a href="#trainers">{{ __t('ครูผู้สอน', 'Trainers') }}</a>
      <a href="#articles">{{ __t('บทความ', 'Articles') }}</a>
      <a href="#faq">{{ __t('คำถามที่พบบ่อย', 'FAQ') }}</a>
      <a href="{{ route('locale.set', app()->getLocale() === 'th' ? 'en' : 'th') }}" class="lang-switch d-inline-flex align-items-center gap-1">
        <img src="{{ asset('images/' . (app()->getLocale() === 'th' ? 'en' : 'th') . '.png') }}" width="16" height="16" style="border-radius:50%;object-fit:cover;" alt="">
        {{ app()->getLocale() === 'th' ? 'EN' : 'TH' }}
      </a>
      <a href="{{ route('customer.login') }}" class="btn-nav-cta">{{ __t('เข้าสู่ระบบ', 'Log In') }}</a>
    </div>
    <button class="nav-toggle" id="navToggle" type="button" aria-label="Menu"><i class="bi bi-list fs-4"></i></button>
  </div>
</nav>

<!-- HERO -->
<header class="hero">
  @foreach(['01','02','03','04','05','06'] as $i => $img)
    <div class="hero-slide {{ $i === 0 ? 'active' : '' }}" style="background-image:url('{{ asset('images/' . $img . '.jpg') }}');"></div>
  @endforeach
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="eyebrow">Drip Pilates Club</div>
    <h1>{{ __t('เคลื่อนไหวอย่างมีสติ ฟื้นฟูร่างกายและจิตใจ', 'Move With Intention. Restore Body and Mind.') }}</h1>
    <p>{{ __t('สตูดิโอพิลาทิสบรรยากาศอบอุ่น พร้อมครูผู้สอนมืออาชีพ จองคลาสง่ายในไม่กี่คลิก', 'A warm, welcoming Pilates studio with professional instructors. Book your class in just a few clicks.') }}</p>
    <div class="hero-ctas">
      <a href="{{ route('customer.register') }}" class="btn-hero-primary">{{ __t('เริ่มต้นใช้งาน', 'Get Started') }}</a>
      <a href="#packages" class="btn-hero-outline">{{ __t('ดูแพ็กเกจ', 'View Packages') }}</a>
    </div>
  </div>
  <div class="hero-dots" id="heroDots"></div>
  <div class="scroll-cue"><i class="bi bi-chevron-down"></i></div>
</header>

<!-- ABOUT -->
<section id="about">
  <div class="container-lg">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="about-media"><img src="{{ asset('images/02.jpg') }}" alt="Drip Pilates Club studio"></div>
      </div>
      <div class="col-lg-6">
        <div class="section-eyebrow" style="text-align:left;">{{ __t('เกี่ยวกับเรา', 'About Us') }}</div>
        <h2 style="text-align:left;">{{ __t('พื้นที่ปลอดภัยสำหรับทุกระดับ', 'A Safe Space For Every Level') }}</h2>
        <p class="text-secondary">{{ __t('ไม่ว่าคุณจะเพิ่งเริ่มต้นหรือฝึกมานาน Drip Pilates Club ออกแบบคลาสให้เหมาะกับร่างกายของคุณ ด้วยอุปกรณ์คุณภาพและครูผู้สอนที่ผ่านการรับรอง', 'Whether you\'re just starting out or an experienced practitioner, Drip Pilates Club tailors every class to your body — with quality equipment and certified instructors.') }}</p>
        <ul class="about-points">
          <li>
            <span class="ap-ic"><i class="bi bi-award"></i></span>
            <div>
              <strong>{{ __t('ครูผู้สอนที่ผ่านการรับรอง', 'Certified Instructors') }}</strong>
              <div class="small text-secondary">{{ __t('ทีมครูมืออาชีพ ดูแลท่าทางอย่างใกล้ชิด', 'A professional team giving close, attentive guidance.') }}</div>
            </div>
          </li>
          <li>
            <span class="ap-ic"><i class="bi bi-geo-alt"></i></span>
            <div>
              <strong>{{ __t('หลายสาขาทั่วกรุงเทพฯ', 'Multiple Branches Across Bangkok') }}</strong>
              <div class="small text-secondary">{{ __t('เลือกสาขาที่สะดวก จองคลาสข้ามสาขาได้', 'Pick the branch that\'s convenient — book across branches freely.') }}</div>
            </div>
          </li>
          <li>
            <span class="ap-ic"><i class="bi bi-phone"></i></span>
            <div>
              <strong>{{ __t('จองคลาสง่ายผ่านมือถือ', 'Book Anytime, Anywhere') }}</strong>
              <div class="small text-secondary">{{ __t('ระบบจองออนไลน์ ใช้งานง่ายเหมือนแอป', 'A simple online booking system that feels just like an app.') }}</div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- BRANCHES -->
@if($branches->isNotEmpty())
<section id="branches" style="background:var(--panel);">
  <div class="container-lg">
    <div class="section-eyebrow">{{ __t('สถานที่', 'Locations') }}</div>
    <h2 class="section-title">{{ __t('สาขาของเรา', 'Our Branches') }}</h2>
    <p class="section-sub">{{ __t('เลือกสาขาที่ใกล้คุณที่สุด', 'Choose the branch nearest to you') }}</p>
    <div class="row g-4 {{ $branches->count() <= 2 ? 'justify-content-center' : '' }}">
      @foreach($branches as $branch)
        <div class="col-md-6 col-lg-4">
          <div class="branch-card">
            <div class="bc-ic"><i class="bi bi-geo-alt-fill"></i></div>
            <h3>{{ $branch->name }}</h3>
            <p>{{ $branch->address }}</p>
            @if($branch->phone)
              <div class="small text-secondary mb-2"><i class="bi bi-telephone"></i> {{ $branch->phone }}</div>
            @endif
            @if($branch->google_map_url)
              <a href="{{ $branch->google_map_url }}" target="_blank" rel="noopener">{{ __t('เปิดแผนที่', 'Open Map') }} <i class="bi bi-arrow-up-right"></i></a>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- PACKAGES -->
@if($packages->isNotEmpty())
<section id="packages">
  <div class="container-lg">
    <div class="section-eyebrow">{{ __t('โปรโมชั่น', 'Promotions') }}</div>
    <h2 class="section-title">{{ __t('แพ็กเกจยอดนิยม', 'Popular Packages') }}</h2>
    <p class="section-sub">{{ __t('เลือกแพ็กเกจที่เหมาะกับไลฟ์สไตล์คุณ', 'Choose the package that fits your lifestyle') }}</p>
    <div class="row g-4">
      @foreach($packages as $i => $pkg)
        <div class="col-md-6 col-lg-4">
          <div class="pkg-card {{ $i === 1 ? 'featured' : '' }}">
            @if($i === 1)<span class="pkg-badge">{{ __t('ยอดนิยม', 'Best Value') }}</span>@endif
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
</section>
@endif

<!-- TRAINERS -->
@if($trainers->isNotEmpty())
<section id="trainers" style="background:var(--panel);">
  <div class="container-lg">
    <div class="section-eyebrow">{{ __t('ทีมงาน', 'Our Team') }}</div>
    <h2 class="section-title">{{ __t('ครูผู้สอน', 'Meet Our Trainers') }}</h2>
    <p class="section-sub">{{ __t('ทีมครูมืออาชีพพร้อมดูแลคุณทุกคลาส', 'A professional team ready to guide every class') }}</p>
    <div class="row row-cols-2 row-cols-md-4 g-4">
      @foreach($trainers as $trainer)
        <div class="col">
          <div class="trainer-card">
            <div class="trainer-avatar">
              @if($trainer->avatar)<img src="{{ asset($trainer->avatar) }}" alt="{{ $trainer->name }}">@else<i class="bi bi-person"></i>@endif
            </div>
            <h3>{{ $trainer->nickname ?: $trainer->name }}</h3>
            @if($trainer->specialties)<p>{{ $trainer->specialties }}</p>@endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- ARTICLES / NEWS -->
@if($announcements->isNotEmpty())
<section id="articles">
  <div class="container-lg">
    <div class="section-eyebrow">{{ __t('ข่าวสาร', 'News') }}</div>
    <h2 class="section-title">{{ __t('บทความและข่าวสาร', 'Articles & News') }}</h2>
    <p class="section-sub">{{ __t('อัปเดตล่าสุดจาก Drip Pilates Club', 'The latest updates from Drip Pilates Club') }}</p>
    <div class="row g-4">
      @foreach($announcements as $ann)
        <div class="col-md-6 col-lg-4">
          <a href="{{ route('articles.show', $ann) }}" class="article-card text-decoration-none d-block">
            <div class="ac-thumb" style="background-image:url('{{ asset($ann->image ?: 'images/01.jpg') }}');"></div>
            <div class="ac-body">
              @if($ann->starts_at)
                <div class="ac-date">{{ $ann->starts_at->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}</div>
              @endif
              <h3>{{ $ann->title }}</h3>
              @if($ann->body)<p>{{ \Illuminate\Support\Str::limit($ann->body, 140) }}</p>@endif
              <span class="ac-readmore">{{ __t('อ่านเพิ่มเติม', 'Read more') }} <i class="bi bi-arrow-right"></i></span>
            </div>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- FAQ -->
<section id="faq" style="background:var(--panel);">
  <div class="container-lg" style="max-width:760px;">
    <div class="section-eyebrow">{{ __t('ช่วยเหลือ', 'Help') }}</div>
    <h2 class="section-title">{{ __t('คำถามที่พบบ่อย', 'Frequently Asked Questions') }}</h2>
    <div class="mt-4">
      @php
        $faqs = [
          [
            'q' => __t('ต้องสมัครสมาชิกก่อนจองคลาสไหม?', 'Do I need to sign up before booking a class?'),
            'a' => __t('ใช่ คุณต้องสมัครสมาชิกและมีแพ็กเกจหรือเครดิตคงเหลือก่อนจึงจะจองคลาสได้', 'Yes, you need to create an account and have an active package or credit before booking a class.'),
          ],
          [
            'q' => __t('ยกเลิกคลาสที่จองไว้ได้ไหม?', 'Can I cancel a class I booked?'),
            'a' => __t('ยกเลิกได้ก่อนเวลาที่กำหนดเพื่อรับเครดิตคืน หากยกเลิกช้ากว่ากำหนดจะไม่ได้รับเครดิตคืน', 'Yes, cancel before the deadline to get your credit refunded. Late cancellations forfeit the credit.'),
          ],
          [
            'q' => __t('ซื้อแพ็กเกจได้ที่ไหน?', 'Where can I purchase a package?'),
            'a' => __t('สมัครสมาชิกแล้วติดต่อเจ้าหน้าที่ที่สาขาเพื่อซื้อแพ็กเกจที่ต้องการ', 'Sign up, then contact our staff at the studio to purchase the package you want.'),
          ],
          [
            'q' => __t('ใช้แพ็กเกจข้ามสาขาได้ไหม?', 'Can I use my package at different branches?'),
            'a' => __t('แพ็กเกจส่วนใหญ่ใช้ได้ทุกสาขา ยกเว้นระบุไว้เฉพาะสาขา', 'Most packages work across all branches, unless a package is limited to a specific branch.'),
          ],
        ];
      @endphp
      @foreach($faqs as $faq)
        <details class="faq-item" @if($loop->first) open @endif>
          <summary>{{ $faq['q'] }} <span class="faq-chevron"><i class="bi bi-chevron-down"></i></span></summary>
          <div class="faq-answer">{{ $faq['a'] }}</div>
        </details>
      @endforeach
    </div>
  </div>
</section>

<!-- CTA -->
<section>
  <div class="container-lg">
    <div class="cta-band">
      <h2>{{ __t('พร้อมเริ่มต้นแล้วหรือยัง?', 'Ready To Get Started?') }}</h2>
      <p>{{ __t('สมัครสมาชิกวันนี้ แล้วเริ่มจองคลาสพิลาทิสได้เลย', 'Sign up today and start booking your Pilates classes right away.') }}</p>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="{{ route('customer.register') }}" class="btn-hero-primary" style="background:#fff;color:var(--accent-deep);border-color:#fff;">{{ __t('สมัครสมาชิก', 'Sign Up') }}</a>
        <a href="{{ route('customer.login') }}" class="btn-hero-outline">{{ __t('เข้าสู่ระบบ', 'Log In') }}</a>
      </div>
    </div>
  </div>
</section>

<footer class="site-footer">
  <div class="container-lg">
    <div class="row g-4">
      <div class="col-md-4">
        <h4>Drip Pilates Club</h4>
        <p class="small" style="color:#8B96AC;">{{ __t('สตูดิโอพิลาทิสบรรยากาศอบอุ่น สำหรับทุกคน', 'A warm Pilates studio for everyone.') }}</p>
      </div>
      <div class="col-md-4">
        <h4>{{ __t('ลิงก์ด่วน', 'Quick Links') }}</h4>
        <a href="#about">{{ __t('เกี่ยวกับเรา', 'About') }}</a>
        <a href="#branches">{{ __t('สาขา', 'Branches') }}</a>
        <a href="#packages">{{ __t('แพ็กเกจ', 'Packages') }}</a>
        <a href="#faq">{{ __t('คำถามที่พบบ่อย', 'FAQ') }}</a>
      </div>
      <div class="col-md-4">
        <h4>{{ __t('บัญชี', 'Account') }}</h4>
        <a href="{{ route('customer.login') }}">{{ __t('เข้าสู่ระบบ', 'Log In') }}</a>
        <a href="{{ route('customer.register') }}">{{ __t('สมัครสมาชิก', 'Sign Up') }}</a>
      </div>
    </div>
    <div class="fbottom">&copy; {{ date('Y') }} Drip Pilates Club. {{ __t('สงวนลิขสิทธิ์', 'All rights reserved.') }}</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// สไลด์โชว์รูปหน้า hero สลับอัตโนมัติ
(function(){
  var slides = document.querySelectorAll('.hero-slide');
  var dotsWrap = document.getElementById('heroDots');
  var idx = 0;

  slides.forEach(function(_, i){
    var dot = document.createElement('button');
    dot.type = 'button';
    if (i === 0) dot.className = 'active';
    dot.addEventListener('click', function(){ goTo(i); });
    dotsWrap.appendChild(dot);
  });

  function goTo(i){
    slides[idx].classList.remove('active');
    dotsWrap.children[idx].classList.remove('active');
    idx = i;
    slides[idx].classList.add('active');
    dotsWrap.children[idx].classList.add('active');
  }

  setInterval(function(){ goTo((idx + 1) % slides.length); }, 5000);
})();

// เมนูมือถือ
var navToggle = document.getElementById('navToggle');
var navLinks = document.getElementById('navLinks');
navToggle.addEventListener('click', function(){
  navLinks.classList.toggle('open');
});
navLinks.querySelectorAll('a').forEach(function(a){
  a.addEventListener('click', function(){ navLinks.classList.remove('open'); });
});
</script>
</body>
</html>

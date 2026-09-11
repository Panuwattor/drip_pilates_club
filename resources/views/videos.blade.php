<!doctype html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __t('วิดีโอทั้งหมด', 'All Videos') }} · Drip Pilates Club</title>
<meta name="description" content="{{ __t('รวมคลิปคลาสและเทคนิคพิลาทิสจาก Drip Pilates Club', 'All Pilates class clips and techniques from Drip Pilates Club.') }}">
<link rel="canonical" href="{{ route('videos.index') }}">
<link rel="alternate" hreflang="th" href="{{ route('videos.index') }}">
<link rel="alternate" hreflang="en" href="{{ route('videos.index') }}">
<link rel="alternate" hreflang="x-default" href="{{ route('videos.index') }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="Drip Pilates Club">
<meta property="og:title" content="{{ __t('วิดีโอทั้งหมด', 'All Videos') }} · Drip Pilates Club">
<meta property="og:description" content="{{ __t('รวมคลิปคลาสและเทคนิคพิลาทิสจาก Drip Pilates Club', 'All Pilates class clips and techniques from Drip Pilates Club.') }}">
<meta property="og:image" content="{{ asset('images/01.jpg') }}">
<meta property="og:url" content="{{ route('videos.index') }}">
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

@if($videos->isNotEmpty())
{{-- VideoObject list ช่วยให้คลิปมีสิทธิ์ขึ้น rich result / video carousel บน Google --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'itemListElement' => $videos->values()->map(fn ($v, $i) => [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'item' => array_filter([
            '@type' => 'VideoObject',
            'name' => $v->title ?: 'Drip Pilates Club',
            'description' => $v->caption ?: __t('คลิปจาก Drip Pilates Club', 'A clip from Drip Pilates Club'),
            'thumbnailUrl' => $v->thumbnail ? asset($v->thumbnail) : asset('images/01.jpg'),
            'uploadDate' => $v->created_at?->toIso8601String(),
            'contentUrl' => $v->url,
            'embedUrl' => $v->url,
        ]),
    ])->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif

<style>
  :root{
    --ground:#EEF1F6; --panel:#FFFFFF; --ink:#2B3242; --ink-soft:#6B7690;
    --accent:#7C93B8; --accent-deep:#5E7699; --accent-soft:#DCE3EF;
    --line:#DCE1EB;
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

  .page-head{ padding:3rem 0 1.5rem; text-align:center; }
  .page-head .breadcrumb-link{ font-size:.85rem; font-weight:700; color:var(--accent-deep); text-decoration:none; }
  .page-head .eyebrow{ text-transform:uppercase; letter-spacing:.2em; font-size:.72rem; font-weight:700; color:var(--accent-deep); margin:1.5rem 0 .5rem; }
  .page-head h1{ font-size:clamp(1.7rem,3.4vw,2.5rem); font-weight:700; margin:0; }
  .page-head p{ color:var(--ink-soft); max-width:620px; margin:.75rem auto 0; }

  .video-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:1.5rem; align-items:start; }

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
    <a href="{{ route('customer.login') }}" style="background:var(--accent);border:1px solid var(--accent);color:#fff;border-radius:999px;padding:.5rem 1.2rem;font-weight:700;font-size:.9rem;text-decoration:none;">
      {{ __t('เข้าสู่ระบบ', 'Log In') }}
    </a>
  </div>
</nav>

<div class="container-lg">
  <div class="page-head">
    <a href="{{ route('landing') }}#videos" class="breadcrumb-link"><i class="bi bi-arrow-left"></i> {{ __t('กลับหน้าแรก', 'Back to Home') }}</a>
    <div class="eyebrow">{{ __t('วิดีโอ', 'Watch') }}</div>
    <h1>{{ __t('คลิปทั้งหมดจากสตูดิโอ', 'All Videos From Our Studio') }}</h1>
    <p>{{ __t('ชมบรรยากาศคลาสและเทคนิคพิลาทิสจากเรา', 'A look inside our classes and Pilates techniques') }}</p>
  </div>

  @if($videos->isEmpty())
    <div class="empty-note"><i class="bi bi-play-btn fs-1 d-block mb-2"></i>{{ __t('ยังไม่มีคลิป', 'No videos yet') }}</div>
  @else
    <div class="video-grid mb-4">
      @foreach($videos as $video)
        @include('partials.video-embed', ['video' => $video])
      @endforeach
    </div>
  @endif
</div>

<footer class="site-footer">
  <div class="container-lg">
    <div class="fbottom">&copy; {{ date('Y') }} Drip Pilates Club. {{ __t('สงวนลิขสิทธิ์', 'All rights reserved.') }}</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@if($videos->isNotEmpty())
  @include('partials.video-embed-assets')
@endif
</body>
</html>

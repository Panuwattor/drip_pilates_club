<!doctype html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $announcement->title }} · Drip Pilates Club</title>
<meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($announcement->body ?? $announcement->title), 160) }}">
<link rel="canonical" href="{{ route('articles.show', $announcement) }}">
<link rel="alternate" hreflang="th" href="{{ route('articles.show', $announcement) }}">
<link rel="alternate" hreflang="en" href="{{ route('articles.show', $announcement) }}">
<link rel="alternate" hreflang="x-default" href="{{ route('articles.show', $announcement) }}">

<meta property="og:type" content="article">
<meta property="og:site_name" content="Drip Pilates Club">
<meta property="og:title" content="{{ $announcement->title }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($announcement->body ?? $announcement->title), 160) }}">
<meta property="og:image" content="{{ asset($announcement->image ?: 'images/01.jpg') }}">
<meta property="og:url" content="{{ route('articles.show', $announcement) }}">
@if($announcement->starts_at)
<meta property="article:published_time" content="{{ $announcement->starts_at->toAtomString() }}">
@endif
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

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $announcement->title,
    'description' => \Illuminate\Support\Str::limit(strip_tags($announcement->body ?? $announcement->title), 160),
    'image' => asset($announcement->image ?: 'images/01.jpg'),
    'datePublished' => optional($announcement->starts_at)->toAtomString(),
    'author' => ['@type' => 'Organization', 'name' => 'Drip Pilates Club'],
    'publisher' => ['@type' => 'Organization', 'name' => 'Drip Pilates Club'],
    'mainEntityOfPage' => route('articles.show', $announcement),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

<style>
  :root{
    --ground:#EEF1F6; --panel:#FFFFFF; --ink:#2B3242; --ink-soft:#6B7690;
    --accent:#7C93B8; --accent-deep:#5E7699; --accent-soft:#DCE3EF;
    --sage:#8C9DBE; --sage-soft:#E4E9F2; --line:#DCE1EB;
  }
  html{ overflow-x:hidden; }
  body{
    background:var(--ground); color:var(--ink); margin:0;
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
    overflow-x:hidden;
  }
  h1,h2,h3,.display-serif{ font-family:'Playfair Display','Noto Sans Thai',Georgia,serif; }

  .site-nav{
    position:sticky; top:0; z-index:50;
    background:rgba(255,255,255,.9); backdrop-filter:blur(10px);
    border-bottom:1px solid var(--line); padding:.8rem 0;
  }
  .site-nav .brand{ font-weight:700; font-size:1.2rem; color:var(--ink); display:flex; align-items:center; gap:.5rem; text-decoration:none; }
  .site-nav .brand img{ width:36px; height:36px; border-radius:50%; object-fit:cover; }

  .article-header{ padding:3rem 0 2rem; }
  .article-header .breadcrumb-link{ font-size:.85rem; font-weight:700; color:var(--accent-deep); text-decoration:none; }
  .article-header .ac-date{ font-size:.75rem; color:var(--accent-deep); font-weight:700; text-transform:uppercase; letter-spacing:.06em; margin-top:1.25rem; }
  .article-header h1{ font-size:clamp(1.6rem,3.4vw,2.4rem); font-weight:700; margin:.5rem 0 0; max-width:820px; }

  .article-hero{ max-width:820px; border-radius:20px; overflow:hidden; box-shadow:0 16px 32px rgba(43,50,66,.1); }
  .article-hero img{ width:100%; max-height:420px; object-fit:cover; display:block; }

  .article-body{ background:var(--panel); border:1px solid var(--line); border-radius:20px; padding:2.25rem; max-width:820px; }
  .article-body .article-content{ color:var(--ink); font-size:1rem; line-height:1.85; }
  .article-body .article-content > *:first-child{ margin-top:0; }
  .article-body .article-content > *:last-child{ margin-bottom:0; }
  .article-body .article-content p{ margin:0 0 1rem; }
  .article-body .article-content h2{ font-size:1.35rem; margin:1.5rem 0 .75rem; }
  .article-body .article-content h3{ font-size:1.15rem; margin:1.25rem 0 .6rem; }
  .article-body .article-content ul, .article-body .article-content ol{ margin:0 0 1rem; padding-left:1.4rem; }
  .article-body .article-content blockquote{ border-left:3px solid var(--accent); margin:0 0 1rem; padding:.3rem 0 .3rem 1rem; color:var(--ink-soft); }
  .article-body .article-content a{ color:var(--accent-deep); }
  .article-body .btn-accent{
    display:inline-flex; align-items:center; gap:.4rem; margin-top:1.75rem;
    background:var(--accent); border:1px solid var(--accent); color:#fff;
    border-radius:999px; padding:.65rem 1.4rem; font-weight:700; text-decoration:none; font-size:.9rem;
  }
  .article-body .btn-accent:hover{ background:var(--accent-deep); color:#fff; }

  .related-card{
    background:var(--panel); border:1px solid var(--line); border-radius:18px; overflow:hidden;
    height:100%; display:block; text-decoration:none; transition:transform .2s, box-shadow .2s;
  }
  .related-card:hover{ transform:translateY(-4px); box-shadow:0 16px 32px rgba(43,50,66,.08); }
  .related-card .rc-thumb{ height:120px; background-size:cover; background-position:center; }
  .related-card .rc-body{ padding:1.25rem; }
  .related-card .ac-date{ font-size:.7rem; color:var(--accent-deep); font-weight:700; text-transform:uppercase; letter-spacing:.06em; }
  .related-card h3{ font-size:.98rem; margin:.4rem 0 0; color:var(--ink); }

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
    <a href="{{ route('customer.login') }}" class="btn-nav-cta" style="background:var(--accent);border:1px solid var(--accent);color:#fff;border-radius:999px;padding:.5rem 1.2rem;font-weight:700;font-size:.9rem;text-decoration:none;">
      {{ __t('เข้าสู่ระบบ', 'Log In') }}
    </a>
  </div>
</nav>

<div class="container-lg">
  <div class="article-header">
    <a href="{{ route('landing') }}#articles" class="breadcrumb-link"><i class="bi bi-arrow-left"></i> {{ __t('กลับไปหน้าข่าวสาร', 'Back to Articles') }}</a>
    @if($announcement->starts_at)
      <div class="ac-date">{{ $announcement->starts_at->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}</div>
    @endif
    <h1>{{ $announcement->title }}</h1>
  </div>

  @if($announcement->image)
    <div class="article-hero mb-4">
      <img src="{{ asset($announcement->image) }}" alt="{{ $announcement->title }}">
    </div>
  @endif

  <div class="article-body mb-5">
    @if($announcement->body)
      <div class="article-content">{!! $announcement->body !!}</div>
    @endif
    @if($announcement->link_url)
      <a href="{{ $announcement->link_url }}" class="btn-accent" target="_blank" rel="noopener noreferrer">
        {{ __t('ดูเพิ่มเติม', 'Learn more') }} <i class="bi bi-box-arrow-up-right"></i>
      </a>
    @endif
  </div>

  @if($related->isNotEmpty())
    <div class="mb-5">
      <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:1rem;">{{ __t('บทความอื่นๆ', 'More Articles') }}</h2>
      <div class="row g-3">
        @foreach($related as $r)
          <div class="col-md-4">
            <a href="{{ route('articles.show', $r) }}" class="related-card">
              <div class="rc-thumb" style="background-image:url('{{ asset($r->image ?: 'images/01.jpg') }}');"></div>
              <div class="rc-body">
                @if($r->starts_at)
                  <div class="ac-date">{{ $r->starts_at->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}</div>
                @endif
                <h3>{{ $r->title }}</h3>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>

<footer class="site-footer">
  <div class="container-lg">
    <div class="fbottom">&copy; {{ date('Y') }} Drip Pilates Club. {{ __t('สงวนลิขสิทธิ์', 'All rights reserved.') }}</div>
  </div>
</footer>

</body>
</html>

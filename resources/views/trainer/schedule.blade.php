<!doctype html>
<html lang="th" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
{{-- ลิงก์นี้เปิดดูได้โดยไม่ต้องล็อกอิน กัน search engine เก็บไว้ --}}
<meta name="robots" content="noindex, nofollow, noarchive">
<title>ตารางสอน · {{ $trainer->name_th }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root{
    --ground:#EEF1F6; --panel:#FFFFFF; --ink:#2B3242; --ink-soft:#6B7690;
    --accent:#7C93B8; --accent-deep:#5E7699; --accent-soft:#DCE3EF;
    --sage:#8C9DBE; --sage-soft:#E4E9F2; --line:#DCE1EB;
  }
  [data-bs-theme="dark"]{
    --ground:#161A22; --panel:#20252F; --ink:#E9ECF3; --ink-soft:#A0ABC2;
    --accent:#9BB0D1; --accent-deep:#B7C6E2; --accent-soft:#2C3547;
    --sage:#8FA3C4; --sage-soft:#2A3243; --line:#333B4C;
  }
  body{
    background:var(--ground); color:var(--ink); padding-bottom:2rem;
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
  }
  .t-header{
    background:var(--panel); border-bottom:1px solid var(--line);
    padding:1.1rem 0; margin-bottom:1.25rem;
  }
  .t-avatar{
    width:52px; height:52px; border-radius:50%; object-fit:cover;
    border:1px solid var(--line); flex:0 0 auto;
  }
  .t-name{ font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-weight:700; font-size:1.2rem; }
  .card-panel{ background:var(--panel); border:1px solid var(--line); border-radius:16px; padding:1rem; }
  .day-head{
    font-size:.7rem; letter-spacing:.1em; text-transform:uppercase;
    color:var(--ink-soft); font-weight:700; margin:1.25rem 0 .5rem;
  }
  .sess-card{
    display:flex; border:1px solid var(--line); border-radius:14px; overflow:hidden;
    background:var(--panel); margin-bottom:.6rem; text-decoration:none; color:inherit;
  }
  .sess-card:hover{ border-color:var(--accent); color:inherit; }
  .sess-rail{
    width:74px; flex:none; background:var(--accent-soft); color:var(--accent-deep);
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    font-weight:700; font-variant-numeric:tabular-nums; padding:.5rem;
  }
  .sess-rail small{ font-weight:500; font-size:.65rem; opacity:.8; }
  .sess-body{ padding:.7rem .9rem; flex:1; min-width:0; }
  .sess-body h4{ font-size:.95rem; font-weight:700; margin:0; }
  .sess-meta{ font-size:.78rem; color:var(--ink-soft); margin:2px 0 0; }
  .badge-soft{
    background:var(--sage-soft); color:var(--sage); border-radius:999px;
    font-size:.7rem; font-weight:700; padding:.2rem .55rem;
  }
  .badge-warn{ background:#F4E3C7; color:#8A6112; }
  .badge-full{ background:var(--accent-soft); color:var(--accent-deep); }
  .stat-tile{ background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:.9rem; text-align:center; }
  .stat-tile .num{ font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif; font-size:1.5rem; line-height:1; font-variant-numeric:tabular-nums; }
  .stat-tile .lbl{ font-size:.68rem; color:var(--ink-soft); margin-top:3px; }
  .nav-pill{
    background:var(--panel); border:1px solid var(--line); color:var(--ink-soft);
    border-radius:999px; font-size:.78rem; font-weight:600; padding:.35rem .9rem; text-decoration:none;
  }
  .nav-pill.active{ background:var(--accent); border-color:var(--accent); color:#FBF3F0; }
  .empty-note{
    text-align:center; color:var(--ink-soft); font-size:.85rem;
    border:1px dashed var(--line); border-radius:14px; padding:2.5rem 1rem;
  }
</style>
</head>
<body>

<div class="t-header">
  <div class="container" style="max-width:840px;">
    <div class="d-flex align-items-center gap-3">
      @if($trainer->avatar)
        <img src="{{ asset($trainer->avatar) }}" alt="" class="t-avatar">
      @else
        <div class="t-avatar d-flex align-items-center justify-content-center" style="background:var(--sage-soft);color:var(--sage);">
          <i class="bi bi-person"></i>
        </div>
      @endif
      <div>
        <div class="t-name">{{ $trainer->name_th }}</div>
        <div class="small" style="color:var(--ink-soft);">ตารางสอนของคุณ</div>
      </div>
      <button class="btn btn-sm ms-auto" id="themeToggle" type="button"
              style="background:var(--ground);border:1px solid var(--line);color:var(--ink-soft);border-radius:999px;">
        <i class="bi bi-circle-half"></i>
      </button>
    </div>
  </div>
</div>

<div class="container" style="max-width:840px;">

  <div class="row g-2 mb-3">
    <div class="col-6">
      <div class="stat-tile">
        <div class="num">{{ $totalSessions }}</div>
        <div class="lbl">คลาสในช่วงนี้</div>
      </div>
    </div>
    <div class="col-6">
      <div class="stat-tile">
        <div class="num">{{ $totalStudents }}</div>
        <div class="lbl">ผู้เรียนรวม</div>
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
    @foreach(['day' => 'วันนี้', 'week' => 'สัปดาห์นี้', 'month' => 'เดือนนี้'] as $v => $label)
      <a href="{{ route('trainer.schedule', ['token' => $trainer->public_token, 'view' => $v, 'date' => $date->toDateString()]) }}"
         class="nav-pill {{ $view === $v ? 'active' : '' }}">{{ $label }}</a>
    @endforeach

    <div class="ms-auto d-flex gap-1">
      @php
        $step = $view === 'month' ? '1 month' : ($view === 'day' ? '1 day' : '1 week');
        $prev = $date->copy()->modify('-' . $step)->toDateString();
        $next = $date->copy()->modify('+' . $step)->toDateString();
      @endphp
      <a href="{{ route('trainer.schedule', ['token' => $trainer->public_token, 'view' => $view, 'date' => $prev]) }}"
         class="nav-pill"><i class="bi bi-chevron-left"></i></a>
      <a href="{{ route('trainer.schedule', ['token' => $trainer->public_token, 'view' => $view, 'date' => now()->toDateString()]) }}"
         class="nav-pill">วันนี้</a>
      <a href="{{ route('trainer.schedule', ['token' => $trainer->public_token, 'view' => $view, 'date' => $next]) }}"
         class="nav-pill"><i class="bi bi-chevron-right"></i></a>
    </div>
  </div>

  <div class="small mb-3" style="color:var(--ink-soft);">
    {{ $start->locale('th')->isoFormat('D MMM YYYY') }} – {{ $end->locale('th')->isoFormat('D MMM YYYY') }}
  </div>

  @if($sessions->isEmpty())
    <div class="empty-note">ไม่มีคลาสในช่วงเวลานี้</div>
  @else
    @foreach($sessions as $day => $daySessions)
      <div class="day-head">
        {{ \Carbon\Carbon::parse($day)->locale('th')->isoFormat('dddd D MMMM') }}
        @if(\Carbon\Carbon::parse($day)->isToday())
          <span class="badge-soft badge-warn">วันนี้</span>
        @endif
      </div>

      @foreach($daySessions as $s)
        <a class="sess-card" href="{{ route('trainer.session', ['token' => $trainer->public_token, 'session' => $s->id]) }}">
          <div class="sess-rail">
            {{ $s->start_at->format('H:i') }}
            <small>{{ (int) $s->start_at->diffInMinutes($s->end_at) }} นาที</small>
          </div>
          <div class="sess-body">
            <div class="d-flex align-items-start gap-2">
              <div class="flex-grow-1 min-width-0">
                <h4>{{ $s->classType->name_th }}</h4>
                <p class="sess-meta">
                  {{ $s->branch->name_th }}
                  @if($s->room) · {{ $s->room->name_th }} @endif
                </p>
              </div>
              <div class="text-end">
                <span class="badge-soft {{ $s->booked_count >= $s->capacity ? 'badge-full' : '' }}">
                  {{ $s->booked_count }}/{{ $s->capacity }}
                </span>
                @if($s->substitute_trainer_id === $trainer->id)
                  <div class="mt-1"><span class="badge-soft badge-warn">สอนแทน</span></div>
                @endif
              </div>
            </div>
          </div>
        </a>
      @endforeach
    @endforeach
  @endif

  <div class="text-center small mt-4" style="color:var(--ink-soft);">
    ลิงก์นี้เป็นข้อมูลส่วนตัว กรุณาอย่าเผยแพร่ต่อ
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  var saved = localStorage.getItem('trainerTheme');
  if(saved){ document.documentElement.setAttribute('data-bs-theme', saved); }
  document.getElementById('themeToggle').addEventListener('click', function(){
    var html = document.documentElement;
    var next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-bs-theme', next);
    localStorage.setItem('trainerTheme', next);
  });
})();
</script>
</body>
</html>

<!doctype html>
<html lang="th" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>{{ $session->classType->name_th }} · รายชื่อผู้เรียน</title>
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
    background:var(--ground); color:var(--ink); padding:1.25rem 0 2rem;
    font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
  }
  .card-panel{ background:var(--panel); border:1px solid var(--line); border-radius:16px; padding:1.1rem; }
  .ttl{
    font-size:.68rem; letter-spacing:.1em; text-transform:uppercase;
    color:var(--ink-soft); font-weight:700; margin-bottom:.75rem;
  }
  .student-row{
    display:flex; align-items:flex-start; gap:.75rem;
    padding:.75rem 0; border-bottom:1px solid var(--line);
  }
  .student-row:last-child{ border-bottom:none; }
  .student-num{
    width:26px; height:26px; border-radius:50%; background:var(--accent-soft); color:var(--accent-deep);
    display:flex; align-items:center; justify-content:center;
    font-size:.75rem; font-weight:700; flex:0 0 auto;
  }
  .medical{
    background:#F4E3C7; color:#8A6112; border-radius:8px;
    padding:.4rem .6rem; font-size:.78rem; margin-top:.35rem;
  }
  .badge-soft{
    background:var(--sage-soft); color:var(--sage); border-radius:999px;
    font-size:.7rem; font-weight:700; padding:.2rem .55rem;
  }
  .badge-warn{ background:#F4E3C7; color:#8A6112; }
  .badge-ok{ background:#D8ECD9; color:#2F6B33; }
  .empty-note{
    text-align:center; color:var(--ink-soft); font-size:.85rem;
    border:1px dashed var(--line); border-radius:14px; padding:2.5rem 1rem;
  }
  a.back{ color:var(--ink-soft); text-decoration:none; font-size:.85rem; }
</style>
</head>
<body>

<div class="container" style="max-width:720px;">
  <a class="back d-inline-block mb-3" href="{{ route('trainer.schedule', ['token' => $trainer->public_token, 'date' => $session->start_at->toDateString()]) }}">
    <i class="bi bi-arrow-left"></i> กลับไปตารางสอน
  </a>

  <div class="card-panel mb-3">
    <h1 style="font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;font-size:1.25rem;margin:0 0 .2rem;">{{ $session->classType->name_th }}</h1>
    <div class="small mb-2" style="color:var(--ink-soft);">{{ $session->classType->name_en }}</div>

    <div class="d-flex flex-wrap gap-3 small">
      <span><i class="bi bi-calendar3"></i> {{ $session->start_at->locale('th')->isoFormat('dddd D MMM YYYY') }}</span>
      <span><i class="bi bi-clock"></i> {{ $session->start_at->format('H:i') }}–{{ $session->end_at->format('H:i') }}</span>
      <span><i class="bi bi-geo-alt"></i> {{ $session->branch->name_th }}</span>
      @if($session->room)<span><i class="bi bi-door-open"></i> {{ $session->room->name_th }}</span>@endif
    </div>

    @if($session->note_th)
      <div class="mt-2 small" style="color:var(--ink-soft);">
        <i class="bi bi-sticky"></i> {{ $session->note_th }}
      </div>
    @endif
  </div>

  <div class="card-panel">
    <div class="d-flex align-items-center mb-2">
      <div class="ttl mb-0">รายชื่อผู้เรียน</div>
      <span class="badge-soft ms-auto">{{ $session->booked_count }}/{{ $session->capacity }} ที่</span>
    </div>

    @if($bookings->isEmpty())
      <div class="empty-note">ยังไม่มีผู้จองคลาสนี้</div>
    @else
      @php $n = 0; @endphp
      @foreach($bookings as $b)
        @php $isWaitlist = $b->status === 'waitlisted'; if(! $isWaitlist) $n++; @endphp
        <div class="student-row">
          <div class="student-num" style="{{ $isWaitlist ? 'background:#F4E3C7;color:#8A6112;' : '' }}">
            {{ $isWaitlist ? 'W' : $n }}
          </div>
          <div class="flex-grow-1 min-width-0">
            <div class="fw-semibold">
              {{ $b->customer->first_name }}
              @if($b->customer->nickname)
                <span class="fw-normal" style="color:var(--ink-soft);">({{ $b->customer->nickname }})</span>
              @endif
            </div>

            @if($b->customer->is_pregnant || $b->customer->medical_note)
              <div class="medical">
                <i class="bi bi-heart-pulse"></i>
                @if($b->customer->is_pregnant)<strong>กำลังตั้งครรภ์</strong>@endif
                @if($b->customer->is_pregnant && $b->customer->medical_note) · @endif
                {{ $b->customer->medical_note }}
              </div>
            @endif
          </div>

          <div class="text-end">
            @if($b->status === 'attended')
              <span class="badge-soft badge-ok">มาแล้ว</span>
            @elseif($isWaitlist)
              <span class="badge-soft badge-warn">คิว {{ $b->waitlist_position }}</span>
            @else
              <span class="badge-soft">ยืนยันแล้ว</span>
            @endif
          </div>
        </div>
      @endforeach
    @endif
  </div>

  <div class="text-center small mt-4" style="color:var(--ink-soft);">
    ข้อมูลนี้เป็นความลับของลูกค้า กรุณาอย่าเผยแพร่
  </div>
</div>

<script>
(function(){
  var saved = localStorage.getItem('trainerTheme');
  if(saved){ document.documentElement.setAttribute('data-bs-theme', saved); }
})();
</script>
</body>
</html>

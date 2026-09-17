{{--
  การ์ดวิดีโอแบบ poster + ปุ่ม Play — ดีไซน์เป๊ะ ทุกใบสูงเท่ากัน
  คลิกแล้วค่อยโหลด embed จริงในที่เดิม (lazy) ปลอดภัย ไม่รับ HTML ดิบจากแอดมิน
  ตัวแปร: $video (App\Models\Video)

  data-embed = payload ที่ JS จะเอาไปสร้าง iframe/blockquote ตอนกดเล่น
--}}
@php
  $url = $video->canonicalUrl();
  $poster = $video->posterUrl();

  // ฝัง iframe เล่นในหน้าเว็บเลยถ้าทำได้ (ไม่เด้งออกแอป) ถ้าไม่ได้ค่อย fallback เปิดลิงก์
  $iframe = $video->embedIframeUrl();
  $embed = $iframe
      ? ['type' => 'iframe', 'src' => $iframe]
      : ['type' => 'link', 'href' => $url];
@endphp

<article class="vcard"
         data-provider="{{ $video->provider }}"
         data-embed='@json($embed)'
         data-href="{{ $url }}">
  <div class="vcard__media">
    @if($poster)
      <img class="vcard__poster" src="{{ $poster }}" alt="{{ $video->title ?: 'DRIP Pilates Club' }}" loading="lazy">
    @else
      @php
        // ไล่โทนพื้นหลังตาม id เพื่อไม่ให้การ์ด placeholder ซ้ำกันหมด
        $tones = [
          ['#2B3242','#5E7699'], ['#5E7699','#8C9DBE'], ['#3A4256','#7C93B8'],
          ['#4A5A78','#B98FA8'], ['#2F3A4C','#6B7690'],
        ];
        $tone = $tones[$video->id % count($tones)];
      @endphp
      <div class="vcard__poster vcard__poster--blank"
           style="background:linear-gradient(150deg,{{ $tone[0] }},{{ $tone[1] }});">
        <img src="{{ asset('images/logo.png') }}" alt="DRIP Pilates Club" class="vcard__logo">
      </div>
    @endif

    <span class="vcard__badge">
      @switch($video->provider)
        @case('youtube') <i class="bi bi-youtube"></i> @break
        @case('tiktok')  <i class="bi bi-tiktok"></i> @break
        @case('facebook')<i class="bi bi-facebook"></i> @break
        @default <i class="bi bi-instagram"></i>
      @endswitch
    </span>

    <button class="vcard__play" type="button" aria-label="Play video">
      <i class="bi bi-play-fill"></i>
    </button>
  </div>

  @if($video->title || $video->caption)
    <div class="vcard__body">
      @if($video->title)<h3 class="vcard__title">{{ $video->title }}</h3>@endif
      @if($video->caption)<p class="vcard__caption">{{ $video->caption }}</p>@endif
    </div>
  @endif
</article>

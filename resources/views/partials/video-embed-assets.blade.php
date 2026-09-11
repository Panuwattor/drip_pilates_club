{{--
  CSS + JS ของการ์ดวิดีโอ (poster + play) ใช้ร่วมทั้งหน้าแรกและหน้า /videos
  include ครั้งเดียวต่อหน้า ก่อน </body>
--}}
<style>
  .vcard{
    background:var(--panel,#fff); border-radius:20px; overflow:hidden;
    box-shadow:0 10px 30px rgba(43,50,66,.10); border:1px solid var(--line,#DCE1EB);
    display:flex; flex-direction:column;
  }
  .vcard__media{
    position:relative; aspect-ratio:9/16; background:#0d1016; overflow:hidden; cursor:pointer;
  }
  .vcard__poster{ width:100%; height:100%; object-fit:cover; display:block; transition:transform .4s ease; }
  .vcard:hover .vcard__poster{ transform:scale(1.04); }
  .vcard__poster--blank{
    display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg,#2B3242,#5E7699);
  }
  .vcard__logo{ width:52%; max-width:150px; opacity:.85; filter:brightness(0) invert(1); }
  .vcard__media::after{
    content:""; position:absolute; inset:0; pointer-events:none;
    background:linear-gradient(180deg,rgba(0,0,0,.28) 0%,rgba(0,0,0,0) 28%,rgba(0,0,0,0) 60%,rgba(0,0,0,.32) 100%);
  }
  .vcard__badge{
    position:absolute; top:12px; right:12px; z-index:2;
    width:34px; height:34px; border-radius:50%;
    background:rgba(0,0,0,.45); backdrop-filter:blur(4px);
    color:#fff; display:flex; align-items:center; justify-content:center; font-size:1rem;
  }
  .vcard__play{
    position:absolute; inset:0; margin:auto; z-index:2;
    width:64px; height:64px; border-radius:50%; border:none;
    background:rgba(255,255,255,.92); color:#2B3242; font-size:1.7rem;
    display:flex; align-items:center; justify-content:center; cursor:pointer;
    box-shadow:0 8px 24px rgba(0,0,0,.28); transition:transform .2s, background .2s;
  }
  .vcard__play i{ margin-left:3px; }
  .vcard:hover .vcard__play{ transform:scale(1.08); background:#fff; }
  .vcard__body{ padding:1rem 1.15rem 1.2rem; }
  .vcard__title{ font-size:1rem; font-weight:700; margin:0 0 .3rem; line-height:1.35; }
  .vcard__caption{
    font-size:.85rem; color:var(--ink-soft,#6B7690); margin:0; line-height:1.5;
    display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
  }

  .vcard__media iframe,
  .vcard__media .vcard__inline{ position:absolute; inset:0; width:100%; height:100%; border:0; background:#000; }

  .video-viewer{
    position:fixed; inset:0; z-index:1050; display:flex; align-items:center; justify-content:center;
    padding:1.25rem; background:rgba(15,19,28,.82); backdrop-filter:blur(8px);
    opacity:0; visibility:hidden; transition:opacity .2s ease, visibility .2s ease;
  }
  .video-viewer.is-open{ opacity:1; visibility:visible; }
  .video-viewer__panel{
    position:relative; width:min(92vw,760px); height:min(88vh,820px); overflow:hidden;
    background:#090b10; border:1px solid rgba(255,255,255,.18); border-radius:18px;
    box-shadow:0 24px 70px rgba(0,0,0,.45); transform:translateY(12px) scale(.98);
    transition:transform .2s ease;
  }
  .video-viewer.is-open .video-viewer__panel{ transform:none; }
  .video-viewer__content{ width:100%; height:100%; display:flex; align-items:center; justify-content:center; }
  .video-viewer iframe{ width:100%; height:100%; border:0; background:#000; }
  .video-viewer__close{
    position:absolute; top:.7rem; right:.7rem; z-index:2; width:38px; height:38px;
    border:1px solid rgba(255,255,255,.28); border-radius:50%; background:rgba(0,0,0,.58);
    color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.1rem;
  }
  .video-viewer__close:hover{ background:rgba(255,255,255,.2); }
  .video-viewer__loading{ color:rgba(255,255,255,.86); text-align:center; font-size:.9rem; }
  .video-viewer__loading i{ display:block; margin-bottom:.6rem; font-size:1.6rem; }
  .video-viewer__fallback{ color:#fff; text-align:center; padding:1.5rem; }
  .video-viewer__fallback i{ display:block; margin-bottom:.7rem; font-size:2rem; }
  .video-viewer__fallback p{ margin:0 0 1rem; color:rgba(255,255,255,.72); }
  .video-viewer__fallback a{
    display:inline-flex; align-items:center; gap:.4rem; padding:.65rem 1.1rem; border-radius:999px;
    background:#fff; color:#2B3242; font-weight:700; text-decoration:none; font-size:.9rem;
  }
  @media (max-width:575.98px){
    .video-viewer{ padding:.5rem; }
    .video-viewer__panel{ width:100%; height:min(90vh,760px); border-radius:14px; }
  }
</style>

<script>
(function(){
  var viewer;

  function createViewer(){
    viewer = document.createElement('div');
    viewer.className = 'video-viewer';
    viewer.setAttribute('role','dialog');
    viewer.setAttribute('aria-modal','true');
    viewer.setAttribute('aria-label','Video player');
    viewer.innerHTML = '<div class="video-viewer__panel">'
      + '<button class="video-viewer__close" type="button" aria-label="Close video"><i class="bi bi-x-lg"></i></button>'
      + '<div class="video-viewer__content"></div></div>';
    document.body.appendChild(viewer);
    viewer.addEventListener('click', function(e){
      if (e.target === viewer || e.target.closest('.video-viewer__close')) closeViewer();
    });
  }

  function closeViewer(){
    if (!viewer) return;
    viewer.classList.remove('is-open');
    document.body.style.overflow = '';
    window.setTimeout(function(){
      var content = viewer.querySelector('.video-viewer__content');
      content.replaceChildren();
    }, 200);
  }

  function loadCard(card){
    var embed;
    try { embed = JSON.parse(card.getAttribute('data-embed') || '{}'); } catch(e){ embed = {}; }
    if (!viewer) createViewer();
    var content = viewer.querySelector('.video-viewer__content');
    content.replaceChildren();
    var loading = document.createElement('div');
    loading.className = 'video-viewer__loading';
    loading.innerHTML = "<i class=\"bi bi-arrow-repeat\"></i>{{ __t('กำลังโหลดวิดีโอ...', 'Loading video...') }}";
    content.appendChild(loading);
    viewer.classList.add('is-open');
    document.body.style.overflow = 'hidden';

    var provider = card.getAttribute('data-provider') || 'instagram';
    if (embed.type === 'iframe' && embed.src){
      var iframe = document.createElement('iframe');
      iframe.src = embed.src;
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
      iframe.setAttribute('allowfullscreen','');
      iframe.setAttribute('referrerpolicy','strict-origin-when-cross-origin');
      iframe.addEventListener('load', function(){ loading.remove(); }, {once:true});
      content.replaceChildren(iframe);
      return;
    }

    var href = card.getAttribute('data-href');
    var label = provider.charAt(0).toUpperCase() + provider.slice(1);
    var box = document.createElement('div');
    box.className = 'video-viewer__fallback';
    var icon = document.createElement('i');
    icon.className = 'bi bi-box-arrow-up-right';
    var message = document.createElement('p');
    message.textContent = "{{ __t('ไม่สามารถฝังวิดีโอนี้ในหน้านี้ได้', 'This video cannot be embedded here.') }}";
    var link = document.createElement('a');
    link.href = href; link.target = '_blank'; link.rel = 'noopener';
    link.innerHTML = "{{ __t('เปิดดูบน', 'Watch on') }} " + label + ' <i class="bi bi-arrow-right"></i>';
    box.append(icon, message, link);
    content.replaceChildren(box);
  }

  document.addEventListener('click', function(e){
    var trigger = e.target.closest('.vcard__play');
    if (!trigger) return;
    var card = trigger.closest('.vcard');
    if (card) loadCard(card);
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeViewer();
  });
})();
</script>

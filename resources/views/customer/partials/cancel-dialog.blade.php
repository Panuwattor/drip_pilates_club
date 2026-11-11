{{--
  ป๊อปอัปยืนยันการยกเลิกคลาส ใช้ร่วมกันทุกหน้าของลูกค้า (การจองของฉัน, ตารางคลาส)

  เรียกใช้: cancelBooking(bookingId, { onStart, onDone, onFail })
    - ถามเซิร์ฟเวอร์ก่อนว่ายกเลิกตอนนี้จะเสียเครดิตไหม แล้วโชว์ป๊อปอัปแบบปกติหรือแบบเตือน Late Cancel
    - Late Cancel (เลย 22:00 ของวันก่อนคลาสสำหรับกรุ๊ปคลาส) ส่ง confirm_late=1 ไปด้วย
      ถ้าเปิดป๊อปอัปค้างไว้จนเลยเส้นตาย เซิร์ฟเวอร์ตอบ 409 แล้วป๊อปอัปจะเด้งถามใหม่แบบเตือน
--}}
<style>
  .cxl-backdrop{
    position:fixed; inset:0; z-index:1080; background:rgba(20,16,14,.55);
    display:flex; align-items:center; justify-content:center; padding:1.25rem;
    opacity:0; visibility:hidden; transition:opacity .18s, visibility .18s;
  }
  .cxl-backdrop.show{ opacity:1; visibility:visible; }
  .cxl-dialog{
    width:100%; max-width:360px; background:var(--panel); color:var(--ink);
    border-radius:22px; padding:1.6rem 1.4rem 1.2rem; text-align:center;
    box-shadow:0 24px 60px rgba(0,0,0,.28); transform:translateY(12px) scale(.98); transition:transform .18s;
  }
  .cxl-backdrop.show .cxl-dialog{ transform:none; }
  .cxl-icon{
    width:64px; height:64px; border-radius:50%; margin:0 auto .9rem;
    display:flex; align-items:center; justify-content:center; font-size:1.9rem;
    background:var(--accent-soft); color:var(--accent-deep);
  }
  .cxl-dialog.is-late .cxl-icon{ background:#FBE3E0; color:#B23B2E; animation:cxl-pulse 1.4s ease-in-out infinite; }
  [data-bs-theme="dark"] .cxl-dialog.is-late .cxl-icon{ background:#3D211D; color:#F09A8E; }
  @keyframes cxl-pulse{ 0%,100%{ box-shadow:0 0 0 0 rgba(178,59,46,.35); } 50%{ box-shadow:0 0 0 10px rgba(178,59,46,0); } }
  .cxl-badge{
    display:inline-block; font-size:.7rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
    background:#B23B2E; color:#fff; border-radius:999px; padding:.2rem .7rem; margin-bottom:.55rem;
  }
  .cxl-title{ font-size:1.12rem; font-weight:700; margin:0 0 .45rem; }
  .cxl-body{ font-size:.9rem; color:var(--ink-soft); margin:0 0 .5rem; line-height:1.55; }
  .cxl-credit{
    font-size:.92rem; font-weight:700; color:#B23B2E; background:#FBE3E0;
    border-radius:12px; padding:.55rem .75rem; margin:.6rem 0 .2rem;
  }
  [data-bs-theme="dark"] .cxl-credit{ background:#3D211D; color:#F09A8E; }
  .cxl-rule{ font-size:.78rem; color:var(--ink-soft); margin:.55rem 0 0; }
  .cxl-actions{ display:flex; gap:.6rem; margin-top:1.15rem; }
  .cxl-actions button{
    flex:1; border-radius:999px; padding:.7rem .5rem; font-weight:700; font-size:.9rem; border:1px solid var(--line);
  }
  .cxl-keep{ background:var(--panel); color:var(--ink); }
  .cxl-confirm{ background:var(--accent); border-color:var(--accent)!important; color:#fff; }
  .cxl-dialog.is-late .cxl-confirm{ background:#B23B2E; border-color:#B23B2E!important; }
  .cxl-actions button:disabled{ opacity:.6; }
</style>

<div class="cxl-backdrop" id="cancelDialog" role="dialog" aria-modal="true" aria-labelledby="cancelDialogTitle">
  <div class="cxl-dialog">
    <div class="cxl-icon"><i class="bi bi-calendar-x"></i></div>
    <div class="cxl-badge" hidden>Late Cancel</div>
    <h3 class="cxl-title" id="cancelDialogTitle"></h3>
    <p class="cxl-body"></p>
    <div class="cxl-credit" hidden></div>
    <p class="cxl-rule"></p>
    <div class="cxl-actions">
      <button type="button" class="cxl-keep"></button>
      <button type="button" class="cxl-confirm"></button>
    </div>
  </div>
</div>

<script>
(function(){
  var dlg = document.getElementById('cancelDialog');
  var box = dlg.querySelector('.cxl-dialog');
  var icon = dlg.querySelector('.cxl-icon i');
  var badge = dlg.querySelector('.cxl-badge');
  var title = dlg.querySelector('.cxl-title');
  var body = dlg.querySelector('.cxl-body');
  var credit = dlg.querySelector('.cxl-credit');
  var rule = dlg.querySelector('.cxl-rule');
  var keepBtn = dlg.querySelector('.cxl-keep');
  var okBtn = dlg.querySelector('.cxl-confirm');
  var th = function(){ return currentLang === 'th'; };

  /** เปิดป๊อปอัป คืน Promise<boolean> ว่าลูกค้ากดยืนยันไหม */
  function ask(p){
    var late = !!p.will_lose_credit;

    box.classList.toggle('is-late', late);
    icon.className = late ? 'bi bi-exclamation-triangle-fill' : 'bi bi-calendar-x';
    badge.hidden = !late;
    credit.hidden = !late;

    if (late) {
      title.textContent = th() ? 'ยกเลิกช้า จะถูกตัดเครดิต' : 'Late cancellation — credit will be charged';
      body.textContent = th()
        ? 'เลยเวลายกเลิกฟรีแล้ว (' + p.deadline_text + ') ถ้ายกเลิกตอนนี้ระบบจะไม่คืนเครดิตให้'
        : 'The free-cancellation deadline has passed (' + p.deadline_text + '). If you cancel now, your credit will not be refunded.';
      credit.textContent = th()
        ? 'เสียเครดิต ' + p.credit_at_stake + ' เครดิต'
        : 'You will lose ' + p.credit_at_stake + ' credit(s)';
      okBtn.textContent = th() ? 'ยืนยันยกเลิก' : 'Cancel anyway';
    } else {
      title.textContent = th() ? 'ยกเลิกการจองนี้?' : 'Cancel this booking?';
      body.textContent = p.credit_at_stake > 0
        ? (th() ? 'ยังอยู่ในเวลายกเลิกฟรี เครดิตจะคืนเข้าบัญชีของคุณ' : 'You are within the free-cancellation window — your credit will be refunded.')
        : (th() ? 'ยืนยันยกเลิกการจองนี้' : 'Please confirm you want to cancel.');
      okBtn.textContent = th() ? 'ยืนยันยกเลิก' : 'Yes, cancel';
    }

    rule.textContent = p.rule_text || '';
    keepBtn.textContent = th() ? 'ไม่ยกเลิก' : 'Keep booking';
    okBtn.disabled = false;

    dlg.classList.add('show');
    keepBtn.focus();

    return new Promise(function(resolve){
      function close(result){
        dlg.classList.remove('show');
        okBtn.removeEventListener('click', onOk);
        keepBtn.removeEventListener('click', onKeep);
        dlg.removeEventListener('click', onBackdrop);
        document.removeEventListener('keydown', onKey);
        resolve(result);
      }
      function onOk(){ close(true); }
      function onKeep(){ close(false); }
      function onBackdrop(e){ if (e.target === dlg) close(false); }
      function onKey(e){ if (e.key === 'Escape') close(false); }

      okBtn.addEventListener('click', onOk);
      keepBtn.addEventListener('click', onKeep);
      dlg.addEventListener('click', onBackdrop);
      document.addEventListener('keydown', onKey);
    });
  }

  function errorToast(){
    showToast(th() ? 'เกิดข้อผิดพลาด กรุณาลองใหม่' : 'Something went wrong, please try again', true);
  }

  function submit(id, confirmLate, cb){
    cb.onStart && cb.onStart();

    fetch('/bookings/' + id + '/cancel', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ confirm_late: confirmLate ? 1 : 0 })
    })
    .then(function(r){ return r.json().then(function(d){ return { status: r.status, ok: r.ok, data: d }; }); })
    .then(function(res){
      // เลยเส้นตายระหว่างเปิดป๊อปอัปค้างไว้ เด้งถามใหม่แบบเตือน Late Cancel
      if (res.status === 409 && res.data.needs_late_confirm) {
        cb.onFail && cb.onFail();
        ask(res.data).then(function(yes){ if (yes) submit(id, true, cb); });
        return;
      }

      showToast(res.data.message || '', !res.ok);
      if (res.ok) { cb.onDone && cb.onDone(res.data); }
      else { cb.onFail && cb.onFail(); }
    })
    .catch(function(){
      cb.onFail && cb.onFail();
      errorToast();
    });
  }

  window.cancelBooking = function(id, cb){
    cb = cb || {};

    fetch('/bookings/' + id + '/cancel-preview', {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r){ if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(function(p){
      return ask(p).then(function(yes){
        if (yes) submit(id, !!p.will_lose_credit, cb);
      });
    })
    .catch(errorToast);
  };
})();
</script>

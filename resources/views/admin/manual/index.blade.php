@extends('admin.layouts.app')
@section('title', 'คู่มือการใช้งานระบบ')

@push('styles')
<style>
  .man-wrap{ display:grid; grid-template-columns:232px minmax(0,1fr); gap:1.5rem; align-items:start; }
  @media (max-width:1199.98px){ .man-wrap{ grid-template-columns:1fr; } }

  /* สารบัญด้านข้าง */
  .man-toc{
    position:sticky; top:74px; max-height:calc(100vh - 96px); overflow-y:auto;
    background:var(--panel); border:1px solid var(--line); border-radius:var(--r-lg);
    padding:.85rem .6rem; box-shadow:var(--shadow-sm);
  }
  @media (max-width:1199.98px){ .man-toc{ position:static; max-height:none; } }
  .man-toc .grp{
    font-size:.6rem; letter-spacing:.13em; text-transform:uppercase;
    color:var(--ink-faint); font-weight:700; padding:.75rem .6rem .3rem;
  }
  .man-toc a{
    display:flex; align-items:center; gap:.55rem; padding:.4rem .6rem;
    border-radius:var(--r-sm); color:var(--ink-soft); text-decoration:none;
    font-size:.8rem; font-weight:500; margin-bottom:1px;
  }
  .man-toc a i{ font-size:.92rem; width:17px; text-align:center; opacity:.8; }
  .man-toc a:hover{ background:var(--ground-2); color:var(--ink); }
  .man-toc a.on{ background:var(--accent-soft); color:var(--accent-deep); font-weight:600; }

  /* หัวข้อบท */
  .man-sec{ scroll-margin-top:84px; margin-bottom:2rem; }
  .man-sec > .card-panel{ padding:1.5rem; }
  .man-h{
    display:flex; align-items:center; gap:.7rem; margin-bottom:.35rem;
    padding-bottom:.85rem; border-bottom:1px solid var(--line-soft);
  }
  .man-h .ic{
    width:38px; height:38px; border-radius:11px; flex:0 0 auto;
    display:flex; align-items:center; justify-content:center; font-size:1.05rem;
    background:var(--accent-soft); color:var(--accent-deep);
  }
  .man-h h2{
    font-family:var(--font-display); font-size:1.12rem; font-weight:600; margin:0;
    letter-spacing:-.015em;
  }
  .man-h .sub{ font-size:.74rem; color:var(--ink-faint); margin-top:1px; }
  .man-h .where{
    margin-left:auto; font-size:.68rem; color:var(--ink-faint);
    background:var(--ground-2); border-radius:999px; padding:.22rem .65rem;
    font-family:ui-monospace,SFMono-Regular,Menlo,monospace; white-space:nowrap;
  }

  .man-body{ padding-top:1.1rem; font-size:.875rem; line-height:1.75; }
  .man-body h3{
    font-size:.93rem; font-weight:600; margin:1.5rem 0 .55rem;
    display:flex; align-items:center; gap:.45rem;
  }
  .man-body h3:first-child{ margin-top:0; }
  .man-body h3::before{
    content:''; width:3px; height:14px; border-radius:2px; background:var(--accent); flex:0 0 auto;
  }
  /* หัวข้อย่อยใต้ h3 เช่น โหมดหักเครดิต 2 แบบ */
  .man-body h4{
    font-size:.88rem; font-weight:600; margin:1.1rem 0 .45rem;
    display:flex; align-items:center; gap:.4rem; color:var(--accent-deep);
  }
  .man-body p{ margin-bottom:.7rem; color:var(--ink); }
  .man-body ul,.man-body ol{ padding-left:1.15rem; margin-bottom:.8rem; }
  .man-body li{ margin-bottom:.32rem; }
  .man-body code{
    background:var(--ground-2); color:var(--accent-deep); padding:.1rem .38rem;
    border-radius:5px; font-size:.82em; font-family:ui-monospace,SFMono-Regular,Menlo,monospace;
  }
  .man-body strong{ font-weight:600; }

  /* ภาพหน้าจอ */
  figure.shot{ margin:1rem 0 1.2rem; }
  figure.shot img{
    width:100%; display:block; border:1px solid var(--line);
    border-radius:var(--r-md); box-shadow:var(--shadow-sm); cursor:zoom-in;
    background:var(--panel-2);
  }
  figure.shot figcaption{
    font-size:.75rem; color:var(--ink-faint); margin-top:.45rem;
    display:flex; align-items:center; gap:.4rem;
  }
  figure.shot figcaption b{ color:var(--ink-soft); font-weight:600; }

  /* กล่องเน้น */
  .note{
    border-radius:var(--r-md); padding:.75rem .95rem; margin:.9rem 0;
    font-size:.83rem; display:flex; gap:.6rem; align-items:flex-start; line-height:1.65;
  }
  .note i{ flex:0 0 auto; margin-top:.15rem; font-size:.95rem; }
  .note.tip{ background:var(--accent-soft); color:var(--accent-deep); }
  .note.warn{ background:var(--warn-soft); color:var(--warn); }
  .note.dang{ background:var(--danger-soft); color:var(--danger); }
  .note.ok{ background:var(--ok-soft); color:var(--ok); }

  /* ขั้นตอน */
  ol.steps{ counter-reset:s; list-style:none; padding-left:0; margin:.6rem 0 1rem; }
  ol.steps > li{
    counter-increment:s; position:relative; padding-left:2rem; margin-bottom:.55rem;
  }
  ol.steps > li::before{
    content:counter(s); position:absolute; left:0; top:.1rem;
    width:1.35rem; height:1.35rem; border-radius:50%;
    background:var(--accent); color:#fff; font-size:.7rem; font-weight:700;
    display:flex; align-items:center; justify-content:center;
  }

  /* ตารางอ้างอิง */
  .man-body table{ width:100%; font-size:.82rem; margin:.7rem 0 1rem; border-collapse:collapse; }
  .man-body table th{
    text-align:left; font-size:.65rem; letter-spacing:.07em; text-transform:uppercase;
    color:var(--ink-faint); font-weight:700; padding:.45rem .6rem;
    border-bottom:1px solid var(--line);
  }
  .man-body table td{
    padding:.52rem .6rem; border-bottom:1px solid var(--line-soft); vertical-align:top;
  }
  .man-body table tr:last-child td{ border-bottom:0; }
  .tbl-wrap{ overflow-x:auto; }

  /* ดูภาพเต็มจอ */
  .lightbox{
    position:fixed; inset:0; z-index:1080; background:rgba(12,16,24,.88);
    display:none; align-items:center; justify-content:center; padding:2rem;
    backdrop-filter:blur(3px); cursor:zoom-out;
  }
  .lightbox.on{ display:flex; }
  .lightbox img{ max-width:100%; max-height:100%; border-radius:10px; box-shadow:var(--shadow-lg); }
  .lightbox .x{
    position:absolute; top:1rem; right:1.3rem; color:#fff; font-size:1.7rem;
    background:none; border:0; line-height:1; opacity:.85;
  }

  @media print{
    .man-toc, .lightbox{ display:none !important; }
    .man-wrap{ grid-template-columns:1fr; }
    .man-sec{ break-inside:avoid; }
  }
</style>
@endpush

@section('content')

@php
  // ภาพทั้งหมดถ่ายจากระบบจริงพร้อมข้อมูลตัวอย่าง
  $img = fn ($f) => asset('docs/manual/img/' . $f . '.png');
@endphp

<div class="alert-soft mb-3">
  <i class="bi bi-book"></i>
  คู่มือนี้อธิบายทุกเมนูในระบบหลังบ้าน ภาพประกอบถ่ายจากหน้าจอจริง — คลิกที่ภาพเพื่อดูขนาดเต็ม
</div>

<div class="man-wrap">

  {{-- ───────── สารบัญ ───────── --}}
  <nav class="man-toc" id="manToc">
    <a href="#intro" class="on"><i class="bi bi-compass"></i> เริ่มต้นใช้งาน</a>
    <a href="#concept"><i class="bi bi-diagram-3"></i> ภาพรวมระบบ</a>
    <a href="#login"><i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ</a>
    <a href="#dashboard"><i class="bi bi-speedometer2"></i> แดชบอร์ด</a>

    <div class="grp">ตารางและการจอง</div>
    <a href="#sessions"><i class="bi bi-calendar3"></i> รอบเรียน</a>
    <a href="#schedules"><i class="bi bi-arrow-repeat"></i> ตารางประจำสัปดาห์</a>
    <a href="#bookings"><i class="bi bi-check2-square"></i> การจอง</a>

    <div class="grp">ลูกค้าและการขาย</div>
    <a href="#counter"><i class="bi bi-shop"></i> เคาน์เตอร์</a>
    <a href="#customers"><i class="bi bi-people"></i> ลูกค้า</a>
    <a href="#orders"><i class="bi bi-receipt"></i> คำสั่งซื้อ</a>
    <a href="#packages"><i class="bi bi-ticket-perforated"></i> แพ็กเกจ</a>

    <div class="grp">ข้อมูลหลัก</div>
    <a href="#branches"><i class="bi bi-geo-alt"></i> สาขาและห้อง</a>
    <a href="#trainers"><i class="bi bi-person-badge"></i> ครูผู้สอน</a>
    <a href="#classtypes"><i class="bi bi-grid-3x3-gap"></i> ประเภทคลาส</a>
    <a href="#holidays"><i class="bi bi-calendar-x"></i> วันหยุด</a>

    <div class="grp">อื่นๆ</div>
    <a href="#announcements"><i class="bi bi-megaphone"></i> บทความ/ประกาศ</a>
    <a href="#videos"><i class="bi bi-play-btn"></i> คลิปวิดีโอ</a>
    <a href="#reports"><i class="bi bi-graph-up"></i> รายงาน</a>
    <a href="#users"><i class="bi bi-shield-lock"></i> ผู้ใช้งานระบบ</a>
    <a href="#settings"><i class="bi bi-sliders"></i> ตั้งค่าระบบ</a>

    <div class="grp">ภาคผนวก</div>
    <a href="#auto"><i class="bi bi-robot"></i> งานอัตโนมัติ</a>
    <a href="#faq"><i class="bi bi-question-circle"></i> ปัญหาที่พบบ่อย</a>
  </nav>

  <div>

  {{-- ───────── เริ่มต้น ───────── --}}
  <section class="man-sec" id="intro">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-compass"></i></div>
        <div><h2>เริ่มต้นใช้งาน</h2><div class="sub">อ่านตรงนี้ก่อนถ้าเพิ่งเริ่มใช้ระบบ</div></div>
      </div>
      <div class="man-body">
        <p>
          ระบบนี้ดูแลงานของสตูดิโอพิลาทิสทั้งหมด ตั้งแต่จัดตารางคลาส รับจอง ขายแพ็กเกจ
          ไปจนถึงเช็คอินหน้าร้านและดูรายงาน คู่มือนี้เรียงตามเมนูด้านซ้ายของระบบ
        </p>

        <h3>งานที่ทำบ่อยที่สุด</h3>
        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:38%">อยากทำอะไร</th><th>ไปที่เมนู</th></tr></thead>
          <tbody>
            <tr><td>ลูกค้ามาถึงหน้าร้าน กดเช็คอิน</td><td><b>รอบเรียน</b> → เปิดรอบของวันนี้ → กด ✓ หลังชื่อลูกค้า</td></tr>
            <tr><td>ลูกค้า walk-in มาเรียนสด ไม่ได้จอง</td><td><b>เคาน์เตอร์</b> → ค้นชื่อ → หักเครดิต → <b>มาเรียนคลาสนี้</b> → เลือกคลาส</td></tr>
            <tr><td>ลูกค้าโอนเงินมา ต้องเพิ่มเครดิต</td><td><b>คำสั่งซื้อ</b> → เปิดบิลที่รอยืนยัน → ตรวจสลิป → ยืนยัน</td></tr>
            <tr><td>ขายแพ็กเกจหน้าร้าน รับเงินสด</td><td><b>คำสั่งซื้อ</b> → เปิดบิลใหม่ → ติ๊ก "ชำระแล้ว"</td></tr>
            <tr><td>ลูกค้าโทรมาขอจองคลาส</td><td><b>รอบเรียน</b> → เปิดรอบนั้น → จองให้ลูกค้า</td></tr>
            <tr><td>ครูลา ต้องหาคนสอนแทน</td><td><b>รอบเรียน</b> → เปิดรอบนั้น → บันทึกครูสอนแทน</td></tr>
            <tr><td>ต้องยกเลิกคลาสทั้งรอบ</td><td><b>รอบเรียน</b> → เปิดรอบนั้น → ยกเลิกรอบนี้ (คืนเครดิตอัตโนมัติ)</td></tr>
            <tr><td>ลูกค้าขอหยุดพักแพ็กชั่วคราว</td><td><b>ลูกค้า</b> → เปิดโปรไฟล์ → ปุ่มฟรีซที่แพ็กนั้น</td></tr>
          </tbody>
        </table>
        </div>

        <div class="note tip">
          <i class="bi bi-lightbulb"></i>
          <span>
            ถ้าไม่แน่ใจว่าควรใช้เมนูไหน ให้เริ่มที่ <b>เคาน์เตอร์</b> เสมอ
            เพราะรวมงานหน้าร้านที่ทำบ่อยไว้ในหน้าเดียว ค้นหาลูกค้าครั้งเดียวแล้วทำได้เกือบทุกอย่าง
          </span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── ภาพรวม ───────── --}}
  <section class="man-sec" id="concept">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-diagram-3"></i></div>
        <div><h2>ภาพรวมระบบ</h2><div class="sub">เข้าใจ 5 คำนี้แล้วใช้ระบบได้ทั้งหมด</div></div>
      </div>
      <div class="man-body">

        <h3>คำศัพท์ที่ต้องแยกให้ออก</h3>
        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:22%">คำ</th><th>หมายถึง</th></tr></thead>
          <tbody>
            <tr>
              <td><b>ประเภทคลาส</b></td>
              <td>ชนิดของคลาส เช่น "ทรีโอ รีฟอร์มเมอร์" "ไพรเวท พิลาทิส" — เป็นแค่ชื่อประเภท ยังไม่มีวันเวลา</td>
            </tr>
            <tr>
              <td><b>ตารางประจำสัปดาห์</b></td>
              <td>แม่แบบที่บอกว่า "ทุกวันจันทร์ 08:00 มีคลาสทรีโอ ห้องรีฟอร์มเมอร์ 2 ครูต้า" — ตั้งครั้งเดียวใช้ยาว</td>
            </tr>
            <tr>
              <td><b>รอบเรียน</b></td>
              <td>คลาสจริงที่มีวันที่แน่นอน เช่น "จันทร์ 21 ก.ย. 2026 08:00" — ระบบสร้างจากแม่แบบให้อัตโนมัติ <b>ลูกค้าจองที่รอบเรียนนี้</b></td>
            </tr>
            <tr>
              <td><b>แพ็กเกจ</b></td>
              <td>สินค้าที่ขาย เช่น "ทรีโอ 10 ครั้ง 8,500 บาท ใช้ได้ 60 วัน"</td>
            </tr>
            <tr>
              <td><b>เครดิต</b></td>
              <td>จำนวนครั้งที่ลูกค้าเหลือ ซื้อแพ็ก 10 ครั้งได้ 10 เครดิต จอง 1 คลาสตัด 1 เครดิต</td>
            </tr>
          </tbody>
        </table>
        </div>

        <h3>เส้นทางของข้อมูล</h3>
        <p>ทั้งระบบไหลตามลำดับนี้ ถ้าตั้งข้อมูลหลักไม่ครบ ขั้นถัดไปจะทำไม่ได้:</p>
        <ol class="steps">
          <li><b>ตั้งข้อมูลหลักก่อน</b> — สาขา → ห้อง → ครูผู้สอน → ประเภทคลาส</li>
          <li><b>วางตารางประจำสัปดาห์</b> — บอกว่าวันไหนเวลาไหนมีคลาสอะไร ใครสอน ห้องไหน</li>
          <li><b>ระบบสร้างรอบเรียน</b> — แปลงแม่แบบเป็นคลาสจริงล่วงหน้า 90 วัน (ข้ามวันหยุดให้)</li>
          <li><b>ขายแพ็กเกจ</b> — ลูกค้าได้เครดิตเข้าบัญชีเมื่อยืนยันการชำระเงิน</li>
          <li><b>ลูกค้าจองคลาส</b> — ตัดเครดิตอัตโนมัติ จากแพ็กที่ใกล้หมดอายุก่อน</li>
          <li><b>เช็คอินหน้าร้าน</b> — บันทึกว่ามาเรียนจริง ข้อมูลเข้ารายงาน</li>
        </ol>

        <div class="note warn">
          <i class="bi bi-exclamation-triangle"></i>
          <span>
            <b>จุดที่คนใหม่มักสับสน:</b> แก้ "ตารางประจำสัปดาห์" แล้ว รอบเรียนที่สร้างไปแล้ว<u>จะไม่เปลี่ยนตาม</u>
            เพราะอาจมีคนจองไว้แล้ว ถ้าอยากแก้คลาสที่จะถึง ต้องเข้าไปแก้ที่ <b>รอบเรียน</b> รายตัว
          </span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── เข้าสู่ระบบ ───────── --}}
  <section class="man-sec" id="login">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-box-arrow-in-right"></i></div>
        <div><h2>เข้าสู่ระบบ</h2><div class="sub">หน้าแรกก่อนเข้าใช้งาน</div></div>
        <span class="where">/admin/login</span>
      </div>
      <div class="man-body">
        <p>กรอกอีเมลและรหัสผ่านที่ได้รับจากเจ้าของระบบ</p>

        <figure class="shot">
          <img src="{{ $img('01-login') }}" alt="หน้าเข้าสู่ระบบ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>หน้าเข้าสู่ระบบ</b></figcaption>
        </figure>

        <div class="note warn">
          <i class="bi bi-shield-exclamation"></i>
          <span>
            กรอกรหัสผิดเกิน 10 ครั้งใน 1 นาที ระบบจะล็อกชั่วคราวเพื่อกันคนสุ่มรหัส
            ถ้าโดนล็อกให้รอสักครู่แล้วลองใหม่ ถ้าลืมรหัสผ่านต้องให้เจ้าของระบบตั้งให้ใหม่ที่เมนู
            <b>ผู้ใช้งานระบบ</b>
          </span>
        </div>

        <h3>ออกจากระบบ</h3>
        <p>
          กดไอคอนรูปคนมุมขวาบน แล้วเลือก "ออกจากระบบ" — ในเมนูนี้จะเห็นชื่อและอีเมลของบัญชีที่กำลังใช้อยู่ด้วย
          ข้างๆ กันมีปุ่ม <i class="bi bi-circle-half"></i> สำหรับสลับโหมดสว่าง/มืด ระบบจะจำค่าไว้ให้
        </p>
      </div>
    </div>
  </section>

  {{-- ───────── แดชบอร์ด ───────── --}}
  <section class="man-sec" id="dashboard">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-speedometer2"></i></div>
        <div><h2>แดชบอร์ด</h2><div class="sub">สรุปภาพรวมของวันนี้</div></div>
        <span class="where">/admin</span>
      </div>
      <div class="man-body">
        <p>หน้าแรกหลังเข้าสู่ระบบ ออกแบบให้กวาดตาครั้งเดียวรู้ว่าวันนี้ต้องทำอะไรบ้าง</p>

        <figure class="shot">
          <img src="{{ $img('02-dashboard') }}" alt="หน้าแดชบอร์ด" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>แดชบอร์ด</b> — ตัวเลขสรุป ตารางวันนี้ แพ็กใกล้หมดอายุ และการจองล่าสุด</figcaption>
        </figure>

        <h3>ตัวเลขสรุป 4 ช่องบนสุด</h3>
        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:26%">ช่อง</th><th>ความหมาย</th></tr></thead>
          <tbody>
            <tr><td><b>คลาสวันนี้</b></td><td>จำนวนรอบเรียนของวันนี้ที่ยังไม่ถูกยกเลิก</td></tr>
            <tr><td><b>ยอดจองวันนี้</b></td><td>จำนวนคนที่จองคลาสวันนี้ พร้อมแถบ <b>อัตราการเต็ม</b> เทียบกับที่นั่งทั้งหมด</td></tr>
            <tr><td><b>สมาชิกที่ใช้งานอยู่</b></td><td>ลูกค้าสถานะ active ทั้งหมด</td></tr>
            <tr><td><b>รายได้เดือนนี้</b></td><td>ยอดรวมของบิลที่ชำระแล้วในเดือนปัจจุบัน</td></tr>
          </tbody>
        </table>
        </div>

        <h3>แถบเตือนสลิปรอยืนยัน</h3>
        <p>
          ถ้ามีลูกค้าโอนเงินแล้วแนบสลิปเข้ามา จะมีแถบสีเหลืองขึ้นเตือนพร้อมปุ่ม <b>ตรวจสอบ</b>
          และมีตัวเลขสีแดงเกาะที่เมนู <b>คำสั่งซื้อ</b> ด้วย ตัวเลขนี้เห็นได้จากทุกหน้า
        </p>
        <div class="note dang">
          <i class="bi bi-clock-history"></i>
          <span>ควรเคลียร์ให้หมดทุกวัน เพราะลูกค้าที่โอนเงินแล้วยังจองคลาสไม่ได้จนกว่าจะมีคนกดยืนยัน</span>
        </div>

        <h3>การ์ดอื่นๆ</h3>
        <ul>
          <li><b>ตารางวันนี้</b> — รอบเรียนทั้งหมดของวันนี้ เรียงตามเวลา กดไอคอนขวาสุดเพื่อเปิดดูรายชื่อผู้เรียน</li>
          <li><b>แพ็กใกล้หมดอายุ (14 วัน)</b> — รายชื่อลูกค้าที่แพ็กกำลังจะหมด ไว้โทรตามให้มาใช้สิทธิ์หรือต่อแพ็ก</li>
          <li><b>การจองล่าสุด</b> — ความเคลื่อนไหวล่าสุด 8 รายการ พร้อมสถานะ</li>
          <li><b>กราฟยอดจอง 14 วัน</b> — ดูแนวโน้มว่าช่วงไหนคนจองเยอะ</li>
        </ul>
      </div>
    </div>
  </section>

  {{-- ───────── รอบเรียน ───────── --}}
  <section class="man-sec" id="sessions">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-calendar3"></i></div>
        <div><h2>รอบเรียน</h2><div class="sub">คลาสจริงที่ลูกค้าจองได้ — เมนูที่ใช้บ่อยที่สุด</div></div>
        <span class="where">/admin/sessions</span>
      </div>
      <div class="man-body">
        <p>
          หน้านี้คือหัวใจของงานประจำวัน ใช้ดูว่าวันนี้มีคลาสอะไร ใครจองบ้าง
          และใช้เช็คอินลูกค้าเมื่อมาถึงสตูดิโอ
        </p>

        <h3>มุมมองรายวัน</h3>
        <p>เลือกสาขาและวันที่ด้านบน ระบบจะแสดงรอบเรียนของวันนั้นเรียงตามเวลา</p>

        <figure class="shot">
          <img src="{{ $img('03-sessions-day') }}" alt="รอบเรียนมุมมองรายวัน" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>รอบเรียน — มุมมองรายวัน</b></figcaption>
        </figure>

        <h3>มุมมองรายสัปดาห์</h3>
        <p>กดปุ่มสลับเป็น "สัปดาห์" เพื่อดูภาพรวมทั้งอาทิตย์ เหมาะกับตอนวางแผนหรือหาช่องว่าง</p>

        <figure class="shot">
          <img src="{{ $img('04-sessions-week') }}" alt="รอบเรียนมุมมองรายสัปดาห์" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>รอบเรียน — มุมมองรายสัปดาห์</b></figcaption>
        </figure>

        <h3>เปิดดูรายชื่อผู้เรียน</h3>
        <p>คลิกที่รอบเรียนเพื่อเข้าหน้ารายละเอียด ตรงนี้ทำงานหน้าร้านได้เกือบทั้งหมด</p>

        <figure class="shot">
          <img src="{{ $img('05-session-show') }}" alt="รายชื่อผู้เรียนในรอบ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>รายชื่อผู้เรียน</b> — เช็คอิน บันทึกไม่มาเรียน ยกเลิก และจองแทนลูกค้า</figcaption>
        </figure>

        <p>ปุ่มท้ายชื่อลูกค้าแต่ละคน:</p>
        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:20%">ปุ่ม</th><th>ใช้เมื่อไหร่</th></tr></thead>
          <tbody>
            <tr><td><b><i class="bi bi-check-lg"></i> เช็คอิน</b></td><td>ลูกค้ามาถึงสตูดิโอแล้ว — สถานะเปลี่ยนเป็น "เข้าเรียนแล้ว"</td></tr>
            <tr><td><b><i class="bi bi-person-x"></i> ไม่มาเรียน</b></td><td>ถึงเวลาแล้วลูกค้าไม่มาและไม่แจ้ง — เครดิตไม่คืน</td></tr>
            <tr><td><b><i class="bi bi-x"></i> ยกเลิก</b></td><td>ยกเลิกให้ลูกค้า — แอดมินยกเลิกถือว่าทันเวลาเสมอ <b>คืนเครดิตให้</b></td></tr>
          </tbody>
        </table>
        </div>

        <div class="note ok">
          <i class="bi bi-arrow-counterclockwise"></i>
          <span>
            <b>กดผิดแก้ได้:</b> ถ้าเผลอกด "ไม่มาเรียน" ทั้งที่ลูกค้ามาจริง (หรือระบบปิดให้อัตโนมัติเพราะลืมเช็คอิน)
            ไปที่เมนู <b>การจอง</b> แล้วกดปุ่มย้อนสถานะ ระบบจะดึงกลับเป็น "ยืนยันแล้ว" ให้กดใหม่ตามจริง
          </span>
        </div>

        <h3>งานอื่นในหน้านี้</h3>
        <ul>
          <li>
            <b>จองให้ลูกค้า</b> — ลูกค้าโทรมาจอง กดปุ่มนี้แล้วค้นชื่อ
            ระบบตัดเครดิตและเช็คโควตาเหมือนลูกค้าจองเอง ถ้าเต็มจะเข้าคิวสำรองให้อัตโนมัติ
          </li>
          <li>
            <b>บันทึกครูสอนแทน</b> — ครูหลักลา เลือกครูสอนแทนจากช่องด้านขวา
            ชื่อครูสอนแทนจะไปแสดงให้ลูกค้าเห็นด้วย
          </li>
          <li>
            <b>แก้ไขรายละเอียดรอบ</b> — เปลี่ยนเวลา ห้อง จำนวนที่นั่ง หรือเครดิตที่ใช้ เฉพาะรอบนี้รอบเดียว
          </li>
        </ul>

        <figure class="shot">
          <img src="{{ $img('06-session-edit') }}" alt="แก้ไขรอบเรียน" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>แก้ไขรายละเอียดรอบ</b> — มีผลเฉพาะรอบนี้ ไม่กระทบแม่แบบ</figcaption>
        </figure>

        <div class="note warn">
          <i class="bi bi-people"></i>
          <span>
            <b>ลดที่นั่งต่ำกว่าคนที่จองแล้วไม่ได้</b> ถ้ามีคนจอง 3 คน จะลดเหลือ 2 ไม่ได้
            ต้องยกเลิกการจองของใครสักคนก่อน ในทางกลับกัน ถ้า<b>เพิ่ม</b>ที่นั่งแล้วมีคิวสำรองรออยู่
            ระบบจะเลื่อนคิวขึ้นมาเป็นผู้จองให้ทันทีพร้อมตัดเครดิต
          </span>
        </div>

        <h3>ยกเลิกทั้งรอบ</h3>
        <p>
          ใช้เมื่อคลาสสอนไม่ได้จริงๆ เช่น ครูป่วยและหาคนแทนไม่ได้ ต้องกรอกเหตุผลทั้งภาษาไทยและอังกฤษ
          เพราะข้อความนี้จะถูกส่งไปแจ้งลูกค้า
        </p>
        <div class="note dang">
          <i class="bi bi-exclamation-octagon"></i>
          <span>
            กดแล้ว<b>ระบบคืนเครดิตให้ผู้จองทุกคนอัตโนมัติ</b>และส่งแจ้งเตือน ย้อนกลับไม่ได้
            ถ้าแค่เปลี่ยนครูให้ใช้ "ครูสอนแทน" แทน
          </span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── ตารางประจำสัปดาห์ ───────── --}}
  <section class="man-sec" id="schedules">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-arrow-repeat"></i></div>
        <div><h2>ตารางประจำสัปดาห์</h2><div class="sub">แม่แบบที่ระบบใช้สร้างรอบเรียนให้อัตโนมัติ</div></div>
        <span class="where">/admin/schedules</span>
      </div>
      <div class="man-body">
        <p>
          ตั้งครั้งเดียวแล้วระบบสร้างคลาสให้ล่วงหน้าเรื่อยๆ ไม่ต้องมานั่งสร้างรอบเองทุกสัปดาห์
          หน้านี้แสดงเป็นคอลัมน์ 7 วัน
        </p>

        <figure class="shot">
          <img src="{{ $img('07-schedules') }}" alt="ตารางประจำสัปดาห์" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ตารางประจำสัปดาห์</b> — แม่แบบแยกตามวันในสัปดาห์</figcaption>
        </figure>

        <h3>เพิ่มคลาสประจำ</h3>
        <p>กดปุ่ม <b>+</b> ที่หัวคอลัมน์ของวันที่ต้องการ หรือปุ่ม "เพิ่มคลาสประจำ" มุมขวาบน</p>

        <figure class="shot">
          <img src="{{ $img('08-schedule-form') }}" alt="ฟอร์มตารางประจำสัปดาห์" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ฟอร์มคลาสประจำ</b></figcaption>
        </figure>

        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:26%">ช่อง</th><th>อธิบาย</th></tr></thead>
          <tbody>
            <tr><td><b>วันในสัปดาห์ / เวลาเริ่ม</b></td><td>คลาสนี้จะเกิดซ้ำทุกสัปดาห์ในวันและเวลานี้</td></tr>
            <tr><td><b>ระยะเวลา (นาที)</b></td><td>ใช้คำนวณเวลาจบ ตั้งได้ 15–240 นาที</td></tr>
            <tr><td><b>จำนวนที่นั่ง</b></td><td>รับได้กี่คนต่อรอบ ปกติตามจำนวนเครื่องในห้อง</td></tr>
            <tr><td><b>เครดิตที่ใช้</b></td><td>จองคลาสนี้ตัดกี่เครดิต ปกติคือ 1</td></tr>
            <tr><td><b>เริ่มใช้ตั้งแต่</b></td><td>ระบบจะสร้างรอบตั้งแต่วันนี้เป็นต้นไป</td></tr>
            <tr><td><b>ใช้ถึงวันที่</b></td><td>เว้นว่างไว้ = ใช้ตลอดไป ใส่วันที่ = หยุดสร้างรอบหลังวันนั้น (เช่น คลาสตามฤดูกาล)</td></tr>
          </tbody>
        </table>
        </div>

        <h3>ปุ่มสร้างรอบเรียน</h3>
        <p>
          ปกติระบบสร้างรอบล่วงหน้าให้เองทุกคืนอยู่แล้ว ปุ่มนี้ใช้เมื่ออยากเห็นผลทันที
          เช่น เพิ่งเพิ่มคลาสใหม่แล้วอยากให้ลูกค้าจองได้เลย ระบุจำนวนวันแล้วกด "สร้างรอบเรียน"
        </p>
        <p>ระบบจะรายงานผลว่าสร้างใหม่กี่รอบ มีอยู่แล้วกี่รอบ และข้ามวันหยุดไปกี่รอบ</p>

        <div class="note tip">
          <i class="bi bi-shield-check"></i>
          <span>กดซ้ำกี่ครั้งก็ปลอดภัย ระบบไม่สร้างรอบซ้ำในวันและเวลาเดียวกัน</span>
        </div>

        <h3>ปิดคลาสประจำ</h3>
        <p>
          กดลบที่แม่แบบ ระบบจะ<b>ปิดการใช้งาน</b>แม่แบบ (ไม่ได้ลบทิ้ง เพื่อเก็บประวัติ)
          แล้วลบเฉพาะรอบในอนาคตที่<b>ยังไม่มีใครจอง</b>
          ส่วนรอบที่มีคนจองแล้วจะถูกเก็บไว้ ต้องเข้าไปจัดการรายรอบเองที่เมนูรอบเรียน
        </p>
      </div>
    </div>
  </section>

  {{-- ───────── การจอง ───────── --}}
  <section class="man-sec" id="bookings">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-check2-square"></i></div>
        <div><h2>การจอง</h2><div class="sub">ค้นหาและแก้ไขการจองทุกรายการ</div></div>
        <span class="where">/admin/bookings</span>
      </div>
      <div class="man-body">
        <p>
          รวมการจองทั้งหมดไว้ที่เดียว ใช้ตอนต้องตามหาการจองรายการใดรายการหนึ่ง
          เช่น ลูกค้าโทรมาถามว่าจองไว้วันไหน หรือต้องแก้สถานะย้อนหลัง
        </p>

        <figure class="shot">
          <img src="{{ $img('09-bookings') }}" alt="รายการจองทั้งหมด" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>รายการจองทั้งหมด</b> — ค้นหาด้วยรหัสจอง ชื่อ หรือเบอร์โทร</figcaption>
        </figure>

        <h3>ตัวกรอง</h3>
        <p>กรองได้พร้อมกันหลายเงื่อนไข: คำค้น (รหัสจอง/ชื่อ/เบอร์/รหัสสมาชิก), สาขา, สถานะ และช่วงวันที่ของคลาส</p>

        <figure class="shot">
          <img src="{{ $img('10-bookings-filter') }}" alt="กรองเฉพาะที่เข้าเรียนแล้ว" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>กรองเฉพาะ "เข้าเรียนแล้ว"</b></figcaption>
        </figure>

        <h3>ความหมายของสถานะ</h3>
        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:20%">สถานะ</th><th>หมายถึง</th><th style="width:18%">เครดิต</th></tr></thead>
          <tbody>
            <tr><td><b>ยืนยันแล้ว</b></td><td>จองสำเร็จ ได้ที่นั่งแน่นอน รอถึงวันเรียน</td><td>ตัดแล้ว</td></tr>
            <tr><td><b>คิวสำรอง</b></td><td>คลาสเต็ม อยู่ในคิว ถ้ามีคนยกเลิกจะเลื่อนขึ้นอัตโนมัติ</td><td>ยังไม่ตัด</td></tr>
            <tr><td><b>เข้าเรียนแล้ว</b></td><td>เช็คอินแล้ว มาเรียนจริง</td><td>ตัดแล้ว</td></tr>
            <tr><td><b>ยกเลิก</b></td><td>ยกเลิกทันกำหนด (ก่อนคลาสเกิน 6 ชั่วโมง)</td><td><b>คืนให้</b></td></tr>
            <tr><td><b>ยกเลิกช้า</b></td><td>ยกเลิกกระชั้นชิดกว่ากำหนด</td><td>ไม่คืน</td></tr>
            <tr><td><b>ไม่มาเรียน</b></td><td>ไม่มาและไม่แจ้งยกเลิก</td><td>ไม่คืน</td></tr>
          </tbody>
        </table>
        </div>

        <div class="note tip">
          <i class="bi bi-arrow-counterclockwise"></i>
          <span>
            <b>ปุ่มย้อนสถานะ</b> ใช้แก้กรณีที่ปิดผลไปแล้วแต่ไม่ตรงความจริง
            ระบบดึงกลับเป็น "ยืนยันแล้ว" แล้วจัดการเครดิตให้ถูกต้องเอง —
            ถ้าเคยคืนเครดิตไปตอนยกเลิก ระบบจะตัดกลับ และถ้าคลาสเต็มไปแล้วจะเตือนว่าย้อนไม่ได้
          </span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── เคาน์เตอร์ ───────── --}}
  <section class="man-sec" id="counter">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-shop"></i></div>
        <div><h2>เคาน์เตอร์</h2><div class="sub">งานหน้าร้านที่ทำบ่อย รวมไว้ในหน้าเดียว</div></div>
        <span class="where">/admin/counter</span>
      </div>
      <div class="man-body">
        <p>
          ออกแบบมาให้จบใน 2 ขั้น — ค้นหาลูกค้า แล้วกดปุ่มที่ตรงกับสถานการณ์
          ไม่ต้องเข้าโปรไฟล์ลูกค้าแล้วหาปุ่มเอง และไม่ต้องพิมพ์เลขติดลบ
        </p>

        <figure class="shot">
          <img src="{{ $img('11-counter-empty') }}" alt="หน้าเคาน์เตอร์ก่อนค้นหา" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>หน้าเคาน์เตอร์</b> — เริ่มจากช่องค้นหา</figcaption>
        </figure>

        <h3>ค้นหาลูกค้า</h3>
        <p>
          พิมพ์อะไรก็ได้ที่จำได้ — ชื่อจริง นามสกุล <b>ชื่อเล่น</b> เบอร์โทร (พิมพ์แค่เลขท้ายก็ได้)
          รหัสสมาชิก หรืออีเมล ถ้าเจอคนเดียวระบบเปิดให้เลยไม่ต้องกดซ้ำ
        </p>

        <figure class="shot">
          <img src="{{ $img('12-counter-result') }}" alt="หน้าเคาน์เตอร์หลังเลือกลูกค้า" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>หลังเลือกลูกค้า</b> — เครดิตคงเหลือ ปุ่มหัก/เพิ่มเครดิต และประวัติล่าสุด</figcaption>
        </figure>

        <h3>หักเครดิต — เลือกโหมดให้ถูกก่อน</h3>
        <p>
          ช่องหักเครดิตมี <b>2 โหมด</b> ให้เลือกด้านบน เลือกผิดยอดคนเข้าคลาสจะไม่ตรง
          หลักง่ายๆ คือ <b>ถ้าลูกค้าเข้าเรียนคลาสจริง ให้ใช้โหมดแรกเสมอ</b>
        </p>

        <h4><i class="bi bi-person-walking"></i> โหมด "มาเรียนคลาสนี้" — ใช้เมื่อลูกค้าเข้าเรียนจริง</h4>
        <p>
          ใช้กับ <b>ลูกค้า walk-in ที่เดินเข้ามาเรียนสด</b> หรือคนที่เรียนไปแล้วแต่ลืมบันทึก
          เลือกคลาสจากรายการ แล้วกดปุ่มเดียวจบ ระบบจะ:
        </p>
        <ul>
          <li><b>สร้างการจองจริงและเช็คอินให้เลย</b> — ไม่ต้องไปกดเช็คอินซ้ำที่หน้าอื่น</li>
          <li><b>หักเครดิตตามที่คลาสนั้นกำหนด</b> — คลาสที่ใช้ 2 เครดิตก็หัก 2 ไม่ต้องจำเอง</li>
          <li><b>นับเป็นคนเข้าเรียน</b> — โผล่ในรายชื่อคลาส รายงานครู และยอดคนเข้าเรียน</li>
        </ul>
        <p>
          รายการคลาสจะโชว์เฉพาะ<b>คลาสของวันนี้ที่ยังไม่จบ</b> พร้อมเวลา ครู จำนวนคนที่จองแล้ว
          และจำนวนเครดิตที่จะถูกหัก <b>คลาสที่เริ่มไปแล้วก็ยังเลือกได้</b>
          เพราะ walk-in ส่วนใหญ่มาถึงตอนคลาสเริ่มไปแล้ว
        </p>
        <div class="note warn">
          <i class="bi bi-people"></i>
          <span>
            ถ้าคลาส<b>เต็มแล้ว</b> ระบบจะเตือนก่อนและยังไม่บันทึก ถ้าครูรับไหวให้<b>กดยืนยันอีกครั้ง</b>
            ระบบจะรับเกินความจุให้ — การตัดสินใจว่าห้องรับไหวไหมเป็นของพนักงานกับครู ไม่ใช่ของระบบ
          </span>
        </div>

        <h4><i class="bi bi-sliders"></i> โหมด "ปรับเครดิตอย่างเดียว" — ใช้เมื่อไม่เกี่ยวกับคลาส</h4>
        <p>ใช้เฉพาะตอนแก้ตัวเลขบัญชี ไม่มีการเข้าเรียนมาเกี่ยวข้อง</p>
        <ul>
          <li><b>แก้ยอดที่ผิดพลาด (หักออก)</b> — เคยเติมเกิน ต้องหักกลับ</li>
          <li><b>อื่นๆ</b> — กรณีนอกเหนือจากนี้ ต้องพิมพ์เหตุผลเอง</li>
        </ul>
        <div class="note tip">
          <i class="bi bi-magic"></i>
          <span>
            ระบบ<b>ตัดจากแพ็กที่ใกล้หมดอายุก่อนอัตโนมัติ</b> และตัดข้ามหลายแพ็กได้ถ้าใบเดียวไม่พอ
            ไม่ต้องเลือกเองว่าจะตัดใบไหน
          </span>
        </div>
        <div class="note warn">
          <i class="bi bi-exclamation-triangle"></i>
          <span>
            โหมดนี้<b>ไม่นับเป็นคนเข้าคลาส</b> — ถ้าลูกค้าเข้าเรียนจริงแล้วมาหักด้วยโหมดนี้
            เครดิตจะถูกหักก็จริง แต่ยอดคนเข้าคลาสกับรายงานครูจะขาดคนนี้ไป
          </span>
        </div>

        <h3>เพิ่มเครดิต</h3>
        <p>ใช้เมื่อต้องคืนหรือชดเชยให้ลูกค้า</p>
        <ul>
          <li><b>ชดเชยคลาสที่ถูกยกเลิก</b> / <b>ชดเชยจากปัญหาการให้บริการ</b></li>
          <li><b>คืนเครดิตให้ลูกค้า</b> / <b>แก้ยอดที่ผิดพลาด (เพิ่มคืน)</b></li>
          <li><b>โปรโมชัน/ของแถม</b> — เช่น แนะนำเพื่อนมาสมัคร</li>
        </ul>
        <p>เลือกได้ว่าจะเติมเข้าแพ็กใบไหน ถ้าไม่เลือกระบบจะเติมเข้าใบที่ใกล้หมดอายุที่สุด</p>

        <div class="note warn">
          <i class="bi bi-pencil"></i>
          <span>
            เลือกเหตุผล <b>"อื่นๆ"</b> ต้องพิมพ์เหตุผลเองเสมอ ระบบบังคับ
            เพราะประวัติที่อ่านไม่รู้เรื่องจะกลายเป็นปัญหาตอนลูกค้าทักถามย้อนหลัง
          </span>
        </div>

        <p>
          ทุกรายการถูกบันทึกพร้อม<b>ชื่อพนักงานที่ทำ</b>และเวลา ดูย้อนหลังได้ที่การ์ด
          "ความเคลื่อนไหวเครดิตล่าสุด" ด้านล่าง
        </p>
      </div>
    </div>
  </section>

  {{-- ───────── ลูกค้า ───────── --}}
  <section class="man-sec" id="customers">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-people"></i></div>
        <div><h2>ลูกค้า</h2><div class="sub">ข้อมูลสมาชิก แพ็กเกจ และประวัติทั้งหมด</div></div>
        <span class="where">/admin/customers</span>
      </div>
      <div class="man-body">

        <figure class="shot">
          <img src="{{ $img('13-customers') }}" alt="รายชื่อลูกค้า" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>รายชื่อลูกค้า</b> — ค้นหาและกรองตามสถานะ</figcaption>
        </figure>

        <h3>โปรไฟล์ลูกค้า</h3>
        <p>คลิกที่ชื่อเพื่อเปิดดูข้อมูลทั้งหมดของคนนั้น หน้านี้รวมทุกอย่างที่ต้องรู้ไว้แล้ว</p>

        <figure class="shot">
          <img src="{{ $img('14-customer-show') }}" alt="โปรไฟล์ลูกค้า" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>โปรไฟล์ลูกค้า</b> — แพ็กเกจ คลาสที่จะถึง ประวัติการเรียน และบัญชีเครดิต</figcaption>
        </figure>

        <ul>
          <li><b>แพ็กเกจ</b> — ทุกใบที่ถืออยู่ เครดิตเหลือเท่าไหร่ หมดอายุเมื่อไหร่</li>
          <li><b>คลาสที่กำลังจะถึง</b> — จองอะไรไว้บ้าง</li>
          <li><b>ประวัติการเรียน</b> — 20 รายการล่าสุด</li>
          <li><b>ประวัติเครดิต</b> — บัญชีเดินสะพัด ตอบได้ทันทีว่าเครดิตหายไปไหน ใครทำ เมื่อไหร่</li>
        </ul>

        <h3>ฟรีซแพ็กเกจ</h3>
        <p>
          ลูกค้าเจ็บตัวหรือเดินทางไกล ขอหยุดพักชั่วคราว — กดปุ่มฟรีซที่แพ็กใบนั้น
          ระหว่างฟรีซลูกค้าจะจองด้วยแพ็กนี้ไม่ได้
        </p>
        <div class="note ok">
          <i class="bi bi-calendar-plus"></i>
          <span>
            ตอนยกเลิกฟรีซ ระบบ<b>ขยายวันหมดอายุออกไปเท่าจำนวนวันที่ฟรีซ</b>ให้อัตโนมัติ
            ลูกค้าไม่เสียสิทธิ์ (ตั้งค่าเพดานได้ที่ตั้งค่าระบบ ปัจจุบัน 30 วันต่อแพ็ก)
          </span>
        </div>

        <h3>ปรับเครดิตเอง</h3>
        <p>
          ปุ่ม "ปรับเครดิตเอง" ใช้แก้ยอดของแพ็กใบใดใบหนึ่งโดยเฉพาะ ใส่เลขบวกเพื่อเพิ่ม ใส่เลขลบเพื่อหัก
          <b>ต้องระบุเหตุผลเสมอ</b>
        </p>
        <div class="note tip">
          <i class="bi bi-info-circle"></i>
          <span>
            งานประจำวันแนะนำให้ใช้หน้า <b>เคาน์เตอร์</b> แทน เพราะเลือกเหตุผลสำเร็จรูปได้
            และระบบเลือกแพ็กที่จะตัดให้เอง ปุ่มนี้เหมาะกับกรณีที่ต้องเจาะจงใบ
            ส่วนลูกค้าที่<b>เข้าเรียนคลาสจริง</b> ให้ใช้โหมด <b>"มาเรียนคลาสนี้"</b> ที่หน้าเคาน์เตอร์
            ไม่ใช่ปุ่มนี้ ไม่งั้นยอดคนเข้าคลาสจะไม่ตรง
          </span>
        </div>

        <h3>เพิ่ม/แก้ไขลูกค้า</h3>

        <figure class="shot">
          <img src="{{ $img('15-customer-form') }}" alt="ฟอร์มข้อมูลลูกค้า" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ฟอร์มข้อมูลลูกค้า</b></figcaption>
        </figure>

        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:26%">ช่อง</th><th>หมายเหตุ</th></tr></thead>
          <tbody>
            <tr><td><b>เบอร์โทร</b> (บังคับ)</td><td>ลูกค้าใช้ล็อกอิน ห้ามซ้ำกับคนอื่น ระบบเก็บเป็นตัวเลขล้วนให้เอง</td></tr>
            <tr><td><b>รหัสผ่าน</b></td><td>เว้นว่างได้ ลูกค้าไปตั้งเองตอนผูก LINE ทีหลัง</td></tr>
            <tr><td><b>ข้อมูลสุขภาพ</b></td><td><b>สำคัญมาก</b> — ครูเห็นก่อนสอน เช่น ปวดหลัง เคยผ่าตัด</td></tr>
            <tr><td><b>กำลังตั้งครรภ์</b></td><td>ติ๊กไว้เพื่อเตือนครูให้เลี่ยงท่าที่ไม่เหมาะ</td></tr>
            <tr><td><b>ผู้ติดต่อฉุกเฉิน</b></td><td>ใช้ตอนเกิดเหตุระหว่างเรียน</td></tr>
            <tr><td><b>โน้ตภายใน</b></td><td>ลูกค้าไม่เห็น ใช้บันทึกเรื่องที่พนักงานควรรู้</td></tr>
            <tr><td><b>สถานะ</b></td><td>active = ใช้งานได้ / inactive = พักชั่วคราว / banned = ระงับ</td></tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── คำสั่งซื้อ ───────── --}}
  <section class="man-sec" id="orders">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-receipt"></i></div>
        <div><h2>คำสั่งซื้อ</h2><div class="sub">ขายแพ็กเกจและยืนยันการชำระเงิน</div></div>
        <span class="where">/admin/orders</span>
      </div>
      <div class="man-body">
        <p>
          เมนูนี้มีตัวเลขสีแดงเตือนเมื่อมีสลิปรอตรวจ
          ลูกค้าที่โอนเงินแล้วจะยังไม่ได้เครดิตจนกว่าจะมีคนกดยืนยัน
        </p>

        <figure class="shot">
          <img src="{{ $img('16-orders') }}" alt="รายการคำสั่งซื้อ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>รายการคำสั่งซื้อ</b></figcaption>
        </figure>

        <h3>งานที่ 1 — ยืนยันสลิปที่ลูกค้าโอนมา</h3>
        <p>เปิดบิลที่สถานะ "รอชำระ" ดูรูปสลิปที่ลูกค้าแนบมา เทียบยอดกับบิล แล้วตัดสินใจ</p>

        <figure class="shot">
          <img src="{{ $img('18-order-pending') }}" alt="บิลรอยืนยันพร้อมสลิป" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>บิลรอยืนยัน</b> — ดูสลิปแล้วกดยืนยันหรือปฏิเสธ</figcaption>
        </figure>

        <ol class="steps">
          <li>ตรวจว่ายอดในสลิปตรงกับยอดสุทธิของบิล และโอนเข้าบัญชีของสตูดิโอจริง</li>
          <li>กด <b>ยืนยัน</b> — ระบบออกแพ็กและเพิ่มเครดิตให้ลูกค้าทันที พร้อมส่งแจ้งเตือน</li>
          <li>ถ้าสลิปไม่ถูกต้อง กด <b>ปฏิเสธ</b> แล้วระบุเหตุผล ลูกค้าจะเห็นเหตุผลนั้น</li>
        </ol>

        <div class="note ok">
          <i class="bi bi-check-circle"></i>
          <span>
            กดยืนยันครั้งเดียวจบ — ระบบออกแพ็ก เพิ่มเครดิต เปลี่ยนสถานะบิลเป็น "ชำระแล้ว"
            และบันทึกชื่อคนที่อนุมัติไว้ให้ครบ กดซ้ำไม่ได้ ระบบกันไว้แล้ว
          </span>
        </div>

        <h3>งานที่ 2 — เปิดบิลขายหน้าร้าน</h3>
        <p>ลูกค้ามาซื้อแพ็กที่สตูดิโอ กดปุ่ม "เปิดบิลใหม่"</p>

        <figure class="shot">
          <img src="{{ $img('19-order-create') }}" alt="ฟอร์มเปิดบิลใหม่" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>เปิดบิลใหม่</b></figcaption>
        </figure>

        <ol class="steps">
          <li>เลือกลูกค้าและแพ็กเกจ ระบุจำนวน</li>
          <li>ใส่ส่วนลดถ้ามี พร้อมเหตุผล (เช่น ส่วนลดลูกค้าเก่า)</li>
          <li>
            รับเงินแล้ว ให้ติ๊ก <b>"ชำระแล้ว"</b> และเลือกวิธีชำระ —
            ระบบจะออกแพ็กให้ทันทีโดยไม่ต้องรอยืนยันอีกรอบ
          </li>
        </ol>

        <div class="note warn">
          <i class="bi bi-1-circle"></i>
          <span>
            แพ็ก<b>ทดลอง</b>ซื้อได้คนละครั้งเดียว ถ้าลูกค้าเคยซื้อไปแล้วระบบจะเตือนและไม่ให้เปิดบิลซ้ำ
          </span>
        </div>

        <h3>หน้ารายละเอียดบิล</h3>

        <figure class="shot">
          <img src="{{ $img('17-order-show-paid') }}" alt="บิลที่ชำระแล้ว" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>บิลที่ชำระเรียบร้อย</b> — เห็นรายการ ยอดเงิน และแพ็กที่ออกให้</figcaption>
        </figure>

        <p>ในหน้านี้ยังทำได้อีก 2 อย่าง:</p>
        <ul>
          <li><b>บันทึกการชำระเงินเพิ่ม</b> — กรณีลูกค้าแบ่งจ่ายหลายครั้ง ระบบออกแพ็กให้เมื่อยอดรวมครบ</li>
          <li><b>ยกเลิกบิล</b> — ทำได้เฉพาะบิลที่ยังไม่ชำระ</li>
        </ul>
        <div class="note dang">
          <i class="bi bi-slash-circle"></i>
          <span>บิลที่<b>ชำระแล้วยกเลิกไม่ได้</b> เพราะเครดิตออกไปแล้ว ต้องทำรายการคืนเงินแทน</span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── แพ็กเกจ ───────── --}}
  <section class="man-sec" id="packages">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-ticket-perforated"></i></div>
        <div><h2>แพ็กเกจ</h2><div class="sub">สินค้าที่เปิดขาย</div></div>
        <span class="where">/admin/packages</span>
      </div>
      <div class="man-body">

        <figure class="shot">
          <img src="{{ $img('20-packages') }}" alt="รายการแพ็กเกจ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>รายการแพ็กเกจ</b></figcaption>
        </figure>

        <h3>แพ็กเกจ 3 แบบ</h3>
        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:20%">แบบ</th><th>ใช้งานอย่างไร</th></tr></thead>
          <tbody>
            <tr>
              <td><b>นับครั้ง</b><br><span class="text-secondary">credit_pack</span></td>
              <td>ซื้อกี่ครั้งได้เครดิตเท่านั้น จองแต่ละครั้งตัดเครดิต — แบบที่ใช้มากที่สุด</td>
            </tr>
            <tr>
              <td><b>เหมาจ่าย</b><br><span class="text-secondary">unlimited</span></td>
              <td>ไม่จำกัดจำนวนครั้ง จองไม่ตัดเครดิต ดูแค่วันหมดอายุ — ควรตั้งโควตาต่อวัน/สัปดาห์กันจองรัวทิ้ง</td>
            </tr>
            <tr>
              <td><b>ทดลอง</b><br><span class="text-secondary">trial</span></td>
              <td>สำหรับลูกค้าใหม่ ติ๊ก "ซื้อได้ครั้งเดียวต่อคน" ระบบกันซื้อซ้ำให้</td>
            </tr>
          </tbody>
        </table>
        </div>

        <figure class="shot">
          <img src="{{ $img('21-package-form') }}" alt="ฟอร์มแพ็กเกจ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ฟอร์มแพ็กเกจ</b></figcaption>
        </figure>

        <h3>ช่องที่ควรเข้าใจ</h3>
        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:28%">ช่อง</th><th>อธิบาย</th></tr></thead>
          <tbody>
            <tr><td><b>ใช้ได้กี่วัน</b></td><td>นับจากวันที่ซื้อ เป็นตัวที่ใช้คำนวณวันหมดอายุจริง</td></tr>
            <tr><td><b>ราคาก่อนลด</b></td><td>ไว้โชว์ขีดฆ่าให้เห็นว่าลดเยอะ ไม่ใส่ก็ได้</td></tr>
            <tr><td><b>จำกัดประเภทคลาส</b></td><td>ปิด "ใช้ได้ทุกประเภท" แล้วเลือกเฉพาะคลาสที่แพ็กนี้ใช้ได้</td></tr>
            <tr><td><b>โควตาต่อวัน/สัปดาห์</b></td><td>กันลูกค้าจองรัวทิ้ง โดยเฉพาะแพ็กเหมาจ่าย</td></tr>
            <tr><td><b>จองล่วงหน้าได้สูงสุด</b></td><td>ถือการจองในอนาคตพร้อมกันได้กี่รายการ</td></tr>
            <tr><td><b>แสดงหน้าเว็บ</b></td><td>ปิด = ขายหน้าร้านเท่านั้น ลูกค้าไม่เห็นในแอป</td></tr>
          </tbody>
        </table>
        </div>

        <div class="note ok">
          <i class="bi bi-lock"></i>
          <span>
            แก้ราคาหรือเงื่อนไขแล้ว <b>แพ็กที่ลูกค้าซื้อไปแล้วไม่เปลี่ยนตาม</b>
            เพราะระบบคัดลอกเงื่อนไข ณ วันที่ขายไว้ในใบของลูกค้า ใบเก่าจึงยังใช้เงื่อนไขเดิมเสมอ
          </span>
        </div>
        <div class="note warn">
          <i class="bi bi-trash"></i>
          <span>แพ็กที่มีลูกค้าซื้อไปแล้ว<b>ลบไม่ได้</b> ให้ปิดใช้งานแทน ของเก่าจะได้ยังอ้างอิงได้</span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── สาขา ───────── --}}
  <section class="man-sec" id="branches">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-geo-alt"></i></div>
        <div><h2>สาขาและห้อง</h2><div class="sub">สถานที่และห้องเรียน</div></div>
        <span class="where">/admin/branches</span>
      </div>
      <div class="man-body">

        <figure class="shot">
          <img src="{{ $img('22-branches') }}" alt="รายการสาขา" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>สาขาและห้อง</b></figcaption>
        </figure>

        <figure class="shot">
          <img src="{{ $img('23-branch-form') }}" alt="ฟอร์มสาขา" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ฟอร์มสาขา</b> — ข้อมูลติดต่อและเวลาทำการ</figcaption>
        </figure>

        <h3>ห้องเรียน</h3>
        <p>
          แต่ละสาขามีได้หลายห้อง เพิ่มห้องได้จากหน้าสาขาโดยตรง
          ใส่<b>จำนวนที่นั่ง</b>ตามจำนวนเครื่องจริงในห้อง เพราะเป็นค่าตั้งต้นตอนสร้างคลาส
        </p>

        <div class="note warn">
          <i class="bi bi-trash"></i>
          <span>สาขาที่มีรอบเรียนอยู่<b>ลบไม่ได้</b> ให้ปิดใช้งานแทน</span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── ครู ───────── --}}
  <section class="man-sec" id="trainers">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-person-badge"></i></div>
        <div><h2>ครูผู้สอน</h2><div class="sub">ประวัติครูและลิงก์ดูตารางสอน</div></div>
        <span class="where">/admin/trainers</span>
      </div>
      <div class="man-body">

        <figure class="shot">
          <img src="{{ $img('24-trainers') }}" alt="รายชื่อครูผู้สอน" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>รายชื่อครูผู้สอน</b></figcaption>
        </figure>

        <figure class="shot">
          <img src="{{ $img('25-trainer-form') }}" alt="ฟอร์มครูผู้สอน" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ฟอร์มครูผู้สอน</b> — ประวัติสองภาษาและรูปโปรไฟล์</figcaption>
        </figure>

        <p>ข้อมูลครู (ชื่อ ประวัติ ความเชี่ยวชาญ รูป) แสดงบนหน้าเว็บให้ลูกค้าเห็น จึงควรกรอกให้ครบทั้งไทยและอังกฤษ</p>

        <h3>ลิงก์ตารางสอนของครู</h3>
        <p>
          ระบบสร้างลิงก์เฉพาะตัวให้ครูแต่ละคน ครู<b>เปิดดูตารางสอนและรายชื่อผู้เรียนได้โดยไม่ต้องล็อกอิน</b>
          ส่งลิงก์นี้ให้ครูทาง LINE ได้เลย
        </p>
        <div class="note dang">
          <i class="bi bi-key"></i>
          <span>
            ลิงก์นี้ใครมีก็เปิดดูได้ ถ้าหลุดออกไปให้กด <b>"สร้างลิงก์ใหม่"</b>
            ลิงก์เดิมจะใช้ไม่ได้ทันที แล้วส่งลิงก์ใหม่ให้ครู
          </span>
        </div>

        <div class="note warn">
          <i class="bi bi-trash"></i>
          <span>ครูที่มีตารางสอนอยู่<b>ลบไม่ได้</b> ให้ปิดใช้งานแทน (ครูลาออกแล้วแต่ประวัติการสอนต้องอยู่)</span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── ประเภทคลาส ───────── --}}
  <section class="man-sec" id="classtypes">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-grid-3x3-gap"></i></div>
        <div><h2>ประเภทคลาส</h2><div class="sub">ชนิดของคลาสที่เปิดสอน</div></div>
        <span class="where">/admin/class-types</span>
      </div>
      <div class="man-body">

        <figure class="shot">
          <img src="{{ $img('26-class-types') }}" alt="รายการประเภทคลาส" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ประเภทคลาส</b></figcaption>
        </figure>

        <figure class="shot">
          <img src="{{ $img('27-class-type-form') }}" alt="ฟอร์มประเภทคลาส" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ฟอร์มประเภทคลาส</b> — ตั้งสีประจำคลาสได้</figcaption>
        </figure>

        <p>
          กรอกชื่อและคำอธิบายสองภาษา เลือก<b>สีประจำคลาส</b>เพื่อให้แยกออกง่ายในตาราง
          ส่วนระยะเวลาและจำนวนที่นั่งตรงนี้เป็นแค่<b>ค่าตั้งต้น</b> ปรับจริงได้ตอนวางตารางประจำสัปดาห์
        </p>

        <div class="note warn">
          <i class="bi bi-trash"></i>
          <span>ประเภทที่มีรอบเรียนใช้อยู่<b>ลบไม่ได้</b> ให้ปิดใช้งานแทน</span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── วันหยุด ───────── --}}
  <section class="man-sec" id="holidays">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-calendar-x"></i></div>
        <div><h2>วันหยุด</h2><div class="sub">วันที่สตูดิโอปิด</div></div>
        <span class="where">/admin/holidays</span>
      </div>
      <div class="man-body">

        <figure class="shot">
          <img src="{{ $img('28-holidays') }}" alt="หน้าวันหยุด" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>วันหยุด</b> — ระบุแยกรายสาขาหรือทุกสาขาก็ได้</figcaption>
        </figure>

        <p>
          ตอนสร้างรอบเรียน ระบบจะ<b>ข้ามวันที่ระบุไว้ที่นี่</b>ให้อัตโนมัติ
          เลือกได้ว่าปิดทุกสาขาหรือเฉพาะสาขาใดสาขาหนึ่ง (เช่น ปิดปรับปรุงห้องเฉพาะสาขาเดียว)
        </p>

        <div class="note dang">
          <i class="bi bi-exclamation-triangle"></i>
          <span>
            <b>ใส่วันหยุดก่อนที่ระบบจะสร้างรอบเสมอ</b> —
            ถ้ามีรอบถูกสร้างไปแล้วในวันนั้น การเพิ่มวันหยุดจะไม่ลบรอบเก่าให้
            ต้องเข้าไปยกเลิกรายรอบเองที่เมนูรอบเรียน
          </span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── ประกาศ ───────── --}}
  <section class="man-sec" id="announcements">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-megaphone"></i></div>
        <div><h2>บทความ/ประกาศ</h2><div class="sub">เนื้อหาที่แสดงให้ลูกค้าเห็น</div></div>
        <span class="where">/admin/announcements</span>
      </div>
      <div class="man-body">

        <figure class="shot">
          <img src="{{ $img('29-announcements') }}" alt="รายการประกาศ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>บทความ/ประกาศ</b></figcaption>
        </figure>

        <figure class="shot">
          <img src="{{ $img('30-announcement-form') }}" alt="ฟอร์มประกาศ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ฟอร์มประกาศ</b> — กรอกสองภาษา แนบรูปได้</figcaption>
        </figure>

        <h3>ประเภทของประกาศ</h3>
        <ul>
          <li><b>ข้อมูลทั่วไป</b> — ข่าวสาร บทความให้ความรู้</li>
          <li><b>โปรโมชัน</b> — แคมเปญ ส่วนลด</li>
          <li><b>แจ้งเตือน</b> — เรื่องด่วนที่ลูกค้าต้องรู้ เช่น ปิดปรับปรุง</li>
        </ul>

        <h3>กำหนดช่วงเวลาแสดงผล</h3>
        <p>
          ตั้ง "แสดงตั้งแต่" และ "แสดงถึง" ไว้ล่วงหน้าได้
          โปรโมชันจะขึ้นเองตามวันที่กำหนดและหายไปเองเมื่อหมดเวลา ไม่ต้องมาคอยลบ
        </p>
        <p>เลือกสาขาได้ถ้าประกาศนั้นเกี่ยวกับสาขาเดียว เว้นว่าง = แสดงทุกสาขา</p>
      </div>
    </div>
  </section>

  {{-- ───────── วิดีโอ ───────── --}}
  <section class="man-sec" id="videos">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-play-btn"></i></div>
        <div><h2>คลิปวิดีโอ</h2><div class="sub">คลิปที่แสดงบนหน้าเว็บ</div></div>
        <span class="where">/admin/videos</span>
      </div>
      <div class="man-body">

        <figure class="shot">
          <img src="{{ $img('31-videos') }}" alt="รายการคลิปวิดีโอ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>คลิปวิดีโอ</b></figcaption>
        </figure>

        <figure class="shot">
          <img src="{{ $img('32-video-form') }}" alt="ฟอร์มคลิปวิดีโอ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ฟอร์มคลิปวิดีโอ</b></figcaption>
        </figure>

        <p>
          ใส่ลิงก์คลิปพร้อมชื่อและคำอธิบาย ใส่รูปปกได้เพื่อให้หน้าเว็บดูดี
          ใช้ "ลำดับ" จัดว่าคลิปไหนขึ้นก่อน และปิดใช้งานได้โดยไม่ต้องลบทิ้ง
        </p>
      </div>
    </div>
  </section>

  {{-- ───────── รายงาน ───────── --}}
  <section class="man-sec" id="reports">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-graph-up"></i></div>
        <div><h2>รายงาน</h2><div class="sub">สรุปผลตามช่วงเวลา</div></div>
        <span class="where">/admin/reports</span>
      </div>
      <div class="man-body">
        <p>เลือกช่วงวันที่ด้านบนแล้วกด "ดูรายงาน" ค่าเริ่มต้นคือเดือนปัจจุบัน</p>

        <figure class="shot">
          <img src="{{ $img('33-reports') }}" alt="หน้ารายงาน" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>รายงาน</b> — รายได้ การเข้าเรียน คลาสและครูยอดนิยม</figcaption>
        </figure>

        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:28%">ตัวเลข</th><th>อ่านอย่างไร</th></tr></thead>
          <tbody>
            <tr><td><b>รายได้รวม</b></td><td>ยอดบิลที่ชำระแล้วในช่วงนี้ แยกตามสาขาให้ด้วย</td></tr>
            <tr><td><b>อัตราการเข้าเรียน</b></td><td>สัดส่วนคนที่มาเรียนจริงจากยอดจองทั้งหมด</td></tr>
            <tr><td><b>อัตราไม่มาเรียน</b></td><td>ยิ่งสูงยิ่งเสียโอกาส — ถ้าเกิน 10% ควรทบทวนนโยบายยกเลิก</td></tr>
            <tr><td><b>อัตราการเต็มของคลาส</b></td><td>ที่นั่งถูกใช้ไปกี่เปอร์เซ็นต์ — ต่ำแปลว่าเปิดคลาสถี่เกินไปหรือเวลาไม่ตรงความต้องการ</td></tr>
            <tr><td><b>คลาส/ครูยอดนิยม</b></td><td>ใช้ตัดสินใจว่าควรเพิ่มรอบคลาสไหน หรือจัดตารางครูคนไหนให้มากขึ้น</td></tr>
          </tbody>
        </table>
        </div>

        <div class="note tip">
          <i class="bi bi-printer"></i>
          <span>กด Ctrl+P เพื่อพิมพ์หรือบันทึกเป็น PDF ได้ ระบบซ่อนเมนูให้อัตโนมัติตอนพิมพ์</span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── ผู้ใช้งาน ───────── --}}
  <section class="man-sec" id="users">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-shield-lock"></i></div>
        <div><h2>ผู้ใช้งานระบบ</h2><div class="sub">บัญชีพนักงาน — เฉพาะเจ้าของระบบ</div></div>
        <span class="where">/admin/users</span>
      </div>
      <div class="man-body">
        <div class="note warn">
          <i class="bi bi-shield-lock"></i>
          <span>เมนูนี้และ "ตั้งค่าระบบ" เห็นได้<b>เฉพาะบัญชีระดับเจ้าของระบบ</b>เท่านั้น</span>
        </div>

        <figure class="shot">
          <img src="{{ $img('34-users') }}" alt="รายชื่อผู้ใช้งานระบบ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ผู้ใช้งานระบบ</b></figcaption>
        </figure>

        <h3>สิทธิ์ 3 ระดับ</h3>
        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:22%">ระดับ</th><th>ทำอะไรได้</th></tr></thead>
          <tbody>
            <tr><td><b>เจ้าของระบบ</b><br><span class="text-secondary">owner</span></td>
                <td>ทุกอย่าง รวมถึงจัดการผู้ใช้งานและตั้งค่าระบบ</td></tr>
            <tr><td><b>ผู้จัดการ</b><br><span class="text-secondary">manager</span></td>
                <td>งานประจำวันทั้งหมด แต่แก้ผู้ใช้งานและตั้งค่าระบบไม่ได้</td></tr>
            <tr><td><b>พนักงาน</b><br><span class="text-secondary">staff</span></td>
                <td>งานหน้าร้าน — เช็คอิน ขายแพ็ก ดูแลลูกค้า</td></tr>
          </tbody>
        </table>
        </div>

        <figure class="shot">
          <img src="{{ $img('35-user-form') }}" alt="ฟอร์มผู้ใช้งาน" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ฟอร์มผู้ใช้งาน</b></figcaption>
        </figure>

        <ul>
          <li><b>สาขาที่ดูแล</b> — ระบุแล้วจะเห็นเฉพาะข้อมูลสาขานั้น เว้นว่าง = เห็นทุกสาขา</li>
          <li><b>รหัสผ่าน</b> — ตอนแก้ไข เว้นว่างไว้ = ไม่เปลี่ยน กรอกใหม่ = ตั้งรหัสใหม่ให้</li>
          <li><b>เปิดใช้งาน</b> — ปิดเมื่อพนักงานลาออก จะเข้าระบบไม่ได้ทันทีแต่ประวัติยังอยู่</li>
        </ul>

        <div class="note dang">
          <i class="bi bi-person-lock"></i>
          <span>
            ระบบกันไว้ 2 อย่าง: <b>ลบบัญชีตัวเองไม่ได้</b>
            และ<b>ต้องเหลือเจ้าของระบบอย่างน้อย 1 คนเสมอ</b> กันเหตุการณ์ไม่มีใครเข้าระบบได้เลย
          </span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── ตั้งค่า ───────── --}}
  <section class="man-sec" id="settings">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-sliders"></i></div>
        <div><h2>ตั้งค่าระบบ</h2><div class="sub">กฎการทำงาน — เฉพาะเจ้าของระบบ</div></div>
        <span class="where">/admin/settings</span>
      </div>
      <div class="man-body">
        <div class="note dang">
          <i class="bi bi-exclamation-octagon"></i>
          <span>
            ค่าในหน้านี้<b>กระทบทั้งระบบทันที</b> โดยเฉพาะกลุ่มการจองและการยกเลิก
            ควรแจ้งทีมงานก่อนแก้ทุกครั้ง
          </span>
        </div>

        <figure class="shot">
          <img src="{{ $img('36-settings') }}" alt="หน้าตั้งค่าระบบ" loading="lazy">
          <figcaption><i class="bi bi-camera"></i> <b>ตั้งค่าระบบ</b> — แบ่งเป็นกลุ่มตามเรื่อง</figcaption>
        </figure>

        <h3>การจอง</h3>
        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:34%">ค่า</th><th>ความหมาย</th></tr></thead>
          <tbody>
            <tr><td><b>ปิดรับจองก่อนคลาสเริ่มกี่นาที</b></td><td>ลูกค้าจองเองไม่ได้เมื่อใกล้เวลานี้ — <b>แอดมินยังจองแทนได้</b></td></tr>
            <tr><td><b>เปิดให้จองล่วงหน้ากี่วัน</b></td><td>ลูกค้าจองไกลสุดได้กี่วันข้างหน้า</td></tr>
            <tr><td><b>ให้ดูตารางย้อนหลังกี่วัน</b></td><td>ลูกค้าเลื่อนดูตารางย้อนหลังได้กี่วัน</td></tr>
            <tr><td><b>สร้างรอบเรียนล่วงหน้ากี่วัน</b></td><td>ระบบสร้างรอบล่วงหน้าไว้กี่วัน ควร ≥ "เปิดให้จองล่วงหน้า"</td></tr>
          </tbody>
        </table>
        </div>

        <h3>การยกเลิก</h3>
        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:34%">ค่า</th><th>ความหมาย</th></tr></thead>
          <tbody>
            <tr><td><b>ยกเลิกฟรีก่อนคลาสกี่ชั่วโมง</b></td><td>ยกเลิกก่อนเวลานี้ = คืนเครดิต / หลังจากนั้น = ยกเลิกช้า</td></tr>
            <tr><td><b>ยกเลิกช้าตัดเครดิตหรือไม่</b></td><td>ปิด = ใจดี คืนเครดิตแม้ยกเลิกช้า</td></tr>
            <tr><td><b>ไม่มาเรียนตัดเครดิตหรือไม่</b></td><td>ปิด = ไม่มาเรียนก็ไม่เสียเครดิต</td></tr>
            <tr><td><b>ปิดคลาสอัตโนมัติหลังคลาสจบกี่นาที</b></td><td>หลังคลาสจบเท่านี้นาที คนที่ไม่ได้เช็คอินจะถูกบันทึกเป็น "ไม่มาเรียน" ให้เอง</td></tr>
          </tbody>
        </table>
        </div>
        <div class="note tip">
          <i class="bi bi-arrow-counterclockwise"></i>
          <span>
            ระบบปิดคลาสให้อัตโนมัติก็จริง แต่ถ้าลูกค้ามาเรียนจริงแล้วพนักงานลืมเช็คอิน
            ใช้ปุ่ม<b>ย้อนสถานะ</b>ที่เมนูการจองแก้ย้อนหลังได้
          </span>
        </div>

        <h3>คิวสำรอง</h3>
        <ul>
          <li><b>เปิดใช้ waitlist</b> — ปิดแล้วลูกค้าจะจองคลาสที่เต็มไม่ได้เลย</li>
          <li><b>เลื่อนคิวอัตโนมัติ</b> — มีคนยกเลิก ระบบดันคิวแรกขึ้นเป็นผู้จองและตัดเครดิตทันที พร้อมส่งแจ้งเตือน</li>
          <li><b>จำนวนคิวสูงสุด</b> — รับคิวสำรองได้กี่คนต่อรอบ</li>
        </ul>

        <h3>แพ็กเกจ</h3>
        <ul>
          <li><b>เตือนก่อนแพ็กหมดอายุกี่วัน</b> — ใส่เป็นชุดตัวเลข เช่น <code>[30,14,3]</code> = เตือน 3 รอบ</li>
          <li><b>ฟรีซแพ็กได้สูงสุดกี่วัน</b> — เพดานการหยุดพักต่อแพ็กหนึ่งใบ</li>
        </ul>

        <h3>บัญชีรับเงิน</h3>
        <p>
          ชื่อบัญชี เลขบัญชี ธนาคาร และพร้อมเพย์ — <b>ลูกค้าเห็นข้อมูลนี้ตอนจะโอนเงิน</b> ต้องถูกต้องเสมอ
          ใส่รูป QR รับเงินได้ และตั้งข้อความแจ้งลูกค้าสองภาษา
        </p>

        <h3>ช่องทางติดต่อและทั่วไป</h3>
        <p>ลิงก์ Facebook / Instagram / LINE / TikTok ที่แสดงบนหน้าเว็บ รวมถึงชื่อสตูดิโอ ภาษาเริ่มต้น และสกุลเงิน</p>
      </div>
    </div>
  </section>

  {{-- ───────── งานอัตโนมัติ ───────── --}}
  <section class="man-sec" id="auto">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-robot"></i></div>
        <div><h2>งานอัตโนมัติ</h2><div class="sub">สิ่งที่ระบบทำให้เองโดยไม่ต้องกด</div></div>
      </div>
      <div class="man-body">
        <p>ระบบทำงานเบื้องหลังเหล่านี้ให้เอง เข้าใจไว้จะได้ไม่งงว่าทำไมข้อมูลเปลี่ยนเอง</p>

        <div class="tbl-wrap">
        <table>
          <thead><tr><th style="width:24%">งาน</th><th style="width:18%">ทำงานเมื่อไหร่</th><th>ทำอะไร</th></tr></thead>
          <tbody>
            <tr>
              <td><b>ปิดคลาสที่จบแล้ว</b></td><td>ทุกชั่วโมง</td>
              <td>คลาสจบแล้วยังไม่ได้เช็คอิน บันทึกเป็น "ไม่มาเรียน" และปิดแพ็กที่หมดอายุ</td>
            </tr>
            <tr>
              <td><b>สร้างรอบเรียน</b></td><td>ทุกวัน 03:00 น.</td>
              <td>สร้างรอบล่วงหน้าจากตารางประจำสัปดาห์ ข้ามวันหยุดให้</td>
            </tr>
            <tr>
              <td><b>เตือนก่อนคลาสเริ่ม</b></td><td>ทุก 15 นาที</td>
              <td>ส่งแจ้งเตือนลูกค้าที่จองไว้ เพื่อลดการไม่มาเรียน</td>
            </tr>
            <tr>
              <td><b>เลื่อนคิวสำรอง</b></td><td>ทันทีที่มีคนยกเลิก</td>
              <td>ดันคิวแรกขึ้นเป็นผู้จอง ตัดเครดิต และแจ้งเตือน</td>
            </tr>
          </tbody>
        </table>
        </div>

        <div class="note warn">
          <i class="bi bi-hdd-network"></i>
          <span>
            งานเหล่านี้ทำงานได้ต่อเมื่อผู้ดูแลเซิร์ฟเวอร์ตั้ง cron ไว้แล้ว
            ถ้าพบว่ารอบเรียนไม่ถูกสร้างเองหรือคลาสเก่าไม่ถูกปิด ให้แจ้งผู้ดูแลระบบตรวจสอบ
          </span>
        </div>
      </div>
    </div>
  </section>

  {{-- ───────── FAQ ───────── --}}
  <section class="man-sec" id="faq">
    <div class="card-panel">
      <div class="man-h">
        <div class="ic"><i class="bi bi-question-circle"></i></div>
        <div><h2>ปัญหาที่พบบ่อย</h2><div class="sub">อาการและวิธีแก้</div></div>
      </div>
      <div class="man-body">

        <h3>ลูกค้าบอกว่าจองไม่ได้</h3>
        <p>ไล่เช็คตามลำดับนี้:</p>
        <ol class="steps">
          <li><b>เครดิตหมดหรือยัง</b> — ดูที่หน้าเคาน์เตอร์หรือโปรไฟล์ลูกค้า</li>
          <li><b>แพ็กหมดอายุหรือถูกฟรีซอยู่หรือเปล่า</b> — แพ็กที่ฟรีซอยู่ใช้จองไม่ได้</li>
          <li><b>เลยเวลาปิดรับจองหรือยัง</b> — ปกติปิดก่อนคลาสเริ่ม 30 นาที ถ้าเลยแล้วให้แอดมินจองแทน</li>
          <li><b>คลาสเต็มหรือเปล่า</b> — ถ้าเต็มจะเข้าคิวสำรองแทน</li>
          <li><b>ติดโควตาต่อวัน/สัปดาห์ของแพ็กไหม</b> — ดูเงื่อนไขที่หน้าแพ็กเกจ</li>
          <li><b>แพ็กนี้ใช้กับคลาสประเภทนี้ได้ไหม</b> — บางแพ็กจำกัดประเภทคลาส</li>
        </ol>

        <h3>ลูกค้าโอนเงินแล้วแต่ยังจองไม่ได้</h3>
        <p>
          เครดิตเข้าต่อเมื่อมีคน<b>กดยืนยันสลิป</b> ไปที่เมนู <b>คำสั่งซื้อ</b>
          เปิดบิลที่รอยืนยัน ตรวจสลิปแล้วกดยืนยัน เครดิตจะเข้าทันที
        </p>

        <h3>เผลอกดไม่มาเรียน / ลืมเช็คอินให้ลูกค้า</h3>
        <p>
          ไปที่เมนู <b>การจอง</b> ค้นหารายการนั้น กดปุ่ม<b>ย้อนสถานะ</b>
          ระบบจะดึงกลับเป็น "ยืนยันแล้ว" แล้วกดเช็คอินใหม่ได้ตามจริง
        </p>

        <h3>แก้ตารางประจำสัปดาห์แล้วรอบเรียนไม่เปลี่ยน</h3>
        <p>
          เป็นการทำงานที่ตั้งใจไว้ เพราะรอบที่สร้างไปแล้วอาจมีคนจองอยู่
          ถ้าต้องการแก้รอบที่จะถึง ให้เข้าไปแก้รายรอบที่เมนู <b>รอบเรียน</b>
        </p>

        <h3>เพิ่มวันหยุดแล้ว แต่ยังมีคลาสในวันนั้น</h3>
        <p>
          วันหยุดมีผลกับรอบที่<b>ยังไม่ถูกสร้าง</b>เท่านั้น
          รอบที่สร้างไปแล้วต้องเข้าไปกดยกเลิกเองที่เมนูรอบเรียน (ระบบจะคืนเครดิตให้ผู้จองอัตโนมัติ)
        </p>

        <h3>ลดจำนวนที่นั่งไม่ได้</h3>
        <p>
          ลดต่ำกว่าจำนวนคนที่จองไปแล้วไม่ได้ ต้องยกเลิกการจองของใครสักคนก่อน
          แล้วค่อยลดที่นั่ง
        </p>

        <h3>ลบสาขา/ครู/ประเภทคลาส/แพ็กเกจไม่ได้</h3>
        <p>
          ระบบกันไว้เมื่อของนั้นถูกใช้งานอยู่ เพื่อไม่ให้ประวัติเก่าเสียหาย
          ให้ใช้ <b>ปิดใช้งาน</b> แทน ผลลัพธ์คือไม่ถูกใช้ต่อไปข้างหน้า แต่ข้อมูลเดิมยังอ้างอิงได้
        </p>

        <h3>ลูกค้าจะยกเลิกคลาส คืนเครดิตไหม</h3>
        <p>
          ยกเลิกก่อนคลาสเกิน 6 ชั่วโมง = คืนเครดิต / ช้ากว่านั้น = ไม่คืน
          แต่ถ้า<b>แอดมินเป็นคนกดยกเลิกให้ ระบบถือว่าทันเวลาเสมอและคืนเครดิต</b>
          ใช้กรณีที่สตูดิโอเป็นฝ่ายผิดหรืออนุโลมให้ลูกค้า
        </p>
      </div>
    </div>
  </section>

  </div>
</div>

{{-- ดูภาพขนาดเต็ม --}}
<div class="lightbox" id="lightbox">
  <button class="x" type="button" aria-label="ปิด">&times;</button>
  <img src="" alt="">
</div>

@endsection

@push('scripts')
<script>
// คลิกภาพเพื่อดูเต็มจอ
(function(){
  var lb = document.getElementById('lightbox');
  var lbImg = lb.querySelector('img');

  document.addEventListener('click', function(e){
    var img = e.target.closest('figure.shot img');
    if (img) { lbImg.src = img.src; lb.classList.add('on'); return; }
    if (e.target.closest('#lightbox')) { lb.classList.remove('on'); lbImg.src = ''; }
  });

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') { lb.classList.remove('on'); lbImg.src = ''; }
  });
})();

// ไฮไลต์หัวข้อที่กำลังอ่านในสารบัญ
(function(){
  var links = Array.prototype.slice.call(document.querySelectorAll('#manToc a'));
  var secs = links
    .map(function(a){ return document.querySelector(a.getAttribute('href')); })
    .filter(Boolean);

  if (!('IntersectionObserver' in window) || !secs.length) return;

  var seen = new Map();
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(en){ seen.set(en.target.id, en.intersectionRatio); });

    var best = null, bestRatio = 0;
    seen.forEach(function(ratio, id){
      if (ratio > bestRatio) { bestRatio = ratio; best = id; }
    });

    if (!best) return;
    links.forEach(function(a){
      a.classList.toggle('on', a.getAttribute('href') === '#' + best);
    });
  }, { rootMargin: '-80px 0px -60% 0px', threshold: [0, .25, .5, 1] });

  secs.forEach(function(s){ obs.observe(s); });
})();
</script>
@endpush

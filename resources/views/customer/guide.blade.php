@extends('customer.layout')
@section('title', __t('คู่มือการใช้งาน', 'How to Use'))

@section('extra-style')
  /* ── สารบัญแบบชิป เลื่อนแนวนอนบนมือถือ ── */
  .g-toc{
    display:flex; gap:.45rem; overflow-x:auto; padding:.15rem .15rem .55rem;
    margin:0 -.25rem 1.1rem; scrollbar-width:none; -webkit-overflow-scrolling:touch;
  }
  .g-toc::-webkit-scrollbar{ display:none; }
  .g-toc a{
    flex:0 0 auto; text-decoration:none; white-space:nowrap;
    background:var(--panel); color:var(--ink-soft); border:1px solid var(--line);
    border-radius:999px; padding:.4rem .85rem; font-size:.8rem; font-weight:600;
  }
  .g-toc a:active{ transform:scale(.97); }
  .g-toc a.on{ background:var(--accent); border-color:var(--accent); color:#fff; }

  /* ── บล็อกขั้นตอน ── */
  .g-step{
    background:var(--panel); border:1px solid var(--line); border-radius:18px;
    padding:1.05rem 1.05rem 1.15rem; margin-bottom:.9rem;
  }
  .g-step-head{ display:flex; align-items:flex-start; gap:.7rem; margin-bottom:.6rem; }
  .g-step-no{
    flex:0 0 auto; width:28px; height:28px; border-radius:50%;
    background:var(--accent); color:#fff; font-weight:700; font-size:.82rem;
    display:flex; align-items:center; justify-content:center; margin-top:.05rem;
  }
  .g-step-head h3{
    font-size:1rem; font-weight:700; margin:0; line-height:1.4; color:var(--ink);
  }
  .g-step p{ font-size:.875rem; line-height:1.72; color:var(--ink-soft); margin:0 0 .6rem; }
  .g-step p:last-child{ margin-bottom:0; }
  .g-step strong{ color:var(--ink); font-weight:600; }

  .g-step ul{ padding-left:1.1rem; margin:0 0 .6rem; }
  .g-step li{ font-size:.875rem; line-height:1.7; color:var(--ink-soft); margin-bottom:.3rem; }
  .g-step li strong{ color:var(--ink); }

  /* ── ภาพหน้าจอมือถือ ── */
  .g-shot{ margin:.85rem 0 .3rem; text-align:center; }
  .g-shot img{
    width:100%; max-width:250px; border-radius:16px;
    border:1px solid var(--line); box-shadow:0 8px 28px -10px rgba(36,31,58,.28);
    cursor:zoom-in; background:var(--panel);
  }
  .g-shot figcaption{
    font-size:.74rem; color:var(--ink-soft); margin-top:.5rem; opacity:.85;
  }

  /* ── กล่องเน้น ── */
  .g-note{
    display:flex; gap:.55rem; align-items:flex-start;
    border-radius:13px; padding:.7rem .85rem; margin:.7rem 0;
    font-size:.83rem; line-height:1.65;
  }
  .g-note i{ flex:0 0 auto; margin-top:.15rem; }
  .g-note.tip{ background:var(--c-blue-soft); color:var(--c-blue-ink); }
  .g-note.warn{ background:var(--c-amber-soft); color:var(--c-amber-ink); }
  .g-note.good{ background:var(--c-green-soft); color:var(--c-green-ink); }
  .g-note.bad{ background:var(--c-pink-soft); color:var(--c-pink-ink); }

  /* ── ตารางสถานะ ── */
  .g-table{ width:100%; font-size:.82rem; border-collapse:collapse; margin:.6rem 0; }
  .g-table td{
    padding:.55rem .1rem; border-bottom:1px solid var(--line);
    vertical-align:top; color:var(--ink-soft); line-height:1.6;
  }
  .g-table tr:last-child td{ border-bottom:0; }
  .g-table td:first-child{ width:38%; padding-right:.6rem; }
  .g-pill{
    display:inline-block; border-radius:999px; padding:.15rem .6rem;
    font-size:.72rem; font-weight:700; white-space:nowrap;
  }
  .p-green{ background:var(--c-green-soft); color:var(--c-green-ink); }
  .p-blue{ background:var(--c-blue-soft); color:var(--c-blue-ink); }
  .p-amber{ background:var(--c-amber-soft); color:var(--c-amber-ink); }
  .p-pink{ background:var(--c-pink-soft); color:var(--c-pink-ink); }
  .p-purple{ background:var(--c-purple-soft); color:var(--c-purple-ink); }

  /* ── หัวข้อหมวด ── */
  .g-sec{ scroll-margin-top:76px; }
  .g-sec-title{
    display:flex; align-items:center; gap:.6rem;
    font-size:1.12rem; font-weight:700; color:var(--ink);
    margin:1.6rem 0 .8rem;
  }
  .g-sec-title .si{
    width:34px; height:34px; border-radius:11px; flex:0 0 auto;
    display:flex; align-items:center; justify-content:center; font-size:.95rem;
    background:var(--accent-soft); color:var(--accent-deep);
  }

  /* ── คำถามพบบ่อย ── */
  .g-faq{
    background:var(--panel); border:1px solid var(--line);
    border-radius:14px; margin-bottom:.55rem; overflow:hidden;
  }
  .g-faq summary{
    padding:.85rem 1rem; font-size:.88rem; font-weight:600; color:var(--ink);
    cursor:pointer; list-style:none; display:flex; align-items:center; gap:.55rem;
  }
  .g-faq summary::-webkit-details-marker{ display:none; }
  .g-faq summary::after{
    content:'\F282'; font-family:'bootstrap-icons'; margin-left:auto;
    font-size:.8rem; color:var(--ink-soft); transition:transform .2s ease;
  }
  .g-faq[open] summary::after{ transform:rotate(180deg); }
  .g-faq .fa-body{
    padding:0 1rem .9rem; font-size:.855rem; line-height:1.72; color:var(--ink-soft);
  }
  .g-faq .fa-body strong{ color:var(--ink); font-weight:600; }

  /* ── ดูภาพเต็มจอ ── */
  .g-lightbox{
    position:fixed; inset:0; z-index:1090; background:rgba(20,16,34,.92);
    display:none; align-items:center; justify-content:center; padding:1.2rem;
    cursor:zoom-out;
  }
  .g-lightbox.on{ display:flex; }
  .g-lightbox img{ max-width:100%; max-height:92vh; border-radius:14px; }
@endsection

@section('content')

@php
  // ภาพแยกตามภาษา ถ่ายจากมือถือจริงทั้งสองภาษา
  $lang = app()->getLocale() === 'en' ? 'en' : 'th';
  $shot = fn ($f) => asset("docs/guide/img/{$f}-{$lang}.png");
@endphp

<div class="page-header">
  <h1>{{ __t('คู่มือการใช้งาน', 'How to Use') }}</h1>
  <p>{{ __t('ใช้งานแอปเป็นใน 5 นาที — แตะที่ภาพเพื่อดูขนาดเต็ม',
            'Learn the app in 5 minutes — tap any image to enlarge') }}</p>
</div>

{{-- สารบัญ --}}
<nav class="g-toc" id="gToc">
  <a href="#start" class="on">{{ __t('เริ่มต้น', 'Getting started') }}</a>
  <a href="#buy">{{ __t('ซื้อแพ็กเกจ', 'Buy a package') }}</a>
  <a href="#book">{{ __t('จองคลาส', 'Book a class') }}</a>
  <a href="#manage">{{ __t('จัดการการจอง', 'Manage bookings') }}</a>
  <a href="#account">{{ __t('บัญชีของฉัน', 'My account') }}</a>
  <a href="#rules">{{ __t('กฎที่ควรรู้', 'Key rules') }}</a>
  <a href="#faq">{{ __t('คำถามพบบ่อย', 'FAQ') }}</a>
</nav>

{{-- ═══════════ เริ่มต้น ═══════════ --}}
<section class="g-sec" id="start">
  <div class="g-sec-title">
    <span class="si"><i class="bi bi-flag"></i></span>
    {{ __t('เริ่มต้นใช้งาน', 'Getting started') }}
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">1</span>
      <h3>{{ __t('สมัครสมาชิก', 'Create your account') }}</h3>
    </div>
    <p>
      {{ __t('สมัครได้ 2 วิธี — กรอกเบอร์โทรกับรหัสผ่านเอง หรือกดปุ่ม LINE เพื่อเข้าสู่ระบบด้วยบัญชี LINE ของคุณ (เร็วกว่า ไม่ต้องจำรหัสผ่าน)',
             'There are two ways to sign up — enter your phone number and a password, or tap the LINE button to sign in with your LINE account (faster, no password to remember).') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('03-register') }}" alt="{{ __t('หน้าสมัครสมาชิก', 'Sign-up screen') }}" loading="lazy">
      <figcaption>{{ __t('หน้าสมัครสมาชิก', 'Sign-up screen') }}</figcaption>
    </figure>

    <div class="g-note tip">
      <i class="bi bi-phone"></i>
      <span>{{ __t('ระบบใช้เบอร์โทรเป็นชื่อผู้ใช้ กรอกเบอร์ที่ใช้งานจริงเพราะใช้เข้าสู่ระบบและรับ OTP',
                   'Your phone number is your username. Use a real number — you will need it to sign in and to receive OTP codes.') }}</span>
    </div>
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">2</span>
      <h3>{{ __t('เข้าสู่ระบบ', 'Sign in') }}</h3>
    </div>
    <p>
      {{ __t('กรอกเบอร์โทรและรหัสผ่าน หรือกดเข้าสู่ระบบด้วย LINE ถ้าเคยผูกบัญชีไว้แล้ว',
             'Enter your phone number and password, or tap Sign in with LINE if you have already linked your account.') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('02-login') }}" alt="{{ __t('หน้าเข้าสู่ระบบ', 'Sign-in screen') }}" loading="lazy">
      <figcaption>{{ __t('หน้าเข้าสู่ระบบ', 'Sign-in screen') }}</figcaption>
    </figure>

    <div class="g-note warn">
      <i class="bi bi-shield-check"></i>
      <span>{{ __t('ถ้าสมัครด้วย LINE แล้วเบอร์ที่กรอกตรงกับบัญชีเดิมที่ทางสตูดิโอเคยสร้างไว้ให้ ระบบจะส่ง OTP ไปยืนยันก่อนรวมบัญชี เพื่อให้ประวัติการเรียนและเครดิตเดิมไม่หาย',
                   'If you sign up with LINE and the phone number matches an account the studio already created for you, we will send an OTP to confirm before merging — so your class history and credits are kept.') }}</span>
    </div>
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">3</span>
      <h3>{{ __t('ทำความรู้จักหน้าแรก', 'Find your way around') }}</h3>
    </div>
    <p>
      {{ __t('หน้าแรกสรุปทุกอย่างที่ต้องรู้ — เครดิตคงเหลือ วันหมดอายุ คลาสที่จองไว้ครั้งถัดไป และปุ่มลัดไปยังงานที่ใช้บ่อย',
             'The home screen shows everything at a glance — your remaining credits, expiry date, your next booked class, and shortcuts to the things you do most.') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('04-home') }}" alt="{{ __t('หน้าแรก', 'Home screen') }}" loading="lazy">
      <figcaption>{{ __t('หน้าแรก', 'Home screen') }}</figcaption>
    </figure>

    <p>{{ __t('แถบเมนูด้านล่างมี 4 ปุ่มหลัก:', 'The bar at the bottom has four main tabs:') }}</p>
    <ul>
      <li><strong>{{ __t('หน้าแรก', 'Home') }}</strong> — {{ __t('ภาพรวมและข่าวสาร', 'overview and announcements') }}</li>
      <li><strong>{{ __t('ตารางคลาส', 'Schedule') }}</strong> — {{ __t('ดูคลาสและจอง', 'browse classes and book') }}</li>
      <li><strong>{{ __t('การจอง', 'Bookings') }}</strong> — {{ __t('คลาสที่จองไว้และประวัติ', 'your upcoming classes and history') }}</li>
      <li><strong>{{ __t('โปรไฟล์', 'Profile') }}</strong> — {{ __t('ข้อมูลส่วนตัว แพ็กเกจ และตั้งค่า', 'your details, packages and settings') }}</li>
    </ul>

    <div class="g-note tip">
      <i class="bi bi-translate"></i>
      <span>{{ __t('เปลี่ยนภาษาได้ที่ปุ่มธงมุมขวาบน และสลับโหมดมืด/สว่างได้ที่ปุ่มข้างๆ',
                   'Tap the flag at the top right to switch language, and the icon beside it to switch between light and dark mode.') }}</span>
    </div>
  </div>
</section>

{{-- ═══════════ ซื้อแพ็กเกจ ═══════════ --}}
<section class="g-sec" id="buy">
  <div class="g-sec-title">
    <span class="si"><i class="bi bi-ticket-perforated"></i></span>
    {{ __t('ซื้อแพ็กเกจ', 'Buy a package') }}
  </div>

  <div class="g-note warn">
    <i class="bi bi-info-circle"></i>
    <span>{{ __t('ต้องมีเครดิตก่อนถึงจะจองคลาสได้ ถ้าเพิ่งสมัครใหม่ ให้ซื้อแพ็กเกจก่อนเป็นอันดับแรก',
                 'You need credits before you can book. If you have just signed up, buying a package is your first step.') }}</span>
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">1</span>
      <h3>{{ __t('เลือกแพ็กเกจ', 'Choose a package') }}</h3>
    </div>
    <p>
      {{ __t('ไปที่ โปรไฟล์ → ซื้อแพ็กเกจ หรือกดปุ่ม "ซื้อแพ็กเกจ" ที่หน้าแรก แต่ละแพ็กบอกไว้ชัดเจนว่าได้กี่ครั้ง ราคาเท่าไร และใช้ได้ถึงเมื่อไหร่',
             'Go to Profile → Buy Packages, or tap "Buy Package" on the home screen. Each package shows how many sessions you get, the price, and how long it stays valid.') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('07-purchase-list') }}" alt="{{ __t('รายการแพ็กเกจ', 'Package list') }}" loading="lazy">
      <figcaption>{{ __t('เลือกแพ็กเกจที่ต้องการ', 'Pick the package you want') }}</figcaption>
    </figure>

    <div class="g-note tip">
      <i class="bi bi-stars"></i>
      <span>{{ __t('ลูกค้าใหม่แนะนำให้เริ่มที่แพ็กทดลอง ราคาพิเศษ แต่ซื้อได้คนละครั้งเดียวเท่านั้น',
                   'New here? Start with a trial package — special price, but each member can buy one only once.') }}</span>
    </div>
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">2</span>
      <h3>{{ __t('ยืนยันคำสั่งซื้อ', 'Confirm your order') }}</h3>
    </div>
    <p>
      {{ __t('ตรวจรายละเอียดแพ็กและยอดเงินให้ถูกต้อง แล้วกดยืนยันเพื่อออกบิล',
             'Check the package details and the total, then confirm to create your order.') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('08-checkout') }}" alt="{{ __t('หน้ายืนยันคำสั่งซื้อ', 'Checkout screen') }}" loading="lazy">
      <figcaption>{{ __t('ตรวจรายละเอียดก่อนยืนยัน', 'Review before confirming') }}</figcaption>
    </figure>
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">3</span>
      <h3>{{ __t('โอนเงินและแนบสลิป', 'Transfer and upload your slip') }}</h3>
    </div>
    <p>
      {{ __t('ระบบจะแสดงเลขบัญชีให้โอน กดปุ่มคัดลอกเลขบัญชีไปวางในแอปธนาคารได้เลย โอนเสร็จแล้วกลับมาแนบรูปสลิปในหน้านี้',
             'The app shows the bank account to transfer to — tap copy to paste it straight into your banking app. Once you have paid, come back and upload a photo of your slip.') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('09-pay') }}" alt="{{ __t('หน้าชำระเงิน', 'Payment screen') }}" loading="lazy">
      <figcaption>{{ __t('โอนตามบัญชีที่แสดง แล้วแนบสลิป', 'Transfer to the account shown, then attach your slip') }}</figcaption>
    </figure>

    @if($bankName && $bankAccountNo)
    <div class="g-note tip">
      <i class="bi bi-bank"></i>
      <span>
        {{ __t('บัญชีรับเงิน', 'Our account') }}: <strong>{{ $bankName }}</strong>
        {{ $bankAccountNo }}@if($bankAccountName) — {{ $bankAccountName }}@endif
      </span>
    </div>
    @endif

    <div class="g-note warn">
      <i class="bi bi-clock-history"></i>
      <span>{{ __t('เครดิตยังไม่เข้าทันทีหลังโอน ต้องรอแอดมินตรวจสลิปก่อน ปกติภายใน 24 ชั่วโมง เมื่ออนุมัติแล้วจะมีแจ้งเตือนเข้าแอปและเครดิตจะเพิ่มให้อัตโนมัติ',
                   'Credits do not arrive the moment you transfer — our team checks your slip first, usually within 24 hours. You will get a notification once it is approved and your credits are added automatically.') }}</span>
    </div>
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">4</span>
      <h3>{{ __t('ติดตามสถานะคำสั่งซื้อ', 'Track your order') }}</h3>
    </div>
    <p>
      {{ __t('ดูได้ที่ โปรไฟล์ → คำสั่งซื้อของฉัน ถ้ายังไม่ได้แนบสลิป หรือแนบผิดรูป เข้ามาแก้ไขที่นี่ได้',
             'Check Profile → My Orders. If you have not uploaded a slip yet, or uploaded the wrong image, you can fix it here.') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('10-orders') }}" alt="{{ __t('คำสั่งซื้อของฉัน', 'My orders') }}" loading="lazy">
      <figcaption>{{ __t('ประวัติคำสั่งซื้อและสถานะ', 'Your orders and their status') }}</figcaption>
    </figure>
  </div>
</section>

{{-- ═══════════ จองคลาส ═══════════ --}}
<section class="g-sec" id="book">
  <div class="g-sec-title">
    <span class="si"><i class="bi bi-calendar-plus"></i></span>
    {{ __t('จองคลาส', 'Book a class') }}
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">1</span>
      <h3>{{ __t('เลือกสาขาและวัน', 'Pick a studio and a day') }}</h3>
    </div>
    <p>
      {{ __t('แตะแท็บ "ตารางคลาส" ด้านล่าง เลือกสาขาที่จะไป แล้วเลื่อนแถบวันที่เพื่อดูคลาสของแต่ละวัน',
             'Tap the Schedule tab, choose the studio you want to visit, then swipe the date strip to see each day\'s classes.') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('05-schedule') }}" alt="{{ __t('ตารางคลาส', 'Class schedule') }}" loading="lazy">
      <figcaption>{{ __t('เลือกสาขาและวันที่ต้องการ', 'Choose your studio and date') }}</figcaption>
    </figure>

    <p>{{ __t('การ์ดคลาสแต่ละใบบอก เวลา ชื่อคลาส ครูผู้สอน ห้อง และจำนวนที่นั่งที่เหลือ',
              'Each class card shows the time, class name, instructor, room, and how many spots are left.') }}</p>
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">2</span>
      <h3>{{ __t('กดจอง', 'Tap to book') }}</h3>
    </div>
    <p>
      {{ __t('กดปุ่มจองที่การ์ดคลาส ระบบจะตัดเครดิตทันทีและยืนยันที่นั่งให้ คลาสที่จองแล้วจะขึ้นในแท็บ "การจอง"',
             'Tap the book button on the class card. A credit is deducted right away and your spot is confirmed. Booked classes appear under the Bookings tab.') }}
    </p>

    <div class="g-note good">
      <i class="bi bi-check-circle"></i>
      <span>{{ __t('ระบบตัดเครดิตจากแพ็กที่ใกล้หมดอายุก่อนเสมอ คุณจึงใช้สิทธิ์ได้คุ้มที่สุดโดยไม่ต้องเลือกเอง',
                   'We always use credits from the package expiring soonest, so you get the most out of what you have paid for — no need to choose.') }}</span>
    </div>

    @if($waitlistEnabled)
    <div class="g-note tip">
      <i class="bi bi-people"></i>
      <span>{{ __t('ถ้าคลาสเต็มแล้ว คุณจะเข้า "คิวสำรอง" ได้ ตอนเข้าคิวยังไม่ตัดเครดิต ถ้ามีคนยกเลิกระบบจะเลื่อนคิวให้อัตโนมัติ ตัดเครดิตตอนนั้น แล้วแจ้งเตือนให้ทราบ',
                   'If a class is full you can join the waitlist. No credit is taken while you wait. If someone cancels we move you up automatically, deduct the credit then, and send you a notification.') }}</span>
    </div>
    @endif

    <div class="g-note warn">
      <i class="bi bi-hourglass-split"></i>
      <span>{{ __t('ระบบปิดรับจองก่อนคลาสเริ่ม', 'Booking closes') }}
        <strong>{{ $closeMinutes }} {{ __t('นาที', 'minutes') }}</strong>
        {{ __t('และจองล่วงหน้าได้ไกลสุด', 'before the class starts, and you can book up to') }}
        <strong>{{ $openDays }} {{ __t('วัน', 'days') }}</strong>
        {{ __t('ถ้าเลยเวลาปิดรับจองแล้วแต่ยังอยากเรียน ให้ติดต่อสาขาโดยตรง',
               'ahead. If booking has closed but you still want to join, contact the studio directly.') }}</span>
    </div>
  </div>
</section>

{{-- ═══════════ จัดการการจอง ═══════════ --}}
<section class="g-sec" id="manage">
  <div class="g-sec-title">
    <span class="si"><i class="bi bi-check2-square"></i></span>
    {{ __t('จัดการการจอง', 'Manage your bookings') }}
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">1</span>
      <h3>{{ __t('ดูคลาสที่จองไว้', 'See what you have booked') }}</h3>
    </div>
    <p>
      {{ __t('แท็บ "การจอง" แบ่งเป็นสองส่วน — คลาสที่กำลังจะถึง และประวัติการเรียนย้อนหลัง',
             'The Bookings tab has two parts — your upcoming classes, and your past history.') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('06-bookings') }}" alt="{{ __t('การจองของฉัน', 'My bookings') }}" loading="lazy">
      <figcaption>{{ __t('คลาสที่จองไว้และประวัติ', 'Upcoming classes and history') }}</figcaption>
    </figure>

    <p>{{ __t('ความหมายของแต่ละสถานะ:', 'What each status means:') }}</p>
    <table class="g-table">
      <tr>
        <td><span class="g-pill p-green">{{ __t('ยืนยันแล้ว', 'Confirmed') }}</span></td>
        <td>{{ __t('ได้ที่นั่งแน่นอน มาเรียนได้ตามเวลา', 'Your spot is secured — just turn up on time.') }}</td>
      </tr>
      @if($waitlistEnabled)
      <tr>
        <td><span class="g-pill p-purple">{{ __t('คิวสำรอง', 'Waitlisted') }}</span></td>
        <td>{{ __t('คลาสเต็ม กำลังรอคิว ยังไม่ตัดเครดิต', 'The class is full and you are in the queue. No credit taken yet.') }}</td>
      </tr>
      @endif
      <tr>
        <td><span class="g-pill p-blue">{{ __t('เข้าเรียนแล้ว', 'Attended') }}</span></td>
        <td>{{ __t('เช็คอินเรียบร้อย มาเรียนจริง', 'You checked in and attended.') }}</td>
      </tr>
      <tr>
        <td><span class="g-pill p-green">{{ __t('ยกเลิก', 'Cancelled') }}</span></td>
        <td>{{ __t('ยกเลิกทันกำหนด ได้เครดิตคืนแล้ว', 'Cancelled in time — your credit was refunded.') }}</td>
      </tr>
      <tr>
        <td><span class="g-pill p-amber">{{ __t('ยกเลิกช้า', 'Late cancel') }}</span></td>
        <td>{{ __t('ยกเลิกกระชั้นชิดเกินกำหนด', 'Cancelled too close to the class time.') }}</td>
      </tr>
      <tr>
        <td><span class="g-pill p-pink">{{ __t('ไม่มาเรียน', 'No-show') }}</span></td>
        <td>{{ __t('ไม่มาและไม่ได้แจ้งยกเลิก', 'You did not attend and did not cancel.') }}</td>
      </tr>
    </table>
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">2</span>
      <h3>{{ __t('ยกเลิกคลาส', 'Cancel a class') }}</h3>
    </div>
    <p>
      {{ __t('กดปุ่มยกเลิกที่การ์ดคลาสในแท็บ "การจอง" ระบบจะบอกก่อนกดยืนยันว่าตอนนี้ยกเลิกแล้วได้เครดิตคืนหรือไม่',
             'Tap Cancel on the class card in the Bookings tab. Before you confirm, the app tells you whether you will get your credit back.') }}
    </p>

    <div class="g-note good">
      <i class="bi bi-arrow-counterclockwise"></i>
      <span>
        {{ __t('ยกเลิกก่อนคลาสเริ่มเกิน', 'Cancel more than') }}
        <strong>{{ $cancelHours }} {{ __t('ชั่วโมง', 'hours') }}</strong>
        {{ __t('ได้เครดิตคืนเต็มจำนวน', 'before the class and you get your credit back in full.') }}
      </span>
    </div>

    @if($lateCancelCharges)
    <div class="g-note bad">
      <i class="bi bi-exclamation-circle"></i>
      <span>{{ __t('ยกเลิกช้ากว่านั้นจะไม่ได้เครดิตคืน เพราะที่นั่งถูกกันไว้ให้คุณแล้ว คนอื่นจองแทนไม่ทัน',
                   'Cancel later than that and the credit is not refunded — the spot was held for you and nobody else could take it in time.') }}</span>
    </div>
    @endif

    @if($noShowCharges)
    <div class="g-note bad">
      <i class="bi bi-person-x"></i>
      <span>{{ __t('ถ้าไม่มาเรียนโดยไม่แจ้งยกเลิกเลย ก็จะไม่ได้เครดิตคืนเช่นกัน ถ้ามาไม่ได้แนะนำให้กดยกเลิกไว้เสมอ',
                   'Not turning up without cancelling also costs the credit. If you cannot make it, always cancel — even at the last minute.') }}</span>
    </div>
    @endif
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">3</span>
      <h3>{{ __t('วันมาเรียน', 'On the day') }}</h3>
    </div>
    <p>
      {{ __t('มาถึงสตูดิโอก่อนเวลาสัก 10 นาที แจ้งชื่อหรือเบอร์โทรที่เคาน์เตอร์ พนักงานจะเช็คอินให้ ไม่ต้องแสดงอะไรในแอป',
             'Arrive about 10 minutes early and give your name or phone number at the front desk — our staff will check you in. You do not need to show anything in the app.') }}
    </p>
    <div class="g-note tip">
      <i class="bi bi-bell"></i>
      <span>{{ __t('ระบบจะส่งแจ้งเตือนก่อนถึงเวลาคลาส กดที่รูปกระดิ่งมุมขวาบนเพื่อดูการแจ้งเตือนทั้งหมด',
                   'We send a reminder before your class. Tap the bell at the top right to see all your notifications.') }}</span>
    </div>

    <figure class="g-shot">
      <img src="{{ $shot('11-notifications') }}" alt="{{ __t('การแจ้งเตือน', 'Notifications') }}" loading="lazy">
      <figcaption>{{ __t('ศูนย์การแจ้งเตือน', 'Notification centre') }}</figcaption>
    </figure>
  </div>
</section>

{{-- ═══════════ บัญชีของฉัน ═══════════ --}}
<section class="g-sec" id="account">
  <div class="g-sec-title">
    <span class="si"><i class="bi bi-person-circle"></i></span>
    {{ __t('บัญชีของฉัน', 'My account') }}
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">1</span>
      <h3>{{ __t('ดูเครดิตและแพ็กเกจ', 'Check credits and packages') }}</h3>
    </div>
    <p>
      {{ __t('แท็บ "โปรไฟล์" แสดงจำนวนคลาสที่เรียนไปแล้ว เครดิตคงเหลือ และแพ็กเกจทุกใบที่ถืออยู่พร้อมวันหมดอายุ',
             'The Profile tab shows how many classes you have taken, your remaining credits, and every package you hold with its expiry date.') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('12-profile') }}" alt="{{ __t('หน้าโปรไฟล์', 'Profile screen') }}" loading="lazy">
      <figcaption>{{ __t('โปรไฟล์และแพ็กเกจของคุณ', 'Your profile and packages') }}</figcaption>
    </figure>

    <div class="g-note warn">
      <i class="bi bi-calendar-x"></i>
      <span>{{ __t('เครดิตมีวันหมดอายุ หมดแล้วใช้ไม่ได้และไม่คืนเงิน ควรดูวันหมดอายุไว้เสมอและวางแผนใช้ให้ทัน',
                   'Credits expire. Once past the expiry date they cannot be used and are not refundable, so keep an eye on the date and plan ahead.') }}</span>
    </div>
  </div>

  <div class="g-step">
    <div class="g-step-head">
      <span class="g-step-no">2</span>
      <h3>{{ __t('แก้ไขข้อมูลส่วนตัว', 'Update your details') }}</h3>
    </div>
    <p>
      {{ __t('ไปที่ โปรไฟล์ → แก้ไขข้อมูล เปลี่ยนชื่อ อีเมล สาขาประจำ และรหัสผ่านได้ที่นี่',
             'Go to Profile → Edit profile to change your name, email, home studio and password.') }}
    </p>

    <figure class="g-shot">
      <img src="{{ $shot('13-profile-edit') }}" alt="{{ __t('แก้ไขข้อมูลส่วนตัว', 'Edit profile') }}" loading="lazy">
      <figcaption>{{ __t('แก้ไขข้อมูลส่วนตัว', 'Edit your profile') }}</figcaption>
    </figure>

    <div class="g-note bad">
      <i class="bi bi-heart-pulse"></i>
      <span>{{ __t('สำคัญมาก: ถ้ามีอาการบาดเจ็บ โรคประจำตัว เคยผ่าตัด หรือกำลังตั้งครรภ์ กรุณาแจ้งทางสตูดิโอ ครูผู้สอนจะได้ปรับท่าให้เหมาะและปลอดภัยกับคุณ',
                   'Important: if you have an injury, a medical condition, past surgery, or you are pregnant, please tell the studio. Your instructor will adapt the exercises to keep you safe.') }}</span>
    </div>
  </div>
</section>

{{-- ═══════════ กฎที่ควรรู้ ═══════════ --}}
<section class="g-sec" id="rules">
  <div class="g-sec-title">
    <span class="si"><i class="bi bi-list-check"></i></span>
    {{ __t('กฎที่ควรรู้', 'Key rules at a glance') }}
  </div>

  <div class="g-step">
    <table class="g-table">
      <tr>
        <td><strong>{{ __t('ยกเลิกฟรี', 'Free cancellation') }}</strong></td>
        <td>{{ __t('ก่อนคลาสเริ่มเกิน', 'More than') }} <strong>{{ $cancelHours }}</strong> {{ __t('ชั่วโมง', 'hours before class') }}</td>
      </tr>
      <tr>
        <td><strong>{{ __t('ปิดรับจอง', 'Booking closes') }}</strong></td>
        <td><strong>{{ $closeMinutes }}</strong> {{ __t('นาทีก่อนคลาสเริ่ม', 'minutes before class starts') }}</td>
      </tr>
      <tr>
        <td><strong>{{ __t('จองล่วงหน้า', 'Book ahead') }}</strong></td>
        <td>{{ __t('ได้ไกลสุด', 'Up to') }} <strong>{{ $openDays }}</strong> {{ __t('วัน', 'days in advance') }}</td>
      </tr>
      <tr>
        <td><strong>{{ __t('ยกเลิกช้า', 'Late cancel') }}</strong></td>
        <td>{{ $lateCancelCharges
              ? __t('ไม่คืนเครดิต', 'Credit is not refunded')
              : __t('คืนเครดิตให้', 'Credit is still refunded') }}</td>
      </tr>
      <tr>
        <td><strong>{{ __t('ไม่มาเรียน', 'No-show') }}</strong></td>
        <td>{{ $noShowCharges
              ? __t('ไม่คืนเครดิต', 'Credit is not refunded')
              : __t('คืนเครดิตให้', 'Credit is still refunded') }}</td>
      </tr>
      <tr>
        <td><strong>{{ __t('ตัดเครดิตจากใบไหน', 'Which package is used') }}</strong></td>
        <td>{{ __t('ใบที่ใกล้หมดอายุที่สุดก่อนเสมอ', 'Always the one expiring soonest') }}</td>
      </tr>
      <tr>
        <td><strong>{{ __t('แพ็กทดลอง', 'Trial package') }}</strong></td>
        <td>{{ __t('ซื้อได้คนละ 1 ครั้ง', 'One per member, once only') }}</td>
      </tr>
      <tr>
        <td><strong>{{ __t('ตรวจสลิป', 'Slip approval') }}</strong></td>
        <td>{{ __t('ปกติภายใน 24 ชั่วโมง', 'Usually within 24 hours') }}</td>
      </tr>
    </table>
  </div>
</section>

{{-- ═══════════ FAQ ═══════════ --}}
<section class="g-sec" id="faq">
  <div class="g-sec-title">
    <span class="si"><i class="bi bi-question-circle"></i></span>
    {{ __t('คำถามพบบ่อย', 'Frequently asked questions') }}
  </div>

  <details class="g-faq">
    <summary>{{ __t('จองคลาสไม่ได้ ทำยังไงดี', 'I cannot book a class — what should I check?') }}</summary>
    <div class="fa-body">
      {{ __t('ลองไล่ดูตามนี้: เครดิตยังเหลือไหม แพ็กหมดอายุหรือยัง คลาสเต็มหรือเปล่า และเลยเวลาปิดรับจองแล้วหรือยัง ถ้าตรวจครบแล้วยังจองไม่ได้ ติดต่อสาขาได้เลย',
             'Check these in order: do you still have credits, has your package expired, is the class full, and has booking already closed? If everything looks fine and it still will not book, contact the studio.') }}
    </div>
  </details>

  <details class="g-faq">
    <summary>{{ __t('โอนเงินแล้วแต่เครดิตยังไม่เข้า', 'I transferred the money but my credits have not arrived') }}</summary>
    <div class="fa-body">
      {{ __t('เครดิตจะเข้าหลังแอดมินตรวจสลิปแล้ว ปกติภายใน 24 ชั่วโมง ตรวจสอบก่อนว่าได้แนบสลิปในระบบเรียบร้อยแล้วที่ โปรไฟล์ → คำสั่งซื้อของฉัน ถ้าแนบแล้วและเกิน 24 ชั่วโมง ให้ติดต่อสาขา',
             'Credits are added after our team checks your slip, usually within 24 hours. First make sure the slip was actually uploaded — see Profile → My Orders. If it is there and more than 24 hours have passed, please contact the studio.') }}
    </div>
  </details>

  <details class="g-faq">
    <summary>{{ __t('ยกเลิกแล้วได้เครดิตคืนไหม', 'Will I get my credit back if I cancel?') }}</summary>
    <div class="fa-body">
      {{ __t('ได้คืนเต็มจำนวนถ้ายกเลิกก่อนคลาสเริ่มเกิน', 'Yes, in full, if you cancel more than') }}
      <strong>{{ $cancelHours }} {{ __t('ชั่วโมง', 'hours') }}</strong>
      {{ __t('ถ้าช้ากว่านั้นจะไม่ได้คืน ก่อนกดยืนยันยกเลิก ระบบจะบอกให้ทราบก่อนเสมอว่าครั้งนี้ได้คืนหรือไม่',
             'before the class. Later than that and it is not refunded. The app always tells you which applies before you confirm.') }}
    </div>
  </details>

  @if($waitlistEnabled)
  <details class="g-faq">
    <summary>{{ __t('คิวสำรองทำงานยังไง', 'How does the waitlist work?') }}</summary>
    <div class="fa-body">
      {{ __t('ถ้าคลาสเต็มคุณเข้าคิวได้ ตอนเข้าคิวยังไม่ตัดเครดิต เมื่อมีคนยกเลิกระบบจะเลื่อนคนแรกในคิวขึ้นเป็นผู้จองอัตโนมัติ ตัดเครดิตตอนนั้น แล้วส่งแจ้งเตือนให้ทราบ ถ้าไม่สะดวกแล้วก็กดยกเลิกคิวได้ ไม่มีค่าใช้จ่าย',
             'If a class is full you can join the queue — no credit is taken at that point. When someone cancels, the first person in the queue is moved up automatically, the credit is taken then, and we notify you. If you no longer want the spot you can leave the queue at any time, free of charge.') }}
    </div>
  </details>
  @endif

  <details class="g-faq">
    <summary>{{ __t('เครดิตหมดอายุแล้วขอต่ออายุได้ไหม', 'My credits expired — can they be extended?') }}</summary>
    <div class="fa-body">
      {{ __t('เครดิตที่หมดอายุแล้วใช้ไม่ได้และไม่คืนเงิน แต่ถ้ามีเหตุจำเป็น เช่น เจ็บป่วยหรือต้องเดินทางไกล ติดต่อสาขาไว้ล่วงหน้าได้ ทางสตูดิโอมีตัวเลือกหยุดพักแพ็กชั่วคราว (ฟรีซ) ซึ่งจะขยายวันหมดอายุให้ตามจำนวนวันที่หยุด',
             'Expired credits cannot be used and are not refundable. However, if something comes up — illness or travel — contact the studio in advance. We can freeze your package, which extends the expiry date by the number of days it was frozen.') }}
    </div>
  </details>

  <details class="g-faq">
    <summary>{{ __t('ต้องเตรียมอะไรไปบ้างในวันเรียน', 'What should I bring to class?') }}</summary>
    <div class="fa-body">
      {{ __t('ใส่ชุดออกกำลังกายที่เคลื่อนไหวสะดวก เตรียมถุงเท้ากันลื่นและน้ำดื่ม มาถึงก่อนเวลาสัก 10 นาทีเพื่อเช็คอินและเตรียมตัว',
             'Wear comfortable activewear you can move in, and bring grip socks and water. Arrive about 10 minutes early to check in and get ready.') }}
    </div>
  </details>

  <details class="g-faq">
    <summary>{{ __t('เปลี่ยนภาษาหรือโหมดมืดได้ที่ไหน', 'Where do I change the language or dark mode?') }}</summary>
    <div class="fa-body">
      {{ __t('ปุ่มธงมุมขวาบนใช้สลับภาษาไทย/อังกฤษ ส่วนปุ่มวงกลมครึ่งสีข้างๆ ใช้สลับโหมดมืดและสว่าง ระบบจะจำค่าที่เลือกไว้ให้',
             'The flag at the top right switches between Thai and English, and the half-circle icon beside it switches between light and dark mode. Your choice is remembered.') }}
    </div>
  </details>

  <details class="g-faq">
    <summary>{{ __t('ลืมรหัสผ่าน', 'I forgot my password') }}</summary>
    <div class="fa-body">
      {{ __t('ถ้าเคยผูกบัญชี LINE ไว้ ให้เข้าสู่ระบบด้วย LINE ได้เลยโดยไม่ต้องใช้รหัสผ่าน ถ้ายังไม่ได้ผูก ให้ติดต่อสาขาเพื่อขอตั้งรหัสผ่านใหม่',
             'If you have linked LINE, just sign in with LINE — no password needed. If you have not linked it, contact the studio and we will reset your password for you.') }}
    </div>
  </details>

  @if($lineUrl)
  <div class="g-note tip mt-3">
    <i class="bi bi-chat-dots"></i>
    <span>
      {{ __t('ยังไม่เจอคำตอบ? ทักหาเราได้ทาง LINE', 'Still stuck? Message us on LINE') }} —
      <a href="{{ $lineUrl }}" target="_blank" rel="noopener" style="color:inherit;font-weight:700;text-decoration:underline;">
        {{ __t('เปิดแชท', 'Open chat') }}
      </a>
    </span>
  </div>
  @endif
</section>

{{-- ดูภาพเต็มจอ --}}
<div class="g-lightbox" id="gLightbox"><img src="" alt=""></div>

@endsection

@section('extra-script')
<script>
// แตะภาพเพื่อดูเต็มจอ
(function(){
  var lb = document.getElementById('gLightbox');
  if (!lb) return;
  var lbImg = lb.querySelector('img');

  document.addEventListener('click', function(e){
    var img = e.target.closest('.g-shot img');
    if (img) { lbImg.src = img.src; lb.classList.add('on'); return; }
    if (e.target.closest('#gLightbox')) { lb.classList.remove('on'); lbImg.src = ''; }
  });
})();

// ไฮไลต์หัวข้อที่กำลังอ่านในสารบัญ และเลื่อนชิปให้เห็น
(function(){
  var toc = document.getElementById('gToc');
  if (!toc || !('IntersectionObserver' in window)) return;

  var links = Array.prototype.slice.call(toc.querySelectorAll('a'));
  var secs = links.map(function(a){ return document.querySelector(a.getAttribute('href')); }).filter(Boolean);
  if (!secs.length) return;

  var ratios = new Map();
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(en){ ratios.set(en.target.id, en.intersectionRatio); });

    var best = null, bestRatio = 0;
    ratios.forEach(function(r, id){ if (r > bestRatio) { bestRatio = r; best = id; } });
    if (!best) return;

    links.forEach(function(a){
      var on = a.getAttribute('href') === '#' + best;
      a.classList.toggle('on', on);
      if (on && a.scrollIntoView) {
        a.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
      }
    });
  }, { rootMargin: '-70px 0px -55% 0px', threshold: [0, .2, .5, 1] });

  secs.forEach(function(s){ obs.observe(s); });
})();
</script>
@endsection

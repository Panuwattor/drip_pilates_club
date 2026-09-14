@extends('customer.layout')
@section('title', __t('ชำระเงิน', 'Payment'))

@section('content')
@php
  $pending = $order->payments->firstWhere('status', 'pending');
  $rejected = $order->payments->firstWhere('status', 'rejected');
@endphp

<div class="page-header">
  <a href="{{ route('customer.purchase.orders') }}" class="small text-decoration-none" style="color:var(--ink-soft);">
    <i class="bi bi-chevron-left"></i> {{ __t('คำสั่งซื้อของฉัน', 'My orders') }}
  </a>
  <h1>{{ __t('ชำระเงิน', 'Payment') }}</h1>
  <p>{{ $order->code }}</p>
</div>

@include('customer.purchase._flash')

@if($order->status === 'paid')
  <div class="panel mb-3 text-center">
    <div class="stat-icon mx-auto mb-2" style="background:#D6EBD8;color:#2F6B36;width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
      <i class="bi bi-check-lg"></i>
    </div>
    <h4 style="font-size:1.05rem;font-weight:700;">{{ __t('ชำระเงินเรียบร้อยแล้ว', 'Payment confirmed') }}</h4>
    <p class="small text-secondary mb-3">{{ __t('เครดิตถูกเพิ่มเข้าบัญชีของคุณแล้ว', 'Credits have been added to your account.') }}</p>
    <a href="{{ route('customer.profile.index') }}" class="btn btn-accent">{{ __t('ดูแพ็กเกจของฉัน', 'View my packages') }}</a>
  </div>
@elseif($order->status === 'cancelled')
  <div class="no-class-note mb-3">{{ __t('คำสั่งซื้อนี้ถูกยกเลิกแล้ว', 'This order was cancelled') }}</div>
@endif

<div class="row g-3">
  <div class="col-md-6">
    <div class="panel mb-3">
      <div class="section-title mt-0">{{ __t('สรุปคำสั่งซื้อ', 'Order summary') }}</div>
      @foreach($order->items as $item)
        <div class="d-flex justify-content-between align-items-start gap-2 py-2">
          <div class="min-width-0">
            <div style="font-weight:600;">{{ $item->name() }}</div>
            <div class="small text-secondary">{{ number_format($item->unit_price) }} ฿ × {{ $item->quantity }}</div>
          </div>
          <div style="white-space:nowrap;">{{ number_format($item->subtotal) }} ฿</div>
        </div>
      @endforeach
      <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="border-color:var(--line)!important;">
        <strong>{{ __t('ยอดที่ต้องโอน', 'Amount to transfer') }}</strong>
        <strong style="font-size:1.35rem;color:var(--accent-deep);">{{ number_format($order->total) }} ฿</strong>
      </div>
    </div>

    @if($order->status === 'pending')
      <div class="panel mb-3">
        <div class="section-title mt-0">{{ __t('โอนเงินมาที่บัญชีนี้', 'Transfer to this account') }}</div>

        <div class="menu-list">
          <div class="menu-row">
            <div class="mi"><i class="bi bi-bank"></i></div>
            <span>{{ $bank['bank'] }}</span>
          </div>
          <div class="menu-row">
            <div class="mi"><i class="bi bi-person"></i></div>
            <span>{{ $bank['account_name'] }}</span>
          </div>
          <div class="menu-row">
            <div class="mi"><i class="bi bi-credit-card-2-front"></i></div>
            <span style="font-variant-numeric:tabular-nums;letter-spacing:.04em;font-weight:600;" id="accountNo">{{ $bank['account_no'] }}</span>
            <button type="button" class="btn btn-sm border ms-auto" data-copy="{{ $bank['account_no'] }}">
              <i class="bi bi-clipboard"></i> {{ __t('คัดลอก', 'Copy') }}
            </button>
          </div>
          @if($bank['promptpay'])
            <div class="menu-row">
              <div class="mi"><i class="bi bi-qr-code"></i></div>
              <span>{{ __t('พร้อมเพย์', 'PromptPay') }} {{ $bank['promptpay'] }}</span>
              <button type="button" class="btn btn-sm border ms-auto" data-copy="{{ $bank['promptpay'] }}">
                <i class="bi bi-clipboard"></i>
              </button>
            </div>
          @endif
        </div>

        @if($bank['qr_image'])
          <div class="text-center mt-3">
            <img src="{{ asset($bank['qr_image']) }}" alt="{{ __t('QR รับเงิน', 'Payment QR') }}"
                 style="max-width:220px;width:100%;border-radius:12px;">
          </div>
        @endif

        @if($bank['note'])
          <div class="small text-secondary mt-3">
            <i class="bi bi-info-circle"></i> {{ $bank['note'] }}
          </div>
        @endif
      </div>
    @endif
  </div>

  <div class="col-md-6">
    @if($pending)
      <div class="panel mb-3">
        <div class="section-title mt-0">{{ __t('สถานะการตรวจสอบ', 'Review status') }}</div>
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="badge rounded-pill" style="background:#F4E3C7;color:#8A6112;">
            <i class="bi bi-hourglass-split"></i> {{ __t('รอแอดมินตรวจสอบ', 'Awaiting approval') }}
          </span>
        </div>
        <p class="small text-secondary mb-2">
          {{ __t('ส่งสลิปเมื่อ', 'Submitted') }}
          {{ $pending->created_at->locale(app()->getLocale())->isoFormat('D MMM YYYY HH:mm') }}
        </p>
        @if($pending->slip_image)
          <img src="{{ asset($pending->slip_image) }}" alt="{{ __t('สลิป', 'Slip') }}"
               style="max-width:100%;border-radius:12px;border:1px solid var(--line);">
        @endif
      </div>
    @endif

    @if($rejected && ! $pending && $order->status === 'pending')
      <div class="alert alert-danger py-2 px-3 small mb-3" style="border-radius:12px;">
        <strong>{{ __t('สลิปก่อนหน้าไม่ผ่านการตรวจสอบ', 'Previous slip was rejected') }}</strong>
        @if($rejected->reject_reason)
          <div>{{ $rejected->reject_reason }}</div>
        @endif
        <div>{{ __t('กรุณาแนบสลิปใหม่อีกครั้ง', 'Please upload a new slip.') }}</div>
      </div>
    @endif

    @if($order->status === 'pending' && ! $pending)
      <form method="POST" action="{{ route('customer.purchase.slip', $order) }}"
            enctype="multipart/form-data" class="panel mb-3">
        @csrf
        <div class="section-title mt-0">{{ __t('แนบสลิปการโอน', 'Upload transfer slip') }}</div>

        <label class="form-label" for="method">{{ __t('ช่องทางที่โอน', 'Payment method') }}</label>
        <select class="form-select mb-3" id="method" name="method">
          <option value="transfer">{{ __t('โอนผ่านธนาคาร', 'Bank transfer') }}</option>
          <option value="promptpay">{{ __t('พร้อมเพย์', 'PromptPay') }}</option>
        </select>

        <label class="form-label" for="slip_image">{{ __t('รูปสลิป', 'Slip image') }}</label>
        <input type="file" class="form-control mb-1" id="slip_image" name="slip_image"
               accept="image/jpeg,image/png,image/webp" required>
        <div class="small text-secondary mb-3">{{ __t('JPG, PNG หรือ WEBP ขนาดไม่เกิน 4MB', 'JPG, PNG or WEBP, max 4MB') }}</div>

        <img id="slipPreview" alt="" class="mb-3 d-none"
             style="max-width:100%;border-radius:12px;border:1px solid var(--line);">

        <label class="form-label" for="reference">{{ __t('เลขอ้างอิง (ถ้ามี)', 'Reference (optional)') }}</label>
        <input type="text" class="form-control mb-3" id="reference" name="reference"
               maxlength="100" value="{{ old('reference') }}">

        <label class="form-label" for="paid_at">{{ __t('วันเวลาที่โอน', 'Transferred at') }}</label>
        <input type="datetime-local" class="form-control mb-3" id="paid_at" name="paid_at"
               max="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}">

        <button type="submit" class="btn btn-accent w-100">
          <i class="bi bi-upload"></i> {{ __t('ส่งสลิปให้แอดมินตรวจสอบ', 'Submit slip for review') }}
        </button>
      </form>

      <form method="POST" action="{{ route('customer.purchase.cancel', $order) }}"
            onsubmit="return confirm('{{ __t('ยกเลิกคำสั่งซื้อนี้?', 'Cancel this order?') }}')">
        @csrf
        <button type="submit" class="btn btn-waitlist btn-sm border w-100">
          {{ __t('ยกเลิกคำสั่งซื้อ', 'Cancel order') }}
        </button>
      </form>
    @endif
  </div>
</div>
@endsection

@section('extra-script')
<script>
  // ปุ่มคัดลอกเลขบัญชี ลูกค้าจะได้ไม่ต้องพิมพ์เอง พิมพ์ผิดแล้วเงินไปผิดบัญชี
  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = btn.dataset.copy;
      var done = function () {
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i>';
        setTimeout(function () { btn.innerHTML = original; }, 1500);
      };

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done);
        return;
      }

      // http ธรรมดาใช้ clipboard API ไม่ได้ ต้องถอยไปใช้ textarea
      var ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.select();
      try { document.execCommand('copy'); done(); } catch (e) {}
      document.body.removeChild(ta);
    });
  });

  // พรีวิวสลิปก่อนส่ง กันแนบรูปผิด
  var slipInput = document.getElementById('slip_image');
  var preview = document.getElementById('slipPreview');

  if (slipInput && preview) {
    slipInput.addEventListener('change', function () {
      var file = slipInput.files && slipInput.files[0];

      if (!file) {
        preview.classList.add('d-none');
        return;
      }

      preview.src = URL.createObjectURL(file);
      preview.classList.remove('d-none');
    });
  }
</script>
@endsection

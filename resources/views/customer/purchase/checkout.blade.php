@extends('customer.layout')
@section('title', __t('ยืนยันคำสั่งซื้อ', 'Confirm Order'))

@section('content')
<div class="page-header">
  <a href="{{ route('customer.purchase.index') }}" class="small text-decoration-none" style="color:var(--ink-soft);">
    <i class="bi bi-chevron-left"></i> {{ __t('เลือกแพ็กอื่น', 'Other packages') }}
  </a>
  <h1>{{ __t('ยืนยันคำสั่งซื้อ', 'Confirm Order') }}</h1>
</div>

@include('customer.purchase._flash')

<div class="row g-3">
  <div class="col-md-7">
    <div class="panel">
      <h4 style="font-size:1.1rem;font-weight:700;margin:0 0 .3rem;">{{ $package->name }}</h4>
      @if($package->description)
        <p class="small text-secondary">{{ $package->description }}</p>
      @endif

      <div class="menu-list mt-3">
        <div class="menu-row">
          <div class="mi"><i class="bi bi-ticket-perforated"></i></div>
          <span>{{ $package->credit_amount === null ? __t('ไม่จำกัดจำนวนครั้ง', 'Unlimited classes') : __t($package->credit_amount . ' ครั้ง', $package->credit_amount . ' classes') }}</span>
        </div>
        <div class="menu-row">
          <div class="mi"><i class="bi bi-calendar-range"></i></div>
          <span>{{ __t('ใช้ได้ ' . $package->valid_days . ' วันนับจากวันที่อนุมัติ', 'Valid ' . $package->valid_days . ' days from approval') }}</span>
        </div>
        @if($package->classTypes->isNotEmpty())
          <div class="menu-row">
            <div class="mi"><i class="bi bi-person-arms-up"></i></div>
            <span>{{ $package->classTypes->pluck('name')->join(', ') }}</span>
          </div>
        @endif
        {{-- หน้าสุดท้ายก่อนจ่ายเงิน ต้องบอกให้ชัดว่าซื้อไปแล้วใช้ได้สาขาไหน --}}
        <div class="menu-row">
          <div class="mi"><i class="bi bi-geo-alt"></i></div>
          <span>
            @if($package->all_branches)
              {{ __t('ใช้ได้ทุกสาขา', 'Valid at all branches') }}
            @else
              <strong>{{ __t('ใช้ได้เฉพาะ', 'Valid only at') }} {{ $package->branches->map(fn ($b) => $b->short_name ?: $b->name)->implode(', ') }}</strong>
            @endif
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-5">
    <form method="POST" action="{{ route('customer.purchase.store', $package) }}" class="panel">
      @csrf

      @if($package->once_per_customer)
        <input type="hidden" name="quantity" value="1">
        <div class="small text-secondary mb-3">
          <i class="bi bi-info-circle"></i> {{ __t('แพ็กทดลองซื้อได้คนละ 1 ครั้ง', 'Trial package: 1 per customer') }}
        </div>
      @else
        <label class="form-label" for="quantity">{{ __t('จำนวน', 'Quantity') }}</label>
        <select class="form-select mb-3" id="quantity" name="quantity"
                data-price="{{ (float) $package->price }}">
          @for($i = 1; $i <= 10; $i++)
            <option value="{{ $i }}">{{ $i }}</option>
          @endfor
        </select>
      @endif

      <div class="d-flex justify-content-between align-items-center py-2 border-top" style="border-color:var(--line)!important;">
        <span class="small text-secondary">{{ __t('ราคาต่อหน่วย', 'Unit price') }}</span>
        <span>{{ number_format($package->price) }} ฿</span>
      </div>
      <div class="d-flex justify-content-between align-items-center py-2 border-top" style="border-color:var(--line)!important;">
        <strong>{{ __t('ยอดรวม', 'Total') }}</strong>
        <strong id="totalAmount" style="font-size:1.25rem;color:var(--accent-deep);">
          {{ number_format($package->price) }} ฿
        </strong>
      </div>

      <button type="submit" class="btn btn-accent w-100 mt-3">
        {{ __t('ยืนยันและไปหน้าชำระเงิน', 'Confirm and pay') }}
      </button>

      <div class="small text-secondary mt-2 text-center">
        {{ __t('ยังไม่ตัดเงิน ขั้นถัดไปจะแสดงบัญชีสำหรับโอน', 'No charge yet. Bank details appear next.') }}
      </div>
    </form>
  </div>
</div>
@endsection

@section('extra-script')
<script>
  // อัปเดตยอดรวมตอนเปลี่ยนจำนวน ให้ลูกค้าเห็นยอดก่อนกดยืนยัน
  (function () {
    var select = document.getElementById('quantity');
    var totalEl = document.getElementById('totalAmount');

    if (!select || !totalEl) return;

    var price = parseFloat(select.dataset.price || '0');

    select.addEventListener('change', function () {
      var total = price * parseInt(select.value, 10);
      totalEl.textContent = total.toLocaleString('en-US') + ' ฿';
    });
  })();
</script>
@endsection

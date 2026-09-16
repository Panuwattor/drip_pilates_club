@extends('admin.layouts.app')
@section('title', __t('เปิดบิลขายแพ็กเกจ', 'New package sale'))

@section('content')
<form method="POST" action="{{ route('admin.orders.store') }}" enctype="multipart/form-data">
  @csrf

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ลูกค้า', 'Customer') }}</div>

        @if($customer)
          <input type="hidden" name="customer_id" value="{{ $customer->id }}">
          <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:var(--ground);">
            <div style="width:42px;height:42px;border-radius:50%;background:var(--accent-soft);color:var(--accent-deep);display:flex;align-items:center;justify-content:center;">
              <i class="bi bi-person"></i>
            </div>
            <div>
              <div class="fw-semibold">{{ $customer->full_name }}</div>
              <div class="small text-secondary">{{ $customer->phone }} · {{ $customer->code }}</div>
            </div>
            <a href="{{ route('admin.orders.create') }}" class="btn btn-sm btn-outline-secondary ms-auto">{{ __t('เปลี่ยน', 'Change') }}</a>
          </div>
        @else
          <input class="form-control mb-2" id="customerSearch" placeholder="{{ __t('พิมพ์ชื่อหรือเบอร์โทรเพื่อค้นหา', 'Type a name or phone to search') }}" autocomplete="off">
          <select class="form-select" name="customer_id" id="customerSelect" size="6" required>
            <option value="">— {{ __t('ค้นหาลูกค้าด้านบน', 'Search for a customer above') }} —</option>
          </select>
        @endif
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('แพ็กเกจที่ขาย', 'Package sold') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('แพ็กเกจ', 'Package') }} <span class="text-danger">*</span></label>
          <select class="form-select" name="package_id" id="packageSelect" required>
            <option value="">— {{ __t('เลือกแพ็กเกจ', 'Select a package') }} —</option>
            @foreach($packages as $p)
              <option value="{{ $p->id }}" data-price="{{ $p->price }}">
                {{ $p->name }} —
                {{ $p->credit_amount === null ? __t('ไม่จำกัด', 'Unlimited') : $p->credit_amount . ' ' . __t('ครั้ง', 'credits') }},
                {{ $p->valid_days }} {{ __t('วัน', 'days') }} — {{ number_format($p->price) }} {{ __t('บาท', 'THB') }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label">{{ __t('จำนวน', 'Quantity') }} <span class="text-danger">*</span></label>
            <input class="form-control" type="number" name="quantity" id="qtyInput" value="1" min="1" max="10" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __t('ส่วนลด', 'Discount') }}</label>
            <div class="input-group">
              <input class="form-control" type="number" step="0.01" name="discount" id="discountInput" value="0" min="0">
              <span class="input-group-text">฿</span>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __t('สาขาที่ขาย', 'Selling branch') }}</label>
            <select class="form-select" name="branch_id">
              <option value="">— {{ __t('ไม่ระบุ', 'Not set') }} —</option>
              @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected($customer?->home_branch_id == $b->id)>{{ $b->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __t('หมายเหตุส่วนลด', 'Discount note') }}</label>
            <input class="form-control" name="discount_note" placeholder="{{ __t('เช่น โปรเปิดสาขา', 'e.g. branch opening promo') }}">
          </div>
          <div class="col-12">
            <label class="form-label">{{ __t('หมายเหตุ', 'Note') }}</label>
            <textarea class="form-control" name="note" rows="2"></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('สรุปยอด', 'Summary') }}</div>
        <table class="table table-sm mb-0">
          <tbody>
            <tr><td class="text-secondary small">{{ __t('ราคารวม', 'Subtotal') }}</td>
                <td class="text-end" id="subtotalCell" style="font-variant-numeric:tabular-nums;">0.00</td></tr>
            <tr><td class="text-secondary small">{{ __t('ส่วนลด', 'Discount') }}</td>
                <td class="text-end" id="discountCell" style="font-variant-numeric:tabular-nums;">0.00</td></tr>
            <tr style="border-top:2px solid var(--line);">
              <td class="fw-bold">{{ __t('ยอดสุทธิ', 'Total') }}</td>
              <td class="text-end fw-bold" id="totalCell"
                  style="font-variant-numeric:tabular-nums;font-size:1.15rem;">0.00</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('การชำระเงิน', 'Payment') }}</div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="mark_paid" name="mark_paid" value="1">
          <label class="form-check-label" for="mark_paid">{{ __t('ชำระเงินแล้ว (ออกแพ็กให้ทันที)', 'Already paid (issue the package now)') }}</label>
        </div>

        <div id="paymentMethodBox" style="display:none;">
          <label class="form-label">{{ __t('ช่องทางชำระ', 'Payment method') }}</label>
          <select class="form-select" name="payment_method">
            <option value="cash">{{ __t('เงินสด', 'Cash') }}</option>
            <option value="transfer">{{ __t('โอนเงิน', 'Bank transfer') }}</option>
            <option value="promptpay">{{ __t('พร้อมเพย์', 'PromptPay') }}</option>
            <option value="credit_card">{{ __t('บัตรเครดิต', 'Credit card') }}</option>
            <option value="other">{{ __t('อื่นๆ', 'Other') }}</option>
          </select>

          <label class="form-label mt-2">{{ __t('สลิป / หลักฐานการชำระ', 'Slip / proof of payment') }}</label>
          <input class="form-control" type="file" name="slip_image" accept="image/jpeg,image/png,image/webp">
          <div class="form-text small">{{ __t('แนบได้ไม่บังคับ — รองรับ JPG, PNG, WebP ขนาดไม่เกิน 4MB', 'Optional — JPG, PNG or WebP up to 4 MB') }}</div>
        </div>

        <div class="form-text small mt-2">
          {{ __t('ถ้ายังไม่ติ๊ก จะเปิดบิลค้างไว้ แล้วค่อยแนบสลิปยืนยันภายหลัง', 'Leave unchecked to create a pending order and confirm the slip later.') }}
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('เปิดบิล', 'Create order') }}</button>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
  </div>
</form>
@endsection

@push('scripts')
<script>
var packageSelect = document.getElementById('packageSelect');
var qtyInput = document.getElementById('qtyInput');
var discountInput = document.getElementById('discountInput');

function recalc(){
  var opt = packageSelect.selectedOptions[0];
  var price = opt && opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
  var qty = parseInt(qtyInput.value, 10) || 0;
  var discount = parseFloat(discountInput.value) || 0;
  var subtotal = price * qty;
  var total = Math.max(0, subtotal - discount);

  document.getElementById('subtotalCell').textContent = subtotal.toFixed(2);
  document.getElementById('discountCell').textContent = discount.toFixed(2);
  document.getElementById('totalCell').textContent = total.toFixed(2);
}

[packageSelect, qtyInput, discountInput].forEach(function(el){
  el.addEventListener('input', recalc);
  el.addEventListener('change', recalc);
});
recalc();

document.getElementById('mark_paid').addEventListener('change', function(){
  document.getElementById('paymentMethodBox').style.display = this.checked ? '' : 'none';
});

// ค้นหาลูกค้า
var searchInput = document.getElementById('customerSearch');
var select = document.getElementById('customerSelect');
var timer = null;

searchInput && searchInput.addEventListener('input', function(){
  clearTimeout(timer);
  var q = this.value.trim();
  if(q.length < 2) return;
  timer = setTimeout(function(){
    fetch('{{ route('admin.customers.index') }}?q=' + encodeURIComponent(q), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
      select.innerHTML = '';
      (data.customers || []).forEach(function(c){
        var opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name + ' · ' + c.phone;
        select.appendChild(opt);
      });
    });
  }, 300);
});
</script>
@endpush

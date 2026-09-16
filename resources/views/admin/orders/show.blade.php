@extends('admin.layouts.app')
@section('title', __t('บิล', 'Order') . ' ' . $order->code)

@section('content')
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card-panel mb-3">
      <div class="d-flex align-items-start mb-3">
        <div>
          <div class="ttl mb-1">{{ __t('เลขที่บิล', 'Order no.') }}</div>
          <div class="fw-bold" style="font-size:1.15rem;">{{ $order->code }}</div>
        </div>
        <div class="ms-auto text-end">
          @php $om = ['pending'=>[__t('รอชำระ','Pending'),'badge-warn'],'paid'=>[__t('ชำระแล้ว','Paid'),'badge-ok'],
                      'cancelled'=>[__t('ยกเลิก','Cancelled'),'badge-soft'],'refunded'=>[__t('คืนเงิน','Refunded'),'badge-danger']]; @endphp
          <span class="badge-soft {{ $om[$order->status][1] }}">{{ $om[$order->status][0] }}</span>
          <div class="small text-secondary mt-1">{{ $order->created_at->format('j M Y H:i') }}</div>
        </div>
      </div>

      <table class="table table-sm mb-3">
        <tbody>
          <tr><td class="text-secondary small">{{ __t('ลูกค้า', 'Customer') }}</td>
              <td class="text-end">
                <a href="{{ route('admin.customers.show', $order->customer) }}" class="text-decoration-none" style="color:var(--ink);">
                  {{ $order->customer->full_name }}
                </a>
              </td></tr>
          <tr><td class="text-secondary small">{{ __t('เบอร์โทร', 'Phone') }}</td><td class="text-end">{{ $order->customer->phone }}</td></tr>
          <tr><td class="text-secondary small">{{ __t('สาขา', 'Branch') }}</td><td class="text-end">{{ $order->branch?->name ?? '—' }}</td></tr>
          <tr><td class="text-secondary small">{{ __t('พนักงาน', 'Staff') }}</td><td class="text-end">{{ $order->user?->name ?? '—' }}</td></tr>
        </tbody>
      </table>

      <div class="ttl">{{ __t('รายการ', 'Items') }}</div>
      <table class="table table-sm">
        <thead>
          <tr><th>{{ __t('แพ็กเกจ', 'Package') }}</th><th class="text-center">{{ __t('จำนวน', 'Qty') }}</th><th class="text-end">{{ __t('ราคา', 'Price') }}</th><th class="text-end">{{ __t('รวม', 'Subtotal') }}</th></tr>
        </thead>
        <tbody>
          @foreach($order->items as $item)
            <tr>
              @php $itemName = app()->getLocale() === 'en' ? ($item->name_en_snapshot ?: $item->name_th_snapshot) : ($item->name_th_snapshot ?: $item->name_en_snapshot); $itemAlt = app()->getLocale() === 'en' ? $item->name_th_snapshot : $item->name_en_snapshot; @endphp
              <td>{{ $itemName }}<div class="small text-secondary">{{ $itemAlt }}</div></td>
              <td class="text-center">{{ $item->quantity }}</td>
              <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
              <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
          @endforeach
          <tr><td colspan="3" class="text-end text-secondary small">{{ __t('ราคารวม', 'Subtotal') }}</td>
              <td class="text-end">{{ number_format($order->subtotal, 2) }}</td></tr>
          @if($order->discount > 0)
            <tr><td colspan="3" class="text-end text-secondary small">
                  {{ __t('ส่วนลด', 'Discount') }} @if($order->discount_note)({{ $order->discount_note }})@endif</td>
                <td class="text-end">-{{ number_format($order->discount, 2) }}</td></tr>
          @endif
          <tr style="border-top:2px solid var(--line);">
            <td colspan="3" class="text-end fw-bold">{{ __t('ยอดสุทธิ', 'Total') }}</td>
            <td class="text-end fw-bold" style="font-size:1.1rem;">{{ number_format($order->total, 2) }}</td>
          </tr>
        </tbody>
      </table>

      @if($order->note)
        <div class="small text-secondary mt-2"><strong>{{ __t('หมายเหตุ', 'Note') }}:</strong> {{ $order->note }}</div>
      @endif
    </div>

    @if($order->customerPackages->isNotEmpty())
      <div class="card-panel">
        <div class="ttl">{{ __t('แพ็กเกจที่ออกให้ลูกค้าแล้ว', 'Packages issued') }}</div>
        <table class="table table-sm mb-0">
          <tbody>
            @foreach($order->customerPackages as $cp)
              <tr>
                <td>{{ $cp->code }}</td>
                <td class="text-end small">
                  {{ $cp->isUnlimited() ? __t('ไม่จำกัด', 'Unlimited') : $cp->credit_remaining . '/' . $cp->credit_total . ' ' . __t('เครดิต', 'credits') }}
                  <div class="text-secondary">{{ __t('หมดอายุ', 'Expires') }} {{ $cp->expires_at->format('j M Y') }}</div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  <div class="col-lg-5">
    <div class="card-panel mb-3">
      <div class="ttl">{{ __t('การชำระเงิน', 'Payments') }}</div>

      @php
        $paid = $order->paidAmount();
        $remaining = max(0, $order->total - $paid);
      @endphp

      <table class="table table-sm mb-3">
        <tbody>
          <tr><td class="text-secondary small">{{ __t('ชำระแล้ว', 'Paid') }}</td>
              <td class="text-end">{{ number_format($paid, 2) }}</td></tr>
          <tr><td class="text-secondary small">{{ __t('คงเหลือ', 'Remaining') }}</td>
              <td class="text-end {{ $remaining > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($remaining, 2) }}</td></tr>
        </tbody>
      </table>

      @forelse($order->payments as $p)
        <div class="border rounded-3 p-2 mb-2" style="border-color:var(--line) !important;">
          <div class="d-flex align-items-start">
            <div>
              <div class="fw-semibold">{{ number_format($p->amount, 2) }} {{ __t('บาท', 'THB') }}</div>
              <div class="small text-secondary">
                @php $meth = ['cash'=>__t('เงินสด','Cash'),'transfer'=>__t('โอนเงิน','Transfer'),'promptpay'=>__t('พร้อมเพย์','PromptPay'),'credit_card'=>__t('บัตรเครดิต','Credit card'),'other'=>__t('อื่นๆ','Other')]; @endphp
                {{ $meth[$p->method] ?? $p->method }}
                @if($p->reference) · {{ $p->reference }}@endif
              </div>
              @if($p->verifiedBy)
                <div class="small text-secondary">{{ __t('ยืนยันโดย', 'Verified by') }} {{ $p->verifiedBy->name }}</div>
              @endif
              @if($p->reject_reason)
                <div class="small text-danger">{{ $p->reject_reason }}</div>
              @endif
            </div>
            <div class="ms-auto">
              @php $pm = ['pending'=>[__t('รอยืนยัน','Pending'),'badge-warn'],'verified'=>[__t('ยืนยันแล้ว','Verified'),'badge-ok'],'rejected'=>[__t('ปฏิเสธ','Rejected'),'badge-danger']]; @endphp
              <span class="badge-soft {{ $pm[$p->status][1] }}">{{ $pm[$p->status][0] }}</span>
            </div>
          </div>

          @if($p->slip_image)
            <a href="{{ asset($p->slip_image) }}" target="_blank" class="d-block mt-2">
              <img src="{{ asset($p->slip_image) }}" alt="{{ __t('สลิป', 'Payment slip') }}" style="max-width:100%;border-radius:8px;">
            </a>
          @endif

          @if($p->status === 'pending')
            <div class="d-flex gap-1 mt-2">
              <form method="POST" action="{{ route('admin.payments.verify', $p) }}" class="flex-fill"
                    data-confirm="{{ __t('ยืนยันการชำระเงินและออกแพ็กให้ลูกค้า?', 'Confirm this payment and issue the package?') }}">
                @csrf
                <button class="btn btn-sm btn-primary w-100" type="submit">
                  <i class="bi bi-check-lg"></i> {{ __t('ยืนยัน', 'Confirm') }}
                </button>
              </form>
              <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                      data-bs-target="#rejectModal{{ $p->id }}" type="button">{{ __t('ปฏิเสธ', 'Reject') }}</button>
            </div>

            <div class="modal fade" id="rejectModal{{ $p->id }}" tabindex="-1">
              <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('admin.payments.reject', $p) }}">
                  @csrf
                  <div class="modal-header"><h5 class="modal-title" style="font-size:1rem;">{{ __t('ปฏิเสธรายการชำระเงิน', 'Reject payment') }}</h5></div>
                  <div class="modal-body">
                    <label class="form-label">{{ __t('เหตุผล', 'Reason') }} <span class="text-danger">*</span></label>
                    <input class="form-control" name="reject_reason" required placeholder="{{ __t('เช่น สลิปไม่ชัด ยอดไม่ตรง', 'e.g. slip unclear, amount mismatch') }}">
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">{{ __t('ยกเลิก', 'Cancel') }}</button>
                    <button class="btn btn-danger" type="submit">{{ __t('ยืนยันปฏิเสธ', 'Reject payment') }}</button>
                  </div>
                </form>
              </div>
            </div>
          @endif
        </div>
      @empty
        <div class="empty-note mb-2"><i class="bi bi-cash-coin"></i>{{ __t('ยังไม่มีรายการชำระเงิน', 'No payments yet') }}</div>
      @endforelse

      @if($order->status !== 'paid' && $order->status !== 'cancelled')
        <hr style="border-color:var(--line);">
        <form method="POST" action="{{ route('admin.orders.payments.add', $order) }}" enctype="multipart/form-data">
          @csrf
          <div class="ttl">{{ __t('บันทึกการชำระเงิน', 'Record a payment') }}</div>
          <div class="row g-2">
            <div class="col-6">
              <input class="form-control form-control-sm" type="number" step="0.01" name="amount"
                     value="{{ $remaining }}" placeholder="{{ __t('จำนวนเงิน', 'Amount') }}" required>
            </div>
            <div class="col-6">
              <select class="form-select form-select-sm" name="method">
                <option value="transfer">{{ __t('โอนเงิน', 'Bank transfer') }}</option>
                <option value="cash">{{ __t('เงินสด', 'Cash') }}</option>
                <option value="promptpay">{{ __t('พร้อมเพย์', 'PromptPay') }}</option>
                <option value="credit_card">{{ __t('บัตรเครดิต', 'Credit card') }}</option>
                <option value="other">{{ __t('อื่นๆ', 'Other') }}</option>
              </select>
            </div>
            <div class="col-12">
              <input class="form-control form-control-sm" name="reference" placeholder="{{ __t('เลขอ้างอิง (ไม่บังคับ)', 'Reference (optional)') }}">
            </div>
            <div class="col-12">
              <label class="form-label">{{ __t('แนบสลิป', 'Attach slip') }}</label>
              <input class="form-control form-control-sm" type="file" name="slip_image" accept="image/*">
            </div>
            <div class="col-12">
              <button class="btn btn-sm btn-primary w-100" type="submit">{{ __t('บันทึก', 'Save') }}</button>
            </div>
          </div>
        </form>
      @endif
    </div>

    @if($order->status === 'pending')
      <form method="POST" action="{{ route('admin.orders.cancel', $order) }}"
            data-confirm="{{ __t('ยกเลิกบิลนี้?', 'Cancel this order?') }}">
        @csrf
        <button class="btn btn-sm btn-outline-danger w-100" type="submit">
          <i class="bi bi-x-circle"></i> {{ __t('ยกเลิกบิล', 'Cancel order') }}
        </button>
      </form>
    @endif
  </div>
</div>
@endsection

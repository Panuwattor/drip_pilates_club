@extends('admin.layouts.app')
@section('title', 'บิล ' . $order->code)

@section('content')
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card-panel mb-3">
      <div class="d-flex align-items-start mb-3">
        <div>
          <div class="ttl mb-1">เลขที่บิล</div>
          <div class="fw-bold" style="font-size:1.15rem;">{{ $order->code }}</div>
        </div>
        <div class="ms-auto text-end">
          @php $om = ['pending'=>['รอชำระ','badge-warn'],'paid'=>['ชำระแล้ว','badge-ok'],
                      'cancelled'=>['ยกเลิก','badge-soft'],'refunded'=>['คืนเงิน','badge-danger']]; @endphp
          <span class="badge-soft {{ $om[$order->status][1] }}">{{ $om[$order->status][0] }}</span>
          <div class="small text-secondary mt-1">{{ $order->created_at->format('j M Y H:i') }}</div>
        </div>
      </div>

      <table class="table table-sm mb-3">
        <tbody>
          <tr><td class="text-secondary small">ลูกค้า</td>
              <td class="text-end">
                <a href="{{ route('admin.customers.show', $order->customer) }}" class="text-decoration-none" style="color:var(--ink);">
                  {{ $order->customer->full_name }}
                </a>
              </td></tr>
          <tr><td class="text-secondary small">เบอร์โทร</td><td class="text-end">{{ $order->customer->phone }}</td></tr>
          <tr><td class="text-secondary small">สาขา</td><td class="text-end">{{ $order->branch?->name_th ?? '—' }}</td></tr>
          <tr><td class="text-secondary small">พนักงาน</td><td class="text-end">{{ $order->user?->name ?? '—' }}</td></tr>
        </tbody>
      </table>

      <div class="ttl">รายการ</div>
      <table class="table table-sm">
        <thead>
          <tr><th>แพ็กเกจ</th><th class="text-center">จำนวน</th><th class="text-end">ราคา</th><th class="text-end">รวม</th></tr>
        </thead>
        <tbody>
          @foreach($order->items as $item)
            <tr>
              <td>{{ $item->name_th_snapshot }}<div class="small text-secondary">{{ $item->name_en_snapshot }}</div></td>
              <td class="text-center">{{ $item->quantity }}</td>
              <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
              <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
          @endforeach
          <tr><td colspan="3" class="text-end text-secondary small">ราคารวม</td>
              <td class="text-end">{{ number_format($order->subtotal, 2) }}</td></tr>
          @if($order->discount > 0)
            <tr><td colspan="3" class="text-end text-secondary small">
                  ส่วนลด @if($order->discount_note)({{ $order->discount_note }})@endif</td>
                <td class="text-end">-{{ number_format($order->discount, 2) }}</td></tr>
          @endif
          <tr style="border-top:2px solid var(--line);">
            <td colspan="3" class="text-end fw-bold">ยอดสุทธิ</td>
            <td class="text-end fw-bold" style="font-size:1.1rem;">{{ number_format($order->total, 2) }}</td>
          </tr>
        </tbody>
      </table>

      @if($order->note)
        <div class="small text-secondary mt-2"><strong>หมายเหตุ:</strong> {{ $order->note }}</div>
      @endif
    </div>

    @if($order->customerPackages->isNotEmpty())
      <div class="card-panel">
        <div class="ttl">แพ็กเกจที่ออกให้ลูกค้าแล้ว</div>
        <table class="table table-sm mb-0">
          <tbody>
            @foreach($order->customerPackages as $cp)
              <tr>
                <td>{{ $cp->code }}</td>
                <td class="text-end small">
                  {{ $cp->isUnlimited() ? 'ไม่จำกัด' : $cp->credit_remaining . '/' . $cp->credit_total . ' เครดิต' }}
                  <div class="text-secondary">หมดอายุ {{ $cp->expires_at->format('j M Y') }}</div>
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
      <div class="ttl">การชำระเงิน</div>

      @php
        $paid = $order->paidAmount();
        $remaining = max(0, $order->total - $paid);
      @endphp

      <table class="table table-sm mb-3">
        <tbody>
          <tr><td class="text-secondary small">ชำระแล้ว</td>
              <td class="text-end">{{ number_format($paid, 2) }}</td></tr>
          <tr><td class="text-secondary small">คงเหลือ</td>
              <td class="text-end {{ $remaining > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($remaining, 2) }}</td></tr>
        </tbody>
      </table>

      @forelse($order->payments as $p)
        <div class="border rounded-3 p-2 mb-2" style="border-color:var(--line) !important;">
          <div class="d-flex align-items-start">
            <div>
              <div class="fw-semibold">{{ number_format($p->amount, 2) }} บาท</div>
              <div class="small text-secondary">
                @php $meth = ['cash'=>'เงินสด','transfer'=>'โอนเงิน','promptpay'=>'พร้อมเพย์','credit_card'=>'บัตรเครดิต','other'=>'อื่นๆ']; @endphp
                {{ $meth[$p->method] ?? $p->method }}
                @if($p->reference) · {{ $p->reference }}@endif
              </div>
              @if($p->verifiedBy)
                <div class="small text-secondary">ยืนยันโดย {{ $p->verifiedBy->name }}</div>
              @endif
              @if($p->reject_reason)
                <div class="small text-danger">{{ $p->reject_reason }}</div>
              @endif
            </div>
            <div class="ms-auto">
              @php $pm = ['pending'=>['รอยืนยัน','badge-warn'],'verified'=>['ยืนยันแล้ว','badge-ok'],'rejected'=>['ปฏิเสธ','badge-danger']]; @endphp
              <span class="badge-soft {{ $pm[$p->status][1] }}">{{ $pm[$p->status][0] }}</span>
            </div>
          </div>

          @if($p->slip_image)
            <a href="{{ asset($p->slip_image) }}" target="_blank" class="d-block mt-2">
              <img src="{{ asset($p->slip_image) }}" alt="สลิป" style="max-width:100%;border-radius:8px;">
            </a>
          @endif

          @if($p->status === 'pending')
            <div class="d-flex gap-1 mt-2">
              <form method="POST" action="{{ route('admin.payments.verify', $p) }}" class="flex-fill"
                    data-confirm="ยืนยันการชำระเงินและออกแพ็กให้ลูกค้า?">
                @csrf
                <button class="btn btn-sm btn-primary w-100" type="submit">
                  <i class="bi bi-check-lg"></i> ยืนยัน
                </button>
              </form>
              <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                      data-bs-target="#rejectModal{{ $p->id }}" type="button">ปฏิเสธ</button>
            </div>

            <div class="modal fade" id="rejectModal{{ $p->id }}" tabindex="-1">
              <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('admin.payments.reject', $p) }}">
                  @csrf
                  <div class="modal-header"><h5 class="modal-title" style="font-size:1rem;">ปฏิเสธรายการชำระเงิน</h5></div>
                  <div class="modal-body">
                    <label class="form-label">เหตุผล <span class="text-danger">*</span></label>
                    <input class="form-control" name="reject_reason" required placeholder="เช่น สลิปไม่ชัด ยอดไม่ตรง">
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">ยกเลิก</button>
                    <button class="btn btn-danger" type="submit">ยืนยันปฏิเสธ</button>
                  </div>
                </form>
              </div>
            </div>
          @endif
        </div>
      @empty
        <div class="empty-note mb-2"><i class="bi bi-cash-coin"></i>ยังไม่มีรายการชำระเงิน</div>
      @endforelse

      @if($order->status !== 'paid' && $order->status !== 'cancelled')
        <hr style="border-color:var(--line);">
        <form method="POST" action="{{ route('admin.orders.payments.add', $order) }}" enctype="multipart/form-data">
          @csrf
          <div class="ttl">บันทึกการชำระเงิน</div>
          <div class="row g-2">
            <div class="col-6">
              <input class="form-control form-control-sm" type="number" step="0.01" name="amount"
                     value="{{ $remaining }}" placeholder="จำนวนเงิน" required>
            </div>
            <div class="col-6">
              <select class="form-select form-select-sm" name="method">
                <option value="transfer">โอนเงิน</option>
                <option value="cash">เงินสด</option>
                <option value="promptpay">พร้อมเพย์</option>
                <option value="credit_card">บัตรเครดิต</option>
                <option value="other">อื่นๆ</option>
              </select>
            </div>
            <div class="col-12">
              <input class="form-control form-control-sm" name="reference" placeholder="เลขอ้างอิง (ไม่บังคับ)">
            </div>
            <div class="col-12">
              <label class="form-label">แนบสลิป</label>
              <input class="form-control form-control-sm" type="file" name="slip_image" accept="image/*">
            </div>
            <div class="col-12">
              <button class="btn btn-sm btn-primary w-100" type="submit">บันทึก</button>
            </div>
          </div>
        </form>
      @endif
    </div>

    @if($order->status === 'pending')
      <form method="POST" action="{{ route('admin.orders.cancel', $order) }}"
            data-confirm="ยกเลิกบิลนี้?">
        @csrf
        <button class="btn btn-sm btn-outline-danger w-100" type="submit">
          <i class="bi bi-x-circle"></i> ยกเลิกบิล
        </button>
      </form>
    @endif
  </div>
</div>
@endsection

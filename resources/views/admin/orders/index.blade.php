@extends('admin.layouts.app')
@section('title', 'คำสั่งซื้อ')

@section('topbar-actions')
  <a href="{{ route('admin.orders.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-cart-plus"></i> เปิดบิลขาย
  </a>
@endsection

@section('content')
@if($pendingCount > 0)
  <div class="alert-soft mb-3">
    <i class="bi bi-clock-history"></i> มีรายการชำระเงินรอยืนยัน {{ $pendingCount }} รายการ
  </div>
@endif

<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
  <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
         placeholder="เลขที่บิล ชื่อ หรือเบอร์โทร" style="max-width:280px;">
  <select class="form-select form-select-sm" name="status" style="width:auto;">
    <option value="">ทุกสถานะ</option>
    @foreach(['pending'=>'รอชำระ','paid'=>'ชำระแล้ว','cancelled'=>'ยกเลิก','refunded'=>'คืนเงิน'] as $v => $l)
      <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
    @endforeach
  </select>
  <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> ค้นหา</button>
</form>

<div class="card-panel">
  @if($orders->isEmpty())
    <div class="empty-note"><i class="bi bi-receipt"></i>ไม่พบคำสั่งซื้อ</div>
  @else
    <div class="table-wrap">
      <table class="table align-middle">
        <thead>
          <tr><th>เลขที่</th><th>ลูกค้า</th><th>รายการ</th><th class="text-end">ยอดรวม</th>
              <th>สถานะ</th><th>วันที่</th><th></th></tr>
        </thead>
        <tbody>
          @foreach($orders as $o)
            <tr>
              <td class="small text-secondary">{{ $o->code }}</td>
              <td>
                <a href="{{ route('admin.customers.show', $o->customer) }}"
                   class="fw-semibold text-decoration-none" style="color:var(--ink);">{{ $o->customer->full_name }}</a>
                <div class="small text-secondary">{{ $o->customer->phone }}</div>
              </td>
              <td class="small">
                @foreach($o->items as $item)
                  <div>{{ $item->name_th_snapshot }} × {{ $item->quantity }}</div>
                @endforeach
              </td>
              <td class="text-end" style="font-variant-numeric:tabular-nums;">
                {{ number_format($o->total, 2) }}
                @if($o->discount > 0)
                  <div class="small text-secondary">ลด {{ number_format($o->discount) }}</div>
                @endif
              </td>
              <td>
                @php $om = ['pending'=>['รอชำระ','badge-warn'],'paid'=>['ชำระแล้ว','badge-ok'],
                            'cancelled'=>['ยกเลิก','badge-soft'],'refunded'=>['คืนเงิน','badge-danger']]; @endphp
                <span class="badge-soft {{ $om[$o->status][1] }}">{{ $om[$o->status][0] }}</span>
                @if($o->payments->where('status', 'pending')->count())
                  <div class="small" style="color:var(--warn);">มีสลิปรอยืนยัน</div>
                @endif
              </td>
              <td class="small text-secondary">{{ $o->created_at->format('j/n/y') }}</td>
              <td class="text-end">
                <a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>
  @endif
</div>
@endsection

@extends('admin.layouts.app')
@section('title', __t('คำสั่งซื้อ', 'Orders'))

@section('topbar-actions')
  <a href="{{ route('admin.orders.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-cart-plus"></i> {{ __t('เปิดบิลขาย', 'New sale') }}
  </a>
@endsection

@section('content')
@if($pendingCount > 0)
  <div class="alert-soft mb-3">
    <i class="bi bi-clock-history"></i> {{ __t('มีรายการชำระเงินรอยืนยัน', 'Payments awaiting confirmation:') }} {{ $pendingCount }}
  </div>
@endif

<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
  <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
         placeholder="{{ __t('เลขที่บิล ชื่อ หรือเบอร์โทร', 'Order no., name or phone') }}" style="max-width:280px;">
  <select class="form-select form-select-sm" name="status" style="width:auto;">
    <option value="">{{ __t('ทุกสถานะ', 'All statuses') }}</option>
    @foreach(['pending'=>__t('รอชำระ','Pending'),'paid'=>__t('ชำระแล้ว','Paid'),'cancelled'=>__t('ยกเลิก','Cancelled'),'refunded'=>__t('คืนเงิน','Refunded')] as $v => $l)
      <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
    @endforeach
  </select>
  <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> {{ __t('ค้นหา', 'Search') }}</button>
</form>

<div class="card-panel">
  @if($orders->isEmpty())
    <div class="empty-note"><i class="bi bi-receipt"></i>{{ __t('ไม่พบคำสั่งซื้อ', 'No orders found') }}</div>
  @else
    <div class="table-wrap">
      <table class="table align-middle">
        <thead>
          <tr><th>{{ __t('เลขที่', 'No.') }}</th><th>{{ __t('ลูกค้า', 'Customer') }}</th><th>{{ __t('รายการ', 'Items') }}</th><th class="text-end">{{ __t('ยอดรวม', 'Total') }}</th>
              <th>{{ __t('สถานะ', 'Status') }}</th><th>{{ __t('วันที่', 'Date') }}</th><th></th></tr>
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
                  <div class="small text-secondary">{{ __t('ลด', 'Disc.') }} {{ number_format($o->discount) }}</div>
                @endif
              </td>
              <td>
                @php $om = ['pending'=>[__t('รอชำระ','Pending'),'badge-warn'],'paid'=>[__t('ชำระแล้ว','Paid'),'badge-ok'],
                            'cancelled'=>[__t('ยกเลิก','Cancelled'),'badge-soft'],'refunded'=>[__t('คืนเงิน','Refunded'),'badge-danger']]; @endphp
                <span class="badge-soft {{ $om[$o->status][1] }}">{{ $om[$o->status][0] }}</span>
                @if($o->payments->where('status', 'pending')->count())
                  <div class="small" style="color:var(--warn);">{{ __t('มีสลิปรอยืนยัน', 'Slip awaiting review') }}</div>
                @endif
              </td>
              <td class="small text-secondary">{{ $o->created_at->format('d/m/Y') }}</td>
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

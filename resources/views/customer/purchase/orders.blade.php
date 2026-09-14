@extends('customer.layout')
@section('title', __t('คำสั่งซื้อของฉัน', 'My Orders'))

@section('content')
<div class="page-header">
  <h1>{{ __t('คำสั่งซื้อของฉัน', 'My Orders') }}</h1>
  <p>{{ __t('ประวัติการซื้อแพ็กเกจและสถานะการชำระเงิน', 'Your package purchases and payment status') }}</p>
</div>

@include('customer.purchase._flash')

@php
  $statusLabels = [
    'pending' => [__t('รอชำระเงิน', 'Pending'), '#F4E3C7', '#8A6112'],
    'paid' => [__t('ชำระแล้ว', 'Paid'), '#D6EBD8', '#2F6B36'],
    'cancelled' => [__t('ยกเลิก', 'Cancelled'), '#E6E8EC', '#6B7690'],
    'refunded' => [__t('คืนเงินแล้ว', 'Refunded'), '#E0E7F5', '#40598A'],
  ];
@endphp

<div class="row g-3 mb-4">
  @forelse($orders as $order)
    @php
      [$label, $bg, $fg] = $statusLabels[$order->status] ?? [$order->status, '#E6E8EC', '#6B7690'];
      $hasPendingSlip = $order->payments->contains('status', 'pending');
    @endphp
    <div class="col-md-6">
      <div class="panel h-100">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
          <div class="min-width-0">
            <div class="small text-secondary">{{ $order->code }}</div>
            <h4 style="font-size:1rem;font-weight:700;margin:.15rem 0;">
              {{ $order->items->first()?->name() }}
              @if($order->items->first() && $order->items->first()->quantity > 1)
                × {{ $order->items->first()->quantity }}
              @endif
            </h4>
            <div class="small text-secondary">
              {{ $order->created_at->locale(app()->getLocale())->isoFormat('D MMM YYYY HH:mm') }}
            </div>
          </div>
          <div class="text-end flex-shrink-0">
            <div style="font-size:1.1rem;font-weight:700;color:var(--accent-deep);">{{ number_format($order->total) }} ฿</div>
            <span class="badge rounded-pill" style="background:{{ $bg }};color:{{ $fg }};">{{ $label }}</span>
          </div>
        </div>

        @if($hasPendingSlip)
          <div class="small text-secondary mb-2">
            <i class="bi bi-hourglass-split"></i> {{ __t('รอแอดมินตรวจสอบสลิป', 'Slip awaiting approval') }}
          </div>
        @endif

        @if($order->status === 'pending')
          <a href="{{ route('customer.purchase.pay', $order) }}" class="btn btn-accent btn-sm w-100">
            {{ $hasPendingSlip ? __t('ดูสถานะ', 'View status') : __t('ชำระเงิน / แนบสลิป', 'Pay / upload slip') }}
          </a>
        @endif
      </div>
    </div>
  @empty
    <div class="col-12">
      <div class="no-class-note">
        {{ __t('ยังไม่มีคำสั่งซื้อ', 'No orders yet') }}
        <div class="mt-3">
          <a href="{{ route('customer.purchase.index') }}" class="btn btn-accent btn-sm">
            {{ __t('เลือกซื้อแพ็กเกจ', 'Browse packages') }}
          </a>
        </div>
      </div>
    </div>
  @endforelse
</div>

{{ $orders->links() }}
@endsection

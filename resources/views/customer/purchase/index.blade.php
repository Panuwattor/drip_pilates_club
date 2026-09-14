@extends('customer.layout')
@section('title', __t('ซื้อแพ็กเกจ', 'Buy Packages'))

@section('content')
<div class="page-header">
  <h1>{{ __t('ซื้อแพ็กเกจ', 'Buy Packages') }}</h1>
  <p>{{ __t('เลือกแพ็กที่ต้องการ โอนเงินแล้วแนบสลิป แอดมินจะเพิ่มเครดิตให้', 'Pick a package, transfer and upload your slip. Admin will add your credits.') }}</p>
</div>

@include('customer.purchase._flash')

@if($pendingOrders->isNotEmpty())
  <div class="section-title">{{ __t('รอชำระเงิน', 'Awaiting payment') }}</div>
  <div class="row g-3 mb-4">
    @foreach($pendingOrders as $order)
      <div class="col-md-6">
        <a href="{{ route('customer.purchase.pay', $order) }}" class="panel h-100 d-block text-decoration-none" style="color:inherit;">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="min-width-0">
              <div class="small text-secondary">{{ $order->code }}</div>
              <h4 style="font-size:1rem;font-weight:700;margin:.15rem 0;">{{ $order->items->first()?->name() }}</h4>
              <div class="small text-secondary">
                {{ $order->created_at->locale(app()->getLocale())->isoFormat('D MMM YYYY HH:mm') }}
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <div style="font-size:1.15rem;font-weight:700;color:var(--accent-deep);">{{ number_format($order->total) }} ฿</div>
              <span class="badge rounded-pill" style="background:#F4E3C7;color:#8A6112;">{{ __t('ชำระเงิน', 'Pay now') }}</span>
            </div>
          </div>
        </a>
      </div>
    @endforeach
  </div>
@endif

@foreach($groups as $groupName => $packages)
  <div class="section-title">{{ $groupName }}</div>
  <div class="row g-3 mb-4">
    @foreach($packages as $p)
      @php $blocked = $purchasedOnceIds->contains($p->id); @endphp
      <div class="col-md-4">
        <div class="panel h-100 d-flex flex-column">
          <h4 style="font-size:1rem;font-weight:700;margin:0 0 .2rem;">{{ $p->name }}</h4>
          @if($p->description)<p class="small text-secondary mb-2">{{ $p->description }}</p>@endif

          <div style="font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;font-size:1.5rem;color:var(--accent-deep);">
            {{ number_format($p->price) }} <span style="font-size:.8rem;">฿</span>
            @if($p->compare_at_price)
              <span class="small text-secondary text-decoration-line-through">{{ number_format($p->compare_at_price) }}</span>
            @endif
          </div>

          <div class="small text-secondary mt-1 mb-3">
            {{ $p->credit_amount === null ? __t('ไม่จำกัดจำนวนครั้ง', 'Unlimited classes') : __t($p->credit_amount . ' ครั้ง', $p->credit_amount . ' classes') }}
            · {{ __t('ใช้ได้ ' . $p->valid_days . ' วัน', 'valid ' . $p->valid_days . ' days') }}
          </div>

          <div class="mt-auto">
            @if($blocked)
              <button class="btn btn-accent w-100" disabled>{{ __t('ซื้อไปแล้ว', 'Already purchased') }}</button>
            @else
              <a href="{{ route('customer.purchase.checkout', $p) }}" class="btn btn-accent w-100">
                {{ __t('ซื้อแพ็กนี้', 'Buy this package') }}
              </a>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>
@endforeach

<div class="text-center mb-4">
  <a href="{{ route('customer.purchase.orders') }}" class="btn btn-waitlist btn-sm border">
    <i class="bi bi-receipt"></i> {{ __t('ประวัติคำสั่งซื้อ', 'Order history') }}
  </a>
</div>
@endsection

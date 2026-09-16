@extends('admin.layouts.app')
@section('title', __t('แดชบอร์ด', 'Dashboard'))

@section('content')
<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si"><i class="bi bi-calendar3"></i></div>
      <div class="num">{{ $stats['today_classes'] }}</div>
      <div class="lbl">{{ __t('คลาสวันนี้', "Today's classes") }}</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--sage-soft);color:var(--sage);"><i class="bi bi-check2-square"></i></div>
      <div class="num">{{ $stats['today_bookings'] }}</div>
      <div class="lbl">{{ __t('ยอดจองวันนี้', "Today's bookings") }}</div>
      <div class="fill-bar mt-2" title="{{ __t('อัตราการเต็ม', 'Fill rate') }} {{ $stats['fill_rate'] }}%">
        <span style="width:{{ min(100, $stats['fill_rate']) }}%"></span>
      </div>
      <div class="lbl" style="margin-top:.35rem;">{{ __t('เต็ม', 'Filled') }} {{ $stats['fill_rate'] }}%</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--warn-soft);color:var(--warn);"><i class="bi bi-people"></i></div>
      <div class="num">{{ number_format($stats['active_customers']) }}</div>
      <div class="lbl">{{ __t('สมาชิกที่ใช้งานอยู่', 'Active members') }}</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--ok-soft);color:var(--ok);"><i class="bi bi-cash-coin"></i></div>
      <div class="num">{{ number_format($stats['month_revenue']) }}</div>
      <div class="lbl">{{ __t('รายได้เดือนนี้ (บาท)', 'Revenue this month (THB)') }}</div>
    </div>
  </div>
</div>

@if($pendingPayments > 0)
  <div class="alert-soft alert-warn mb-3">
    <i class="bi bi-exclamation-circle-fill"></i>
    <span>{{ __t('มีรายการชำระเงินรอยืนยัน', 'Payments awaiting confirmation:') }} <strong>{{ $pendingPayments }}</strong>{{ __t(' รายการ', '') }}</span>
    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="ms-auto btn btn-sm btn-primary">{{ __t('ตรวจสอบ', 'Review') }}</a>
  </div>
@endif

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card-panel mb-3">
      <div class="ttl">{{ __t('ตารางวันนี้', "Today's schedule") }} · {{ now()->format('j M Y') }}</div>

      @if($todaySessions->isEmpty())
        <div class="empty-note"><i class="bi bi-calendar3"></i>{{ __t('วันนี้ไม่มีคลาส', 'No classes today') }}</div>
      @else
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>{{ __t('เวลา', 'Time') }}</th><th>{{ __t('คลาส', 'Class') }}</th><th>{{ __t('ครู', 'Trainer') }}</th><th>{{ __t('ห้อง', 'Room') }}</th>
                <th class="text-center">{{ __t('จอง', 'Booked') }}</th><th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($todaySessions as $s)
                @php $t = $s->actualTrainer(); @endphp
                <tr>
                  <td class="fw-bold num-cell">{{ $s->start_at->format('H:i') }}</td>
                  <td>
                    {{ $s->classType->name }}
                    @if($s->branch_id && $todaySessions->pluck('branch_id')->unique()->count() > 1)
                      <div class="small text-secondary">{{ $s->branch->short_name ?? $s->branch->name }}</div>
                    @endif
                  </td>
                  <td>
                    {{ $t?->nickname ?: $t?->name ?: '—' }}
                    @if($s->substitute_trainer_id)<span class="badge-soft badge-warn ms-1">{{ __t('สอนแทน', 'Substitute') }}</span>@endif
                  </td>
                  <td class="small text-secondary">{{ $s->room?->name ?? '—' }}</td>
                  <td class="text-center num-cell">
                    <span class="{{ $s->isFull() ? 'text-danger fw-bold' : '' }}">
                      {{ $s->booked_count }}/{{ $s->capacity }}
                    </span>
                    @if($s->waitlist_count > 0)
                      <div class="small text-secondary">{{ __t('คิว', 'Waitlist') }} {{ $s->waitlist_count }}</div>
                    @endif
                  </td>
                  <td class="text-end">
                    <a href="{{ route('admin.sessions.show', $s) }}" class="btn btn-sm btn-outline-secondary">
                      <i class="bi bi-list-check"></i>
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    <div class="card-panel">
      <div class="ttl">{{ __t('ยอดจอง 14 วันล่าสุด', 'Bookings, last 14 days') }}</div>
      @php $max = max(1, $chart->max('value')); @endphp
      <div class="mini-chart">
        @foreach($chart as $point)
          <div class="mc-col" title="{{ $point['label'] }} · {{ $point['value'] }}">
            <div class="mc-track">
              <div class="mc-bar" style="height:{{ max(3, round($point['value'] / $max * 100)) }}%">
                <span class="mc-val">{{ $point['value'] }}</span>
              </div>
            </div>
            <div class="mc-lbl">{{ $point['label'] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card-panel mb-3">
      <div class="ttl">{{ __t('แพ็กใกล้หมดอายุ (14 วัน)', 'Packages expiring (14 days)') }}</div>

      @if($expiringPackages->isEmpty())
        <div class="empty-note"><i class="bi bi-shield-check"></i>{{ __t('ไม่มีแพ็กที่ใกล้หมดอายุ', 'No packages expiring soon') }}</div>
      @else
        <div class="table-wrap">
          <table class="table">
            <tbody>
              @foreach($expiringPackages as $cp)
                <tr>
                  <td>
                    <a href="{{ route('admin.customers.show', $cp->customer) }}" class="text-decoration-none fw-semibold" style="color:var(--ink);">
                      {{ $cp->customer->full_name }}
                    </a>
                    <div class="small text-secondary">{{ $cp->package->name }}</div>
                  </td>
                  <td class="text-end">
                    @php $days = $cp->daysUntilExpiry(); @endphp
                    <span class="badge-soft {{ $days <= 3 ? 'badge-danger' : 'badge-warn' }}">
                      {{ __t('เหลือ ' . $days . ' วัน', $days . ' days left') }}
                    </span>
                    @if(! $cp->isUnlimited())
                      <div class="small text-secondary mt-1">{{ $cp->credit_remaining }} {{ __t('เครดิต', 'credits') }}</div>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    <div class="card-panel">
      <div class="ttl">{{ __t('การจองล่าสุด', 'Recent bookings') }}</div>

      @if($recentBookings->isEmpty())
        <div class="empty-note"><i class="bi bi-inbox"></i>{{ __t('ยังไม่มีการจอง', 'No bookings yet') }}</div>
      @else
        <div class="table-wrap">
          <table class="table">
            <tbody>
              @foreach($recentBookings as $b)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $b->customer->full_name }}</div>
                    <div class="small text-secondary">
                      {{ $b->classSession->classType->name }} ·
                      {{ $b->classSession->start_at->format('j/n H:i') }}
                    </div>
                  </td>
                  <td class="text-end">
                    @include('admin.partials.booking-status', ['status' => $b->status])
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .fill-bar{ height:5px; border-radius:99px; background:var(--ground-2); overflow:hidden; }
  .fill-bar span{ display:block; height:100%; border-radius:99px; background:var(--sage); }

  .mini-chart{ display:flex; align-items:flex-end; gap:5px; height:140px; }
  .mc-col{ flex:1; display:flex; flex-direction:column; align-items:center; gap:6px; height:100%; min-width:0; }
  .mc-track{ flex:1; width:100%; display:flex; align-items:flex-end; }
  .mc-bar{
    position:relative; width:100%; border-radius:5px 5px 2px 2px;
    background:linear-gradient(180deg, var(--accent), color-mix(in srgb, var(--accent) 72%, transparent));
    transition:filter .15s ease;
  }
  .mc-col:hover .mc-bar{ filter:brightness(1.1); }
  .mc-val{
    position:absolute; top:-1.15rem; left:0; right:0; text-align:center;
    font-size:.62rem; font-weight:700; color:var(--ink-soft);
    font-variant-numeric:tabular-nums; opacity:0; transition:opacity .15s ease;
  }
  .mc-col:hover .mc-val{ opacity:1; }
  .mc-lbl{ font-size:.6rem; color:var(--ink-faint); white-space:nowrap; }
</style>
@endpush

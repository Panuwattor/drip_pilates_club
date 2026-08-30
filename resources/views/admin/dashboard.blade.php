@extends('admin.layouts.app')
@section('title', 'แดชบอร์ด')

@section('content')
<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si"><i class="bi bi-calendar3"></i></div>
      <div class="num">{{ $stats['today_classes'] }}</div>
      <div class="lbl">คลาสวันนี้</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--sage-soft);color:var(--sage);"><i class="bi bi-check2-square"></i></div>
      <div class="num">{{ $stats['today_bookings'] }}</div>
      <div class="lbl">ยอดจองวันนี้</div>
      <div class="fill-bar mt-2" title="อัตราการเต็ม {{ $stats['fill_rate'] }}%">
        <span style="width:{{ min(100, $stats['fill_rate']) }}%"></span>
      </div>
      <div class="lbl" style="margin-top:.35rem;">เต็ม {{ $stats['fill_rate'] }}%</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--warn-soft);color:var(--warn);"><i class="bi bi-people"></i></div>
      <div class="num">{{ number_format($stats['active_customers']) }}</div>
      <div class="lbl">สมาชิกที่ใช้งานอยู่</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--ok-soft);color:var(--ok);"><i class="bi bi-cash-coin"></i></div>
      <div class="num">{{ number_format($stats['month_revenue']) }}</div>
      <div class="lbl">รายได้เดือนนี้ (บาท)</div>
    </div>
  </div>
</div>

@if($pendingPayments > 0)
  <div class="alert-soft alert-warn mb-3">
    <i class="bi bi-exclamation-circle-fill"></i>
    <span>มีรายการชำระเงินรอยืนยัน <strong>{{ $pendingPayments }}</strong> รายการ</span>
    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="ms-auto btn btn-sm btn-primary">ตรวจสอบ</a>
  </div>
@endif

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card-panel mb-3">
      <div class="ttl">ตารางวันนี้ · {{ now()->format('j M Y') }}</div>

      @if($todaySessions->isEmpty())
        <div class="empty-note"><i class="bi bi-calendar3"></i>วันนี้ไม่มีคลาส</div>
      @else
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>เวลา</th><th>คลาส</th><th>ครู</th><th>ห้อง</th>
                <th class="text-center">จอง</th><th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($todaySessions as $s)
                @php $t = $s->actualTrainer(); @endphp
                <tr>
                  <td class="fw-bold num-cell">{{ $s->start_at->format('H:i') }}</td>
                  <td>
                    {{ $s->classType->name_th }}
                    @if($s->branch_id && $todaySessions->pluck('branch_id')->unique()->count() > 1)
                      <div class="small text-secondary">{{ $s->branch->short_name_th ?? $s->branch->name_th }}</div>
                    @endif
                  </td>
                  <td>
                    {{ $t?->nickname_th ?: $t?->name_th ?: '—' }}
                    @if($s->substitute_trainer_id)<span class="badge-soft badge-warn ms-1">สอนแทน</span>@endif
                  </td>
                  <td class="small text-secondary">{{ $s->room?->name_th ?? '—' }}</td>
                  <td class="text-center num-cell">
                    <span class="{{ $s->isFull() ? 'text-danger fw-bold' : '' }}">
                      {{ $s->booked_count }}/{{ $s->capacity }}
                    </span>
                    @if($s->waitlist_count > 0)
                      <div class="small text-secondary">คิว {{ $s->waitlist_count }}</div>
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
      <div class="ttl">ยอดจอง 14 วันล่าสุด</div>
      @php $max = max(1, $chart->max('value')); @endphp
      <div class="mini-chart">
        @foreach($chart as $point)
          <div class="mc-col" title="{{ $point['label'] }} · {{ $point['value'] }} รายการ">
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
      <div class="ttl">แพ็กใกล้หมดอายุ (14 วัน)</div>

      @if($expiringPackages->isEmpty())
        <div class="empty-note"><i class="bi bi-shield-check"></i>ไม่มีแพ็กที่ใกล้หมดอายุ</div>
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
                    <div class="small text-secondary">{{ $cp->package->name_th }}</div>
                  </td>
                  <td class="text-end">
                    @php $days = $cp->daysUntilExpiry(); @endphp
                    <span class="badge-soft {{ $days <= 3 ? 'badge-danger' : 'badge-warn' }}">
                      เหลือ {{ $days }} วัน
                    </span>
                    @if(! $cp->isUnlimited())
                      <div class="small text-secondary mt-1">{{ $cp->credit_remaining }} เครดิต</div>
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
      <div class="ttl">การจองล่าสุด</div>

      @if($recentBookings->isEmpty())
        <div class="empty-note"><i class="bi bi-inbox"></i>ยังไม่มีการจอง</div>
      @else
        <div class="table-wrap">
          <table class="table">
            <tbody>
              @foreach($recentBookings as $b)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $b->customer->full_name }}</div>
                    <div class="small text-secondary">
                      {{ $b->classSession->classType->name_th }} ·
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

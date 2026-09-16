@extends('admin.layouts.app')
@section('title', __t('รายงาน', 'Reports'))

@section('content')
<form method="GET" class="d-flex flex-wrap align-items-end gap-2 mb-3">
  <div>
    <label class="form-label">{{ __t('ตั้งแต่', 'From') }}</label>
    <input class="form-control form-control-sm" type="date" name="from" value="{{ $from }}">
  </div>
  <div>
    <label class="form-label">{{ __t('ถึง', 'To') }}</label>
    <input class="form-control form-control-sm" type="date" name="to" value="{{ $to }}">
  </div>
  <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> {{ __t('ดูรายงาน', 'Run report') }}</button>
</form>

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--ok-soft);color:var(--ok);"><i class="bi bi-cash-coin"></i></div>
      <div class="num">{{ number_format($totalRevenue) }}</div>
      <div class="lbl">{{ __t('รายได้รวม (บาท)', 'Total revenue (THB)') }}</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si"><i class="bi bi-check2-square"></i></div>
      <div class="num">{{ number_format($totalBookings) }}</div>
      <div class="lbl">{{ __t('ยอดจองทั้งหมด', 'Total bookings') }}</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--sage-soft);color:var(--sage);"><i class="bi bi-person-check"></i></div>
      <div class="num">{{ $attendanceRate }}%</div>
      <div class="lbl">{{ __t('อัตราการเข้าเรียน', 'Attendance rate') }}</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--danger-soft);color:var(--danger);"><i class="bi bi-person-x"></i></div>
      <div class="num">{{ $noShowRate }}%</div>
      <div class="lbl">{{ __t('อัตราไม่มาเรียน', 'No-show rate') }}</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card-panel mb-3">
      <div class="ttl">{{ __t('รายได้แยกตามสาขา', 'Revenue by branch') }}</div>
      @if($revenueByBranch->isEmpty())
        <div class="empty-note"><i class="bi bi-graph-up"></i>{{ __t('ไม่มีรายได้ในช่วงนี้', 'No revenue in this period') }}</div>
      @else
        <table class="table">
          <thead><tr><th>{{ __t('สาขา', 'Branch') }}</th><th class="text-center">{{ __t('บิล', 'Orders') }}</th><th class="text-end">{{ __t('รายได้', 'Revenue') }}</th></tr></thead>
          <tbody>
            @foreach($revenueByBranch as $r)
              <tr>
                <td>{{ $r['branch'] }}</td>
                <td class="text-center">{{ $r['orders'] }}</td>
                <td class="text-end" style="font-variant-numeric:tabular-nums;">{{ number_format($r['total'], 2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div class="card-panel">
      <div class="ttl">{{ __t('สถานะการจอง', 'Booking statuses') }}</div>
      <table class="table">
        <tbody>
          @php $sm = ['confirmed'=>__t('ยืนยันแล้ว','Confirmed'),'attended'=>__t('เข้าเรียนแล้ว','Attended'),'waitlisted'=>__t('คิวสำรอง','Waitlisted'),
                      'cancelled'=>__t('ยกเลิก','Cancelled'),'late_cancelled'=>__t('ยกเลิกช้า','Late cancelled'),'no_show'=>__t('ไม่มาเรียน','No show')]; @endphp
          @forelse($bookingStats as $status => $count)
            <tr>
              <td>{{ $sm[$status] ?? $status }}</td>
              <td class="text-end" style="font-variant-numeric:tabular-nums;">{{ number_format($count) }}</td>
              <td class="text-end text-secondary small" style="width:60px;">
                {{ $totalBookings > 0 ? round($count / $totalBookings * 100) : 0 }}%
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center text-secondary small py-3">{{ __t('ไม่มีข้อมูล', 'No data') }}</td></tr>
          @endforelse
          <tr style="border-top:2px solid var(--line);">
            <td class="fw-bold">{{ __t('อัตราการเต็มของคลาส', 'Class fill rate') }}</td>
            <td class="text-end fw-bold" colspan="2">{{ $fillRate }}%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card-panel mb-3">
      <div class="ttl">{{ __t('คลาสยอดนิยม', 'Popular classes') }}</div>
      @if($popularClasses->isEmpty())
        <div class="empty-note"><i class="bi bi-bar-chart"></i>{{ __t('ไม่มีข้อมูล', 'No data') }}</div>
      @else
        <table class="table">
          <thead><tr><th>{{ __t('คลาส', 'Class') }}</th><th class="text-center">{{ __t('รอบ', 'Sessions') }}</th><th class="text-end">{{ __t('ยอดจอง', 'Bookings') }}</th></tr></thead>
          <tbody>
            @foreach($popularClasses as $c)
              <tr>
                <td>{{ $c->classType?->name ?? '—' }}</td>
                <td class="text-center">{{ $c->sessions }}</td>
                <td class="text-end" style="font-variant-numeric:tabular-nums;">{{ number_format($c->booked) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div class="card-panel">
      <div class="ttl">{{ __t('ครูยอดนิยม', 'Popular trainers') }}</div>
      @if($popularTrainers->isEmpty())
        <div class="empty-note"><i class="bi bi-bar-chart"></i>{{ __t('ไม่มีข้อมูล', 'No data') }}</div>
      @else
        <table class="table">
          <thead><tr><th>{{ __t('ครู', 'Trainer') }}</th><th class="text-center">{{ __t('รอบ', 'Sessions') }}</th><th class="text-end">{{ __t('ยอดจอง', 'Bookings') }}</th></tr></thead>
          <tbody>
            @foreach($popularTrainers as $t)
              <tr>
                <td>{{ $t->trainer?->name ?? '—' }}</td>
                <td class="text-center">{{ $t->sessions }}</td>
                <td class="text-end" style="font-variant-numeric:tabular-nums;">{{ number_format($t->booked) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>
</div>
@endsection

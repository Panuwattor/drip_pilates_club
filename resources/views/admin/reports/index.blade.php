@extends('admin.layouts.app')
@section('title', 'รายงาน')

@section('content')
<form method="GET" class="d-flex flex-wrap align-items-end gap-2 mb-3">
  <div>
    <label class="form-label">ตั้งแต่</label>
    <input class="form-control form-control-sm" type="date" name="from" value="{{ $from }}">
  </div>
  <div>
    <label class="form-label">ถึง</label>
    <input class="form-control form-control-sm" type="date" name="to" value="{{ $to }}">
  </div>
  <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> ดูรายงาน</button>
</form>

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--ok-soft);color:var(--ok);"><i class="bi bi-cash-coin"></i></div>
      <div class="num">{{ number_format($totalRevenue) }}</div>
      <div class="lbl">รายได้รวม (บาท)</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si"><i class="bi bi-check2-square"></i></div>
      <div class="num">{{ number_format($totalBookings) }}</div>
      <div class="lbl">ยอดจองทั้งหมด</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--sage-soft);color:var(--sage);"><i class="bi bi-person-check"></i></div>
      <div class="num">{{ $attendanceRate }}%</div>
      <div class="lbl">อัตราการเข้าเรียน</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-tile">
      <div class="si" style="background:var(--danger-soft);color:var(--danger);"><i class="bi bi-person-x"></i></div>
      <div class="num">{{ $noShowRate }}%</div>
      <div class="lbl">อัตราไม่มาเรียน</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card-panel mb-3">
      <div class="ttl">รายได้แยกตามสาขา</div>
      @if($revenueByBranch->isEmpty())
        <div class="empty-note"><i class="bi bi-graph-up"></i>ไม่มีรายได้ในช่วงนี้</div>
      @else
        <table class="table">
          <thead><tr><th>สาขา</th><th class="text-center">บิล</th><th class="text-end">รายได้</th></tr></thead>
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
      <div class="ttl">สถานะการจอง</div>
      <table class="table">
        <tbody>
          @php $sm = ['confirmed'=>'ยืนยันแล้ว','attended'=>'เข้าเรียนแล้ว','waitlisted'=>'คิวสำรอง',
                      'cancelled'=>'ยกเลิก','late_cancelled'=>'ยกเลิกช้า','no_show'=>'ไม่มาเรียน']; @endphp
          @forelse($bookingStats as $status => $count)
            <tr>
              <td>{{ $sm[$status] ?? $status }}</td>
              <td class="text-end" style="font-variant-numeric:tabular-nums;">{{ number_format($count) }}</td>
              <td class="text-end text-secondary small" style="width:60px;">
                {{ $totalBookings > 0 ? round($count / $totalBookings * 100) : 0 }}%
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center text-secondary small py-3">ไม่มีข้อมูล</td></tr>
          @endforelse
          <tr style="border-top:2px solid var(--line);">
            <td class="fw-bold">อัตราการเต็มของคลาส</td>
            <td class="text-end fw-bold" colspan="2">{{ $fillRate }}%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card-panel mb-3">
      <div class="ttl">คลาสยอดนิยม</div>
      @if($popularClasses->isEmpty())
        <div class="empty-note"><i class="bi bi-bar-chart"></i>ไม่มีข้อมูล</div>
      @else
        <table class="table">
          <thead><tr><th>คลาส</th><th class="text-center">รอบ</th><th class="text-end">ยอดจอง</th></tr></thead>
          <tbody>
            @foreach($popularClasses as $c)
              <tr>
                <td>{{ $c->classType?->name_th ?? '—' }}</td>
                <td class="text-center">{{ $c->sessions }}</td>
                <td class="text-end" style="font-variant-numeric:tabular-nums;">{{ number_format($c->booked) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div class="card-panel">
      <div class="ttl">ครูยอดนิยม</div>
      @if($popularTrainers->isEmpty())
        <div class="empty-note"><i class="bi bi-bar-chart"></i>ไม่มีข้อมูล</div>
      @else
        <table class="table">
          <thead><tr><th>ครู</th><th class="text-center">รอบ</th><th class="text-end">ยอดจอง</th></tr></thead>
          <tbody>
            @foreach($popularTrainers as $t)
              <tr>
                <td>{{ $t->trainer?->name_th ?? '—' }}</td>
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

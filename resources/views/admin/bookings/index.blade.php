@extends('admin.layouts.app')
@section('title', 'การจอง')

@section('content')
<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
  {{-- คงลำดับการเรียงไว้เมื่อกดค้นหา ไม่งั้นเด้งกลับไปค่าเริ่มต้น --}}
  <input type="hidden" name="sort" value="{{ $sort }}">
  <input type="hidden" name="dir" value="{{ $dir }}">

  <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
         placeholder="รหัสจอง ชื่อ หรือเบอร์โทร" style="max-width:250px;">

  <select class="form-select form-select-sm" name="branch" style="width:auto;">
    <option value="">ทุกสาขา</option>
    @foreach($branches as $b)
      <option value="{{ $b->id }}" @selected(request('branch') == $b->id)>{{ $b->name_th }}</option>
    @endforeach
  </select>

  <select class="form-select form-select-sm" name="status" style="width:auto;">
    <option value="">ทุกสถานะ</option>
    @foreach(['confirmed'=>'ยืนยันแล้ว','waitlisted'=>'คิวสำรอง','attended'=>'เข้าเรียนแล้ว',
              'cancelled'=>'ยกเลิก','late_cancelled'=>'ยกเลิกช้า','no_show'=>'ไม่มาเรียน'] as $v => $l)
      <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
    @endforeach
  </select>

  <input class="form-control form-control-sm" type="date" name="from" value="{{ request('from') }}" style="width:auto;">
  <input class="form-control form-control-sm" type="date" name="to" value="{{ request('to') }}" style="width:auto;">

  <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> ค้นหา</button>
  @if(request()->hasAny(['q','status','branch','from','to']))
    {{-- ล้างเฉพาะตัวกรอง ยังคงลำดับการเรียงที่เลือกไว้ --}}
    <a href="{{ route('admin.bookings.index', ['sort' => $sort, 'dir' => $dir]) }}"
       class="btn btn-sm btn-outline-secondary">ล้าง</a>
  @endif
</form>

<div class="card-panel">
  @if($bookings->isEmpty())
    <div class="empty-note"><i class="bi bi-check2-square"></i>ไม่พบรายการจอง</div>
  @else
    <div class="table-wrap">
      <table class="table align-middle">
        <thead>
          <tr>
            @include('admin.partials.sort-header', ['key' => 'code', 'label' => 'รหัส'])
            @include('admin.partials.sort-header', ['key' => 'customer', 'label' => 'ลูกค้า'])
            <th>คลาส</th>
            @include('admin.partials.sort-header', ['key' => 'date', 'label' => 'วันเวลา'])
            <th>สาขา</th>
            @include('admin.partials.sort-header', ['key' => 'status', 'label' => 'สถานะ'])
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($bookings as $b)
            <tr>
              <td class="small text-secondary">{{ $b->code }}</td>
              <td>
                <a href="{{ route('admin.customers.show', $b->customer) }}"
                   class="fw-semibold text-decoration-none" style="color:var(--ink);">{{ $b->customer->full_name }}</a>
                <div class="small text-secondary">{{ $b->customer->phone }}</div>
              </td>
              <td class="small">{{ $b->classSession->classType->name_th }}</td>
              <td class="small" style="white-space:nowrap;">
                {{ $b->classSession->start_at->format('d/m/Y') }}
                <div class="text-secondary">{{ $b->classSession->start_at->format('H:i') }}</div>
              </td>
              <td class="small text-secondary">
                {{ $b->classSession->branch->short_name_th ?? $b->classSession->branch->name_th }}
              </td>
              <td>
                @include('admin.partials.booking-status', ['status' => $b->status])
                @if($b->status === 'waitlisted')
                  <div class="small text-secondary">คิวที่ {{ $b->waitlist_position }}</div>
                @endif
              </td>
              <td class="text-end" style="white-space:nowrap;">
                @if($b->status === 'confirmed')
                  <form method="POST" action="{{ route('admin.bookings.checkin', $b) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-primary" type="submit" title="เช็คอิน"><i class="bi bi-check-lg"></i></button>
                  </form>
                  <form method="POST" action="{{ route('admin.bookings.noshow', $b) }}" class="d-inline"
                        data-confirm="บันทึกว่าไม่มาเรียน?">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary" type="submit" title="ไม่มาเรียน"><i class="bi bi-person-x"></i></button>
                  </form>
                  <form method="POST" action="{{ route('admin.bookings.cancel', $b) }}" class="d-inline"
                        data-confirm="ยกเลิกการจองและคืนเครดิตให้ลูกค้า?">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger" type="submit" title="ยกเลิกและคืนเครดิต"><i class="bi bi-x-lg"></i></button>
                  </form>
                @elseif(in_array($b->status, ['no_show', 'attended', 'late_cancelled', 'cancelled']))
                  {{-- ระบบปิดให้อัตโนมัติหลังคลาสจบ แอดมินย้อนกลับมาแก้ตามจริงได้ --}}
                  <form method="POST" action="{{ route('admin.bookings.reopen', $b) }}" class="d-inline"
                        data-confirm="ย้อนกลับเป็น 'ยืนยันแล้ว' เพื่อแก้ไข? @if($b->credit_refunded)เครดิตที่คืนไปจะถูกตัดกลับ@endif">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary" type="submit" title="ย้อนสถานะเพื่อแก้ไข">
                      <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                  </form>
                @endif
                <a href="{{ route('admin.sessions.show', $b->classSession) }}"
                   class="btn btn-sm btn-outline-secondary" title="ดูรอบเรียน"><i class="bi bi-list-check"></i></a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $bookings->links() }}</div>
  @endif
</div>
@endsection

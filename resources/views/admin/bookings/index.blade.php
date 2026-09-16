@extends('admin.layouts.app')
@section('title', __t('การจอง', 'Bookings'))

@section('content')
<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
  {{-- คงลำดับการเรียงไว้เมื่อกดค้นหา ไม่งั้นเด้งกลับไปค่าเริ่มต้น --}}
  <input type="hidden" name="sort" value="{{ $sort }}">
  <input type="hidden" name="dir" value="{{ $dir }}">

  <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
         placeholder="{{ __t('รหัสจอง ชื่อ หรือเบอร์โทร', 'Booking code, name or phone') }}" style="max-width:250px;">

  <select class="form-select form-select-sm" name="branch" style="width:auto;">
    <option value="">{{ __t('ทุกสาขา', 'All branches') }}</option>
    @foreach($branches as $b)
      <option value="{{ $b->id }}" @selected(request('branch') == $b->id)>{{ $b->name }}</option>
    @endforeach
  </select>

  <select class="form-select form-select-sm" name="status" style="width:auto;">
    <option value="">{{ __t('ทุกสถานะ', 'All statuses') }}</option>
    @foreach(['confirmed'=>__t('ยืนยันแล้ว','Confirmed'),'waitlisted'=>__t('คิวสำรอง','Waitlisted'),'attended'=>__t('เข้าเรียนแล้ว','Attended'),
              'cancelled'=>__t('ยกเลิก','Cancelled'),'late_cancelled'=>__t('ยกเลิกช้า','Late cancelled'),'no_show'=>__t('ไม่มาเรียน','No show')] as $v => $l)
      <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
    @endforeach
  </select>

  <input class="form-control form-control-sm" type="date" name="from" value="{{ request('from') }}" style="width:auto;">
  <input class="form-control form-control-sm" type="date" name="to" value="{{ request('to') }}" style="width:auto;">

  <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> {{ __t('ค้นหา', 'Search') }}</button>
  @if(request()->hasAny(['q','status','branch','from','to']))
    {{-- ล้างเฉพาะตัวกรอง ยังคงลำดับการเรียงที่เลือกไว้ --}}
    <a href="{{ route('admin.bookings.index', ['sort' => $sort, 'dir' => $dir]) }}"
       class="btn btn-sm btn-outline-secondary">{{ __t('ล้าง', 'Clear') }}</a>
  @endif
</form>

<div class="card-panel">
  @if($bookings->isEmpty())
    <div class="empty-note"><i class="bi bi-check2-square"></i>{{ __t('ไม่พบรายการจอง', 'No bookings found') }}</div>
  @else
    <div class="table-wrap">
      <table class="table align-middle">
        <thead>
          <tr>
            @include('admin.partials.sort-header', ['key' => 'code', 'label' => __t('รหัส', 'Code')])
            @include('admin.partials.sort-header', ['key' => 'customer', 'label' => __t('ลูกค้า', 'Customer')])
            <th>{{ __t('คลาส', 'Class') }}</th>
            @include('admin.partials.sort-header', ['key' => 'date', 'label' => __t('วันเวลา', 'Date & time')])
            <th>{{ __t('สาขา', 'Branch') }}</th>
            @include('admin.partials.sort-header', ['key' => 'status', 'label' => __t('สถานะ', 'Status')])
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
              <td class="small">{{ $b->classSession->classType->name }}</td>
              <td class="small" style="white-space:nowrap;">
                {{ $b->classSession->start_at->format('d/m/Y') }}
                <div class="text-secondary">{{ $b->classSession->start_at->format('H:i') }}</div>
              </td>
              <td class="small text-secondary">
                {{ $b->classSession->branch->short_name ?? $b->classSession->branch->name }}
              </td>
              <td>
                @include('admin.partials.booking-status', ['status' => $b->status])
                @if($b->status === 'waitlisted')
                  <div class="small text-secondary">{{ __t('คิวที่', 'Queue') }} {{ $b->waitlist_position }}</div>
                @endif
              </td>
              <td class="text-end" style="white-space:nowrap;">
                @if($b->status === 'confirmed')
                  <form method="POST" action="{{ route('admin.bookings.checkin', $b) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-primary" type="submit" title="{{ __t('เช็คอิน', 'Check in') }}"><i class="bi bi-check-lg"></i></button>
                  </form>
                  <form method="POST" action="{{ route('admin.bookings.noshow', $b) }}" class="d-inline"
                        data-confirm="{{ __t('บันทึกว่าไม่มาเรียน?', 'Mark as no-show?') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary" type="submit" title="{{ __t('ไม่มาเรียน', 'No show') }}"><i class="bi bi-person-x"></i></button>
                  </form>
                  <form method="POST" action="{{ route('admin.bookings.cancel', $b) }}" class="d-inline"
                        data-confirm="{{ __t('ยกเลิกการจองและคืนเครดิตให้ลูกค้า?', 'Cancel booking and refund credit to the customer?') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger" type="submit" title="{{ __t('ยกเลิกและคืนเครดิต', 'Cancel and refund credit') }}"><i class="bi bi-x-lg"></i></button>
                  </form>
                @elseif(in_array($b->status, ['no_show', 'attended', 'late_cancelled', 'cancelled']))
                  {{-- ระบบปิดให้อัตโนมัติหลังคลาสจบ แอดมินย้อนกลับมาแก้ตามจริงได้ --}}
                  <form method="POST" action="{{ route('admin.bookings.reopen', $b) }}" class="d-inline"
                        data-confirm="{{ __t("ย้อนกลับเป็น 'ยืนยันแล้ว' เพื่อแก้ไข?", 'Revert to Confirmed for editing?') }} @if($b->credit_refunded){{ __t('เครดิตที่คืนไปจะถูกตัดกลับ', 'The refunded credit will be deducted again.') }}@endif">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary" type="submit" title="{{ __t('ย้อนสถานะเพื่อแก้ไข', 'Revert status to edit') }}">
                      <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                  </form>
                @endif
                <a href="{{ route('admin.sessions.show', $b->classSession) }}"
                   class="btn btn-sm btn-outline-secondary" title="{{ __t('ดูรอบเรียน', 'View session') }}"><i class="bi bi-list-check"></i></a>
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

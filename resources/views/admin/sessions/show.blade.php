@extends('admin.layouts.app')
@section('title', 'รายชื่อผู้เรียน')

@section('content')
@php $trainer = $session->actualTrainer(); @endphp

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card-panel mb-3">
      <div class="d-flex align-items-start gap-3 flex-wrap">
        <div style="width:5px;align-self:stretch;min-height:60px;border-radius:3px;background:{{ $session->classType->color ?: 'var(--accent)' }};"></div>
        <div class="flex-grow-1">
          <h2 style="font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;font-size:1.3rem;margin:0;">{{ $session->classType->name_th }}</h2>
          <div class="small text-secondary mb-2">{{ $session->classType->name_en }}</div>
          <div class="d-flex flex-wrap gap-3 small">
            <span><i class="bi bi-calendar3"></i> {{ $session->start_at->locale('th')->isoFormat('dddd D MMM YYYY') }}</span>
            <span><i class="bi bi-clock"></i> {{ $session->start_at->format('H:i') }}–{{ $session->end_at->format('H:i') }}</span>
            <span><i class="bi bi-geo-alt"></i> {{ $session->branch->name_th }}</span>
            @if($session->room)<span><i class="bi bi-door-open"></i> {{ $session->room->name_th }}</span>@endif
            <span><i class="bi bi-person"></i> {{ $trainer?->name_th ?? 'ยังไม่กำหนดครู' }}
              @if($session->substitute_trainer_id)<span class="badge-soft badge-warn">สอนแทน</span>@endif
            </span>
          </div>
        </div>
        <div class="text-end">
          <div style="font-family:'Noto Sans Thai','Segoe UI',-apple-system,BlinkMacSystemFont,'Inter',sans-serif;font-size:1.7rem;line-height:1;font-variant-numeric:tabular-nums;">
            {{ $session->booked_count }}<span class="text-secondary" style="font-size:1rem;">/{{ $session->capacity }}</span>
          </div>
          <div class="small text-secondary">ที่นั่งที่จองแล้ว</div>
        </div>
      </div>

      @if($session->status === 'cancelled')
        <div class="alert alert-danger py-2 mt-3 mb-0 small">
          <strong>รอบนี้ถูกยกเลิกแล้ว</strong> — {{ $session->cancel_reason_th }}
        </div>
      @endif
    </div>

    <div class="card-panel">
      <div class="d-flex align-items-center mb-2">
        <div class="ttl mb-0">รายชื่อผู้เรียน</div>
        @if($session->status !== 'cancelled')
          <button class="btn btn-sm btn-outline-secondary ms-auto" data-bs-toggle="modal" data-bs-target="#addBookingModal">
            <i class="bi bi-plus-lg"></i> จองให้ลูกค้า
          </button>
        @endif
      </div>

      @if($bookings->isEmpty())
        <div class="empty-note"><i class="bi bi-person-slash"></i>ยังไม่มีผู้จองรอบนี้</div>
      @else
        <div class="table-wrap">
          <table class="table align-middle">
            <thead>
              <tr><th>#</th><th>ลูกค้า</th><th>แพ็กเกจ</th><th>สถานะ</th><th></th></tr>
            </thead>
            <tbody>
              @foreach($bookings as $i => $b)
                <tr>
                  <td class="text-secondary small">
                    {{ $b->status === 'waitlisted' ? 'คิว ' . $b->waitlist_position : $i + 1 }}
                  </td>
                  <td>
                    <a href="{{ route('admin.customers.show', $b->customer) }}"
                       class="fw-semibold text-decoration-none" style="color:var(--ink);">
                      {{ $b->customer->full_name }}
                    </a>
                    <div class="small text-secondary">
                      {{ $b->customer->phone }} · {{ $b->customer->code }}
                    </div>
                    @if($b->customer->medical_note)
                      <div class="small mt-1" style="color:var(--warn);">
                        <i class="bi bi-heart-pulse"></i> {{ $b->customer->medical_note }}
                      </div>
                    @endif
                    @if($b->customer->is_pregnant)
                      <span class="badge-soft badge-warn mt-1">ตั้งครรภ์</span>
                    @endif
                  </td>
                  <td class="small text-secondary">
                    {{ $b->customerPackage?->package?->name_th ?? '—' }}
                    @if($b->credit_used > 0)
                      <div>ใช้ {{ rtrim(rtrim(number_format($b->credit_used, 2), '0'), '.') }} เครดิต</div>
                    @endif
                  </td>
                  <td>@include('admin.partials.booking-status', ['status' => $b->status])</td>
                  <td class="text-end" style="white-space:nowrap;">
                    @if($b->status === 'confirmed')
                      <form method="POST" action="{{ route('admin.bookings.checkin', $b) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-primary" type="submit" title="เช็คอิน">
                          <i class="bi bi-check-lg"></i>
                        </button>
                      </form>
                      <form method="POST" action="{{ route('admin.bookings.noshow', $b) }}" class="d-inline"
                            data-confirm="บันทึกว่า {{ $b->customer->full_name }} ไม่มาเรียน?">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary" type="submit" title="ไม่มาเรียน">
                          <i class="bi bi-person-x"></i>
                        </button>
                      </form>
                      <form method="POST" action="{{ route('admin.bookings.cancel', $b) }}" class="d-inline"
                            data-confirm="ยกเลิกการจองของ {{ $b->customer->full_name }} และคืนเครดิต?">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger" type="submit" title="ยกเลิกและคืนเครดิต">
                          <i class="bi bi-x-lg"></i>
                        </button>
                      </form>
                    @elseif($b->status === 'waitlisted')
                      <form method="POST" action="{{ route('admin.bookings.cancel', $b) }}" class="d-inline"
                            data-confirm="เอา {{ $b->customer->full_name }} ออกจากคิว?">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger" type="submit" title="ออกจากคิว">
                          <i class="bi bi-x-lg"></i>
                        </button>
                      </form>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

  <div class="col-lg-4">
    @if($session->status !== 'cancelled')
      <div class="card-panel mb-3">
        <div class="ttl">ครูสอนแทน</div>
        <form method="POST" action="{{ route('admin.sessions.substitute', $session) }}">
          @csrf
          <select class="form-select form-select-sm mb-2" name="substitute_trainer_id">
            <option value="">— ไม่มี (ครูหลักสอนเอง) —</option>
            @foreach($trainers as $t)
              <option value="{{ $t->id }}" @selected($session->substitute_trainer_id == $t->id)>{{ $t->name_th }}</option>
            @endforeach
          </select>
          <button class="btn btn-sm btn-primary w-100" type="submit">บันทึกครูสอนแทน</button>
        </form>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">จัดการรอบนี้</div>
        <a href="{{ route('admin.sessions.edit', $session) }}" class="btn btn-sm btn-outline-secondary w-100 mb-2">
          <i class="bi bi-pencil"></i> แก้ไขรายละเอียดรอบ
        </a>
        <button class="btn btn-sm btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelSessionModal">
          <i class="bi bi-x-circle"></i> ยกเลิกรอบนี้
        </button>
        <div class="form-text small mt-1">
          ยกเลิกรอบจะคืนเครดิตให้ผู้จองทุกคนและส่งแจ้งเตือนอัตโนมัติ
        </div>
      </div>
    @endif

    <div class="card-panel">
      <div class="ttl">สรุป</div>
      <table class="table table-sm mb-0">
        <tbody>
          <tr><td class="text-secondary small">ที่นั่งทั้งหมด</td><td class="text-end">{{ $session->capacity }}</td></tr>
          <tr><td class="text-secondary small">จองแล้ว</td><td class="text-end">{{ $session->booked_count }}</td></tr>
          <tr><td class="text-secondary small">ที่ว่าง</td><td class="text-end">{{ $session->spotsLeft() }}</td></tr>
          <tr><td class="text-secondary small">คิวสำรอง</td><td class="text-end">{{ $session->waitlist_count }}</td></tr>
          <tr><td class="text-secondary small">เช็คอินแล้ว</td><td class="text-end">{{ $session->attended_count }}</td></tr>
          <tr><td class="text-secondary small">ใช้เครดิต</td>
              <td class="text-end">{{ rtrim(rtrim(number_format($session->credit_cost, 2), '0'), '.') }}</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- จองให้ลูกค้า --}}
<div class="modal fade" id="addBookingModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('admin.sessions.book', $session) }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title" style="font-size:1rem;">จองให้ลูกค้า</h5>
        <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">ค้นหาลูกค้า</label>
        <input class="form-control mb-2" id="customerSearch" placeholder="พิมพ์ชื่อหรือเบอร์โทร" autocomplete="off">
        <select class="form-select" name="customer_id" id="customerSelect" size="6" required>
          <option value="">— เลือกลูกค้า —</option>
        </select>
        <div class="form-text small mt-2">
          ระบบจะตัดเครดิตจากแพ็กที่ใกล้หมดอายุที่สุด ถ้าคลาสเต็มจะเข้าคิวสำรองให้อัตโนมัติ
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">ยกเลิก</button>
        <button class="btn btn-primary" type="submit">ยืนยันการจอง</button>
      </div>
    </form>
  </div>
</div>

{{-- ยกเลิกรอบ --}}
<div class="modal fade" id="cancelSessionModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('admin.sessions.cancel', $session) }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title" style="font-size:1rem;">ยกเลิกรอบเรียน</h5>
        <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
      </div>
      <div class="modal-body">
        <div class="alert-soft mb-3 small">
          ผู้จอง {{ $session->booked_count }} คนจะได้รับเครดิตคืนทั้งหมด และได้รับแจ้งเตือนพร้อมเหตุผลที่ระบุ
        </div>
        <div class="mb-2">
          <label class="form-label">เหตุผล (ภาษาไทย) <span class="text-danger">*</span></label>
          <input class="form-control" name="reason_th" placeholder="เช่น ครูป่วยกะทันหัน" required>
        </div>
        <div class="mb-2">
          <label class="form-label">เหตุผล (English) <span class="text-danger">*</span></label>
          <input class="form-control" name="reason_en" placeholder="e.g. Instructor unwell" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">ไม่ยกเลิก</button>
        <button class="btn btn-danger" type="submit">ยืนยันยกเลิกรอบนี้</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
// ค้นหาลูกค้าแบบสด
var searchInput = document.getElementById('customerSearch');
var select = document.getElementById('customerSelect');
var timer = null;

searchInput && searchInput.addEventListener('input', function(){
  clearTimeout(timer);
  var q = this.value.trim();
  if(q.length < 2){ return; }
  timer = setTimeout(function(){
    fetch('{{ route('admin.customers.index') }}?q=' + encodeURIComponent(q), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
      select.innerHTML = '';
      (data.customers || []).forEach(function(c){
        var opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name + ' · ' + c.phone + ' · ' + c.credits + ' เครดิต';
        select.appendChild(opt);
      });
      if(!select.options.length){
        var none = document.createElement('option');
        none.textContent = 'ไม่พบลูกค้า';
        none.disabled = true;
        select.appendChild(none);
      }
    });
  }, 300);
});
</script>
@endpush

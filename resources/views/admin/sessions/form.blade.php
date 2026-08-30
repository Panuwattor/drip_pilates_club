@extends('admin.layouts.app')
@section('title', 'แก้ไขรอบเรียน')

@section('content')
<form method="POST" action="{{ route('admin.sessions.update', $session) }}">
  @csrf @method('PUT')

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">รายละเอียดรอบนี้</div>

        <div class="alert-soft small mb-3">
          <i class="bi bi-info-circle"></i>
          แก้ที่นี่กระทบเฉพาะรอบนี้รอบเดียว ไม่กระทบตารางประจำสัปดาห์
        </div>

        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <label class="form-label">ประเภทคลาส <span class="text-danger">*</span></label>
            <select class="form-select" name="class_type_id" required>
              @foreach($classTypes as $ct)
                <option value="{{ $ct->id }}" @selected($session->class_type_id == $ct->id)>{{ $ct->name_th }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">ห้อง</label>
            <select class="form-select" name="room_id">
              <option value="">— ไม่ระบุ —</option>
              @foreach($rooms as $r)
                <option value="{{ $r->id }}" @selected($session->room_id == $r->id)>
                  {{ $r->name_th }} ({{ $r->capacity }} ที่)
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">ครูหลัก</label>
            <select class="form-select" name="trainer_id">
              <option value="">— ยังไม่กำหนด —</option>
              @foreach($trainers as $t)
                <option value="{{ $t->id }}" @selected($session->trainer_id == $t->id)>{{ $t->name_th }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">ครูสอนแทน</label>
            <select class="form-select" name="substitute_trainer_id">
              <option value="">— ไม่มี —</option>
              @foreach($trainers as $t)
                <option value="{{ $t->id }}" @selected($session->substitute_trainer_id == $t->id)>{{ $t->name_th }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">วันและเวลาเริ่ม <span class="text-danger">*</span></label>
            <input class="form-control" type="datetime-local" name="start_at"
                   value="{{ $session->start_at->format('Y-m-d\TH:i') }}" required>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">ระยะเวลา <span class="text-danger">*</span></label>
            <div class="input-group">
              <input class="form-control" type="number" name="duration_min"
                     value="{{ (int) $session->start_at->diffInMinutes($session->end_at) }}" min="15" required>
              <span class="input-group-text">นาที</span>
            </div>
          </div>
        </div>

        @include('admin.partials.bilingual-field', [
          'name' => 'note', 'label' => 'หมายเหตุถึงผู้เรียน', 'model' => $session, 'type' => 'textarea', 'rows' => 2,
        ])
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">ที่นั่งและเครดิต</div>

        <div class="mb-2">
          <label class="form-label">จำนวนที่นั่ง <span class="text-danger">*</span></label>
          <input class="form-control" type="number" name="capacity"
                 value="{{ $session->capacity }}" min="{{ max(1, $session->booked_count) }}" required>
          <div class="form-text small">
            จองไปแล้ว {{ $session->booked_count }} คน · ลดต่ำกว่านี้ไม่ได้
            @if($session->waitlist_count > 0)
              <br>ถ้าเพิ่มที่นั่ง ระบบจะเลื่อนคิวสำรอง {{ $session->waitlist_count }} คนขึ้นมาอัตโนมัติ
            @endif
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">ใช้เครดิต <span class="text-danger">*</span></label>
          <input class="form-control" type="number" step="0.5" name="credit_cost"
                 value="{{ $session->credit_cost }}" min="0" required>
          <div class="form-text small">เปลี่ยนแล้วมีผลกับคนที่จองใหม่เท่านั้น</div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> บันทึก</button>
    <a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-outline-secondary">ยกเลิก</a>
  </div>
</form>
@endsection

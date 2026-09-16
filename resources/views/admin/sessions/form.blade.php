@extends('admin.layouts.app')
@section('title', __t('แก้ไขรอบเรียน', 'Edit session'))

@section('content')
<form method="POST" action="{{ route('admin.sessions.update', $session) }}">
  @csrf @method('PUT')

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('รายละเอียดรอบนี้', 'Session details') }}</div>

        <div class="alert-soft small mb-3">
          <i class="bi bi-info-circle"></i>
          {{ __t('แก้ที่นี่กระทบเฉพาะรอบนี้รอบเดียว ไม่กระทบตารางประจำสัปดาห์', 'Changes here affect this one session only, not the weekly schedule.') }}
        </div>

        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('ประเภทคลาส', 'Class type') }} <span class="text-danger">*</span></label>
            <select class="form-select" name="class_type_id" required>
              @foreach($classTypes as $ct)
                <option value="{{ $ct->id }}" @selected($session->class_type_id == $ct->id)>{{ $ct->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('ห้อง', 'Room') }}</label>
            <select class="form-select" name="room_id">
              <option value="">— {{ __t('ไม่ระบุ', 'Not set') }} —</option>
              @foreach($rooms as $r)
                <option value="{{ $r->id }}" @selected($session->room_id == $r->id)>
                  {{ $r->name }} ({{ $r->capacity }} {{ __t('ที่', 'seats') }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('ครูหลัก', 'Main trainer') }}</label>
            <select class="form-select" name="trainer_id">
              <option value="">— {{ __t('ยังไม่กำหนด', 'Unassigned') }} —</option>
              @foreach($trainers as $t)
                <option value="{{ $t->id }}" @selected($session->trainer_id == $t->id)>{{ $t->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('ครูสอนแทน', 'Substitute trainer') }}</label>
            <select class="form-select" name="substitute_trainer_id">
              <option value="">— {{ __t('ไม่มี', 'None') }} —</option>
              @foreach($trainers as $t)
                <option value="{{ $t->id }}" @selected($session->substitute_trainer_id == $t->id)>{{ $t->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('วันและเวลาเริ่ม', 'Start date & time') }} <span class="text-danger">*</span></label>
            <input class="form-control" type="datetime-local" name="start_at"
                   value="{{ $session->start_at->format('Y-m-d\TH:i') }}" required>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('ระยะเวลา', 'Duration') }} <span class="text-danger">*</span></label>
            <div class="input-group">
              <input class="form-control" type="number" name="duration_min"
                     value="{{ (int) $session->start_at->diffInMinutes($session->end_at) }}" min="15" required>
              <span class="input-group-text">{{ __t('นาที', 'min') }}</span>
            </div>
          </div>
        </div>

        @include('admin.partials.bilingual-field', [
          'name' => 'note', 'label' => __t('หมายเหตุถึงผู้เรียน', 'Note to attendees'), 'model' => $session, 'type' => 'textarea', 'rows' => 2,
        ])
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ที่นั่งและเครดิต', 'Seats & credits') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('จำนวนที่นั่ง', 'Seats') }} <span class="text-danger">*</span></label>
          <input class="form-control" type="number" name="capacity"
                 value="{{ $session->capacity }}" min="{{ max(1, $session->booked_count) }}" required>
          <div class="form-text small">
            {{ __t('จองไปแล้ว', 'Booked') }} {{ $session->booked_count }} · {{ __t('ลดต่ำกว่านี้ไม่ได้', 'cannot go lower') }}
            @if($session->waitlist_count > 0)
              <br>{{ __t('ถ้าเพิ่มที่นั่ง ระบบจะเลื่อนคิวสำรองขึ้นมาอัตโนมัติ', 'Adding seats promotes people from the waitlist automatically') }} ({{ $session->waitlist_count }})
            @endif
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ใช้เครดิต', 'Credit cost') }} <span class="text-danger">*</span></label>
          <input class="form-control" type="number" step="0.5" name="credit_cost"
                 value="{{ $session->credit_cost }}" min="0" required>
          <div class="form-text small">{{ __t('เปลี่ยนแล้วมีผลกับคนที่จองใหม่เท่านั้น', 'Applies to new bookings only') }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
    <a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
  </div>
</form>
@endsection

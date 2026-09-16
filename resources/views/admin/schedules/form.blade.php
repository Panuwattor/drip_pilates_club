@extends('admin.layouts.app')
@section('title', $schedule->exists ? __t('แก้ไขคลาสประจำ', 'Edit recurring class') : __t('เพิ่มคลาสประจำสัปดาห์', 'Add recurring class'))

@section('content')
<form method="POST" action="{{ $schedule->exists ? route('admin.schedules.update', $schedule) : route('admin.schedules.store') }}">
  @csrf
  @if($schedule->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('คลาสนี้จัดที่ไหน เมื่อไหร่', 'Where and when') }}</div>

        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('สาขา', 'Branch') }} <span class="text-danger">*</span></label>
            <select class="form-select" name="branch_id" id="branchSelect" required>
              <option value="">— {{ __t('เลือกสาขา', 'Select a branch') }} —</option>
              @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected(old('branch_id', $schedule->branch_id) == $b->id)>{{ $b->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('ห้อง', 'Room') }}</label>
            <select class="form-select" name="room_id" id="roomSelect">
              <option value="">— {{ __t('ไม่ระบุ', 'Not set') }} —</option>
              @foreach($rooms as $r)
                <option value="{{ $r->id }}" data-branch="{{ $r->branch_id }}" data-capacity="{{ $r->capacity }}"
                        @selected(old('room_id', $schedule->room_id) == $r->id)>
                  {{ $r->name }} ({{ $r->capacity }} {{ __t('ที่', 'seats') }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('วันในสัปดาห์', 'Day of week') }} <span class="text-danger">*</span></label>
            <select class="form-select" name="day_of_week" required>
              @foreach($days as $v => $l)
                <option value="{{ $v }}" @selected((string) old('day_of_week', $schedule->day_of_week) === (string) $v)>{{ $l }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('เวลาเริ่ม', 'Start time') }} <span class="text-danger">*</span></label>
            <input class="form-control" type="time" name="start_time"
                   value="{{ old('start_time', substr($schedule->start_time ?? '08:00:00', 0, 5)) }}" required>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('ประเภทคลาส', 'Class type') }} <span class="text-danger">*</span></label>
            <select class="form-select" name="class_type_id" id="classTypeSelect" required>
              <option value="">— {{ __t('เลือกคลาส', 'Select a class') }} —</option>
              @foreach($classTypes as $ct)
                <option value="{{ $ct->id }}"
                        data-duration="{{ $ct->duration_min }}"
                        data-capacity="{{ $ct->default_capacity }}"
                        data-credit="{{ $ct->credit_cost }}"
                        @selected(old('class_type_id', $schedule->class_type_id) == $ct->id)>{{ $ct->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('ครูผู้สอน', 'Trainer') }}</label>
            <select class="form-select" name="trainer_id">
              <option value="">— {{ __t('ยังไม่กำหนด', 'Unassigned') }} —</option>
              @foreach($trainers as $t)
                <option value="{{ $t->id }}" @selected(old('trainer_id', $schedule->trainer_id) == $t->id)>
                  {{ $t->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ช่วงเวลาที่ใช้ตารางนี้', 'Active period') }}</div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">{{ __t('เริ่มใช้ตั้งแต่', 'Effective from') }} <span class="text-danger">*</span></label>
            <input class="form-control" type="date" name="effective_from"
                   value="{{ old('effective_from', optional($schedule->effective_from)->toDateString() ?? now()->toDateString()) }}" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __t('ใช้ถึงวันที่', 'Effective until') }}</label>
            <input class="form-control" type="date" name="effective_until"
                   value="{{ old('effective_until', optional($schedule->effective_until)->toDateString()) }}">
            <div class="form-text small">{{ __t('เว้นว่าง = ใช้ไปเรื่อยๆ', 'Blank = no end date') }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ที่นั่งและเครดิต', 'Seats & credits') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ระยะเวลา', 'Duration') }} <span class="text-danger">*</span></label>
          <div class="input-group">
            <input class="form-control" type="number" name="duration_min" id="durationInput"
                   value="{{ old('duration_min', $schedule->duration_min ?? 50) }}" min="15" required>
            <span class="input-group-text">{{ __t('นาที', 'min') }}</span>
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('จำนวนที่นั่ง', 'Seats') }} <span class="text-danger">*</span></label>
          <input class="form-control" type="number" name="capacity" id="capacityInput"
                 value="{{ old('capacity', $schedule->capacity ?? 8) }}" min="1" required>
          <div class="form-text small" id="capacityHint"></div>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ใช้เครดิต', 'Credit cost') }} <span class="text-danger">*</span></label>
          <input class="form-control" type="number" step="0.5" name="credit_cost" id="creditInput"
                 value="{{ old('credit_cost', $schedule->credit_cost ?? 1) }}" min="0" required>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $schedule->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">{{ __t('เปิดใช้งานตารางนี้', 'This schedule is active') }}</label>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
    <a href="{{ route('admin.schedules.index', ['branch' => $schedule->branch_id]) }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
  </div>
</form>

@if($schedule->exists)
  <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="mt-2"
        data-confirm="{{ __t('ปิดตารางนี้? รอบที่ยังไม่มีคนจองจะถูกลบ ส่วนรอบที่มีคนจองแล้วจะยังอยู่', 'Turn off this schedule? Sessions with no bookings are removed; sessions with bookings stay.') }}">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit">
      <i class="bi bi-x-circle"></i> {{ __t('ปิดตารางนี้', 'Turn off') }}
    </button>
  </form>
@endif
@endsection

@push('scripts')
<script>
// กรองห้องตามสาขาที่เลือก
var branchSelect = document.getElementById('branchSelect');
var roomSelect = document.getElementById('roomSelect');

function filterRooms(){
  var branch = branchSelect.value;
  Array.from(roomSelect.options).forEach(function(opt){
    if(!opt.value) return;
    var match = opt.dataset.branch === branch;
    opt.hidden = !match;
    if(!match && opt.selected){ roomSelect.value = ''; }
  });
  updateCapacityHint();
}

// เติมค่าเริ่มต้นจากประเภทคลาสที่เลือก
var classTypeSelect = document.getElementById('classTypeSelect');
classTypeSelect.addEventListener('change', function(){
  var opt = this.selectedOptions[0];
  if(!opt || !opt.value) return;
  document.getElementById('durationInput').value = opt.dataset.duration;
  document.getElementById('capacityInput').value = opt.dataset.capacity;
  document.getElementById('creditInput').value = opt.dataset.credit;
  updateCapacityHint();
});

// เตือนถ้าที่นั่งเกินความจุห้อง
function updateCapacityHint(){
  var hint = document.getElementById('capacityHint');
  var opt = roomSelect.selectedOptions[0];
  var cap = parseInt(document.getElementById('capacityInput').value, 10);
  if(!opt || !opt.value){ hint.textContent = ''; hint.className = 'form-text small'; return; }
  var roomCap = parseInt(opt.dataset.capacity, 10);
  if(cap > roomCap){
    hint.textContent = @json(__t('ที่นั่งเกินความจุห้อง', 'Seats exceed room capacity')) + ' (' + roomCap + ')';
    hint.className = 'form-text small text-danger';
  } else {
    hint.textContent = @json(__t('ความจุห้อง', 'Room capacity')) + ' ' + roomCap;
    hint.className = 'form-text small';
  }
}

branchSelect.addEventListener('change', filterRooms);
roomSelect.addEventListener('change', updateCapacityHint);
document.getElementById('capacityInput').addEventListener('input', updateCapacityHint);
filterRooms();
</script>
@endpush

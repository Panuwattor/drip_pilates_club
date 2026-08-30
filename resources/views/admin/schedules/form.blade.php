@extends('admin.layouts.app')
@section('title', $schedule->exists ? 'แก้ไขคลาสประจำ' : 'เพิ่มคลาสประจำสัปดาห์')

@section('content')
<form method="POST" action="{{ $schedule->exists ? route('admin.schedules.update', $schedule) : route('admin.schedules.store') }}">
  @csrf
  @if($schedule->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">คลาสนี้จัดที่ไหน เมื่อไหร่</div>

        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <label class="form-label">สาขา <span class="text-danger">*</span></label>
            <select class="form-select" name="branch_id" id="branchSelect" required>
              <option value="">— เลือกสาขา —</option>
              @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected(old('branch_id', $schedule->branch_id) == $b->id)>{{ $b->name_th }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">ห้อง</label>
            <select class="form-select" name="room_id" id="roomSelect">
              <option value="">— ไม่ระบุ —</option>
              @foreach($rooms as $r)
                <option value="{{ $r->id }}" data-branch="{{ $r->branch_id }}" data-capacity="{{ $r->capacity }}"
                        @selected(old('room_id', $schedule->room_id) == $r->id)>
                  {{ $r->name_th }} ({{ $r->capacity }} ที่)
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">วันในสัปดาห์ <span class="text-danger">*</span></label>
            <select class="form-select" name="day_of_week" required>
              @foreach($days as $v => $l)
                <option value="{{ $v }}" @selected((string) old('day_of_week', $schedule->day_of_week) === (string) $v)>{{ $l }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">เวลาเริ่ม <span class="text-danger">*</span></label>
            <input class="form-control" type="time" name="start_time"
                   value="{{ old('start_time', substr($schedule->start_time ?? '08:00:00', 0, 5)) }}" required>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">ประเภทคลาส <span class="text-danger">*</span></label>
            <select class="form-select" name="class_type_id" id="classTypeSelect" required>
              <option value="">— เลือกคลาส —</option>
              @foreach($classTypes as $ct)
                <option value="{{ $ct->id }}"
                        data-duration="{{ $ct->duration_min }}"
                        data-capacity="{{ $ct->default_capacity }}"
                        data-credit="{{ $ct->credit_cost }}"
                        @selected(old('class_type_id', $schedule->class_type_id) == $ct->id)>{{ $ct->name_th }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-2">
            <label class="form-label">ครูผู้สอน</label>
            <select class="form-select" name="trainer_id">
              <option value="">— ยังไม่กำหนด —</option>
              @foreach($trainers as $t)
                <option value="{{ $t->id }}" @selected(old('trainer_id', $schedule->trainer_id) == $t->id)>
                  {{ $t->name_th }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">ช่วงเวลาที่ใช้ตารางนี้</div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">เริ่มใช้ตั้งแต่ <span class="text-danger">*</span></label>
            <input class="form-control" type="date" name="effective_from"
                   value="{{ old('effective_from', optional($schedule->effective_from)->toDateString() ?? now()->toDateString()) }}" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">ใช้ถึงวันที่</label>
            <input class="form-control" type="date" name="effective_until"
                   value="{{ old('effective_until', optional($schedule->effective_until)->toDateString()) }}">
            <div class="form-text small">เว้นว่าง = ใช้ไปเรื่อยๆ</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">ที่นั่งและเครดิต</div>

        <div class="mb-2">
          <label class="form-label">ระยะเวลา <span class="text-danger">*</span></label>
          <div class="input-group">
            <input class="form-control" type="number" name="duration_min" id="durationInput"
                   value="{{ old('duration_min', $schedule->duration_min ?? 50) }}" min="15" required>
            <span class="input-group-text">นาที</span>
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">จำนวนที่นั่ง <span class="text-danger">*</span></label>
          <input class="form-control" type="number" name="capacity" id="capacityInput"
                 value="{{ old('capacity', $schedule->capacity ?? 8) }}" min="1" required>
          <div class="form-text small" id="capacityHint"></div>
        </div>

        <div class="mb-2">
          <label class="form-label">ใช้เครดิต <span class="text-danger">*</span></label>
          <input class="form-control" type="number" step="0.5" name="credit_cost" id="creditInput"
                 value="{{ old('credit_cost', $schedule->credit_cost ?? 1) }}" min="0" required>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $schedule->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">เปิดใช้งานตารางนี้</label>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> บันทึก</button>
    <a href="{{ route('admin.schedules.index', ['branch' => $schedule->branch_id]) }}" class="btn btn-outline-secondary">ยกเลิก</a>
  </div>
</form>

@if($schedule->exists)
  <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="mt-2"
        data-confirm="ปิดตารางนี้? รอบที่ยังไม่มีคนจองจะถูกลบ ส่วนรอบที่มีคนจองแล้วจะยังอยู่">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit">
      <i class="bi bi-x-circle"></i> ปิดตารางนี้
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
    hint.textContent = 'ที่นั่งเกินความจุห้อง (' + roomCap + ' ที่)';
    hint.className = 'form-text small text-danger';
  } else {
    hint.textContent = 'ความจุห้อง ' + roomCap + ' ที่';
    hint.className = 'form-text small';
  }
}

branchSelect.addEventListener('change', filterRooms);
roomSelect.addEventListener('change', updateCapacityHint);
document.getElementById('capacityInput').addEventListener('input', updateCapacityHint);
filterRooms();
</script>
@endpush

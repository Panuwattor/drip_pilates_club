@extends('admin.layouts.app')
@section('title', $branch->exists ? 'แก้ไขสาขา · ' . $branch->name_th : 'เพิ่มสาขา')

@section('content')
<div class="row g-3">
  <div class="col-lg-7">
    <form method="POST" action="{{ $branch->exists ? route('admin.branches.update', $branch) : route('admin.branches.store') }}">
      @csrf
      @if($branch->exists) @method('PUT') @endif

      <div class="card-panel mb-3">
        <div class="ttl">ข้อมูลสาขา</div>

        <div class="mb-3">
          <label class="form-label">รหัสสาขา <span class="text-danger">*</span></label>
          <input class="form-control" name="code" value="{{ old('code', $branch->code) }}"
                 placeholder="เช่น aree" required>
          <div class="form-text small">ใช้ในลิงก์และระบบภายใน ตัวอักษรอังกฤษและขีดกลางเท่านั้น</div>
        </div>

        @include('admin.partials.bilingual-field', [
          'name' => 'name', 'label' => 'ชื่อสาขา', 'model' => $branch, 'required' => true,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'short_name', 'label' => 'ชื่อย่อ (ใช้บนปุ่มสลับสาขา)', 'model' => $branch,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'address', 'label' => 'ที่อยู่', 'model' => $branch, 'type' => 'textarea', 'rows' => 3,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'direction', 'label' => 'วิธีเดินทาง', 'model' => $branch, 'type' => 'textarea', 'rows' => 2,
        ])
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">ติดต่อและเวลาทำการ</div>

        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <label class="form-label">เบอร์โทร</label>
            <input class="form-control" name="phone" value="{{ old('phone', $branch->phone) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">LINE ID</label>
            <input class="form-control" name="line_id" value="{{ old('line_id', $branch->line_id) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">อีเมล</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $branch->email) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">ลิงก์ Google Maps</label>
            <input class="form-control" name="google_map_url" value="{{ old('google_map_url', $branch->google_map_url) }}">
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">เปิด <span class="text-danger">*</span></label>
            <input class="form-control" type="time" name="open_time"
                   value="{{ old('open_time', substr($branch->open_time ?? '07:00:00', 0, 5)) }}" required>
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">ปิด <span class="text-danger">*</span></label>
            <input class="form-control" type="time" name="close_time"
                   value="{{ old('close_time', substr($branch->close_time ?? '21:00:00', 0, 5)) }}" required>
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">ละติจูด</label>
            <input class="form-control" name="lat" value="{{ old('lat', $branch->lat) }}">
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">ลองจิจูด</label>
            <input class="form-control" name="lng" value="{{ old('lng', $branch->lng) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">ลำดับการแสดง</label>
            <input class="form-control" type="number" name="sort_order" value="{{ old('sort_order', $branch->sort_order ?? 0) }}">
          </div>
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">เปิดใช้งานสาขานี้</label>
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">บัญชีรับชำระเงิน</div>
        <div class="form-text small mb-2">ลูกค้าจะเห็นข้อมูลนี้ตอนโอนเงินและแนบสลิป แต่ละสาขาใช้คนละบัญชีได้</div>

        <div class="row g-2">
          <div class="col-md-4 mb-2">
            <label class="form-label">ธนาคาร</label>
            <input class="form-control" name="bank_name" value="{{ old('bank_name', $branch->bank_name) }}"
                   placeholder="เช่น SCB">
          </div>
          <div class="col-md-8 mb-2">
            <label class="form-label">ชื่อบัญชี</label>
            <input class="form-control" name="bank_account_name" value="{{ old('bank_account_name', $branch->bank_account_name) }}"
                   placeholder="เช่น DRIP Pilates and Wellness club">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">เลขที่บัญชี</label>
            <input class="form-control" name="bank_account_number" value="{{ old('bank_account_number', $branch->bank_account_number) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">พร้อมเพย์</label>
            <input class="form-control" name="promptpay_id" value="{{ old('promptpay_id', $branch->promptpay_id) }}"
                   placeholder="เบอร์โทรหรือเลขประจำตัวผู้เสียภาษี">
          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> บันทึก</button>
        <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>

        @if($branch->exists)
          <span class="ms-auto"></span>
        @endif
      </div>
    </form>

    @if($branch->exists)
      <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="mt-2"
            data-confirm="ยืนยันลบสาขานี้?">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> ลบสาขา</button>
      </form>
    @endif
  </div>

  <div class="col-lg-5">
    @if($branch->exists)
      <div class="card-panel">
        <div class="ttl">ห้องในสาขานี้</div>

        @forelse($branch->rooms as $room)
          <form method="POST" action="{{ route('admin.rooms.update', $room) }}"
                class="border rounded-3 p-2 mb-2" style="border-color:var(--line) !important;">
            @csrf @method('PUT')
            <div class="row g-2">
              <div class="col-6">
                <input class="form-control form-control-sm" name="name_th" value="{{ $room->name_th }}" placeholder="ชื่อห้อง (ไทย)" required>
              </div>
              <div class="col-6">
                <input class="form-control form-control-sm" name="name_en" value="{{ $room->name_en }}" placeholder="Room name (EN)" required>
              </div>
              <div class="col-4">
                <div class="input-group input-group-sm">
                  <input class="form-control" type="number" name="capacity" value="{{ $room->capacity }}" min="1" required>
                  <span class="input-group-text">ที่</span>
                </div>
              </div>
              <div class="col-5">
                <select class="form-select form-select-sm" name="equipment_type">
                  @foreach(['reformer' => 'Reformer', 'mat' => 'Mat', 'cadillac' => 'Cadillac', 'chair' => 'Chair', 'mixed' => 'ผสม'] as $v => $l)
                    <option value="{{ $v }}" @selected($room->equipment_type === $v)>{{ $l }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-3 d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary flex-fill" type="submit" title="บันทึก">
                  <i class="bi bi-check-lg"></i>
                </button>
              </div>
              <div class="col-12">
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="room_active_{{ $room->id }}"
                         name="is_active" value="1" {{ $room->is_active ? 'checked' : '' }}>
                  <label class="form-check-label small" for="room_active_{{ $room->id }}">เปิดใช้งาน</label>
                </div>
              </div>
            </div>
          </form>

          <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" class="mb-3"
                data-confirm="ยืนยันลบห้อง {{ $room->name_th }}?">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-link text-danger p-0 small" type="submit">ลบห้องนี้</button>
          </form>
        @empty
          <div class="empty-note mb-3"><i class="bi bi-door-open"></i>ยังไม่มีห้องในสาขานี้</div>
        @endforelse

        <hr style="border-color:var(--line);">

        <form method="POST" action="{{ route('admin.branches.rooms.store', $branch) }}">
          @csrf
          <div class="ttl">เพิ่มห้องใหม่</div>
          <div class="row g-2">
            <div class="col-6">
              <input class="form-control form-control-sm" name="name_th" placeholder="ชื่อห้อง (ไทย)" required>
            </div>
            <div class="col-6">
              <input class="form-control form-control-sm" name="name_en" placeholder="Room name (EN)" required>
            </div>
            <div class="col-4">
              <div class="input-group input-group-sm">
                <input class="form-control" type="number" name="capacity" value="8" min="1" required>
                <span class="input-group-text">ที่</span>
              </div>
            </div>
            <div class="col-5">
              <select class="form-select form-select-sm" name="equipment_type">
                <option value="reformer">Reformer</option>
                <option value="mat">Mat</option>
                <option value="cadillac">Cadillac</option>
                <option value="chair">Chair</option>
                <option value="mixed" selected>ผสม</option>
              </select>
            </div>
            <div class="col-3">
              <button class="btn btn-sm btn-primary w-100" type="submit"><i class="bi bi-plus-lg"></i></button>
            </div>
          </div>
        </form>
      </div>
    @else
      <div class="card-panel">
        <div class="empty-note"><i class="bi bi-info-circle"></i>บันทึกสาขาก่อน แล้วจึงเพิ่มห้องได้</div>
      </div>
    @endif
  </div>
</div>
@endsection

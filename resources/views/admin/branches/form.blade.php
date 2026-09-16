@extends('admin.layouts.app')
@section('title', $branch->exists ? __t('แก้ไขสาขา', 'Edit branch') . ' · ' . $branch->name : __t('เพิ่มสาขา', 'Add branch'))

@section('content')
<div class="row g-3">
  <div class="col-lg-7">
    <form method="POST" action="{{ $branch->exists ? route('admin.branches.update', $branch) : route('admin.branches.store') }}">
      @csrf
      @if($branch->exists) @method('PUT') @endif

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ข้อมูลสาขา', 'Branch details') }}</div>

        <div class="mb-3">
          <label class="form-label">{{ __t('รหัสสาขา', 'Branch code') }} <span class="text-danger">*</span></label>
          <input class="form-control" name="code" value="{{ old('code', $branch->code) }}"
                 placeholder="{{ __t('เช่น aree', 'e.g. aree') }}" required>
          <div class="form-text small">{{ __t('ใช้ในลิงก์และระบบภายใน ตัวอักษรอังกฤษและขีดกลางเท่านั้น', 'Used in URLs and internally — lowercase letters and hyphens only') }}</div>
        </div>

        @include('admin.partials.bilingual-field', [
          'name' => 'name', 'label' => __t('ชื่อสาขา', 'Branch name'), 'model' => $branch, 'required' => true,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'short_name', 'label' => __t('ชื่อย่อ (ใช้บนปุ่มสลับสาขา)', 'Short name (used on the branch switcher)'), 'model' => $branch,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'address', 'label' => __t('ที่อยู่', 'Address'), 'model' => $branch, 'type' => 'textarea', 'rows' => 3,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'direction', 'label' => __t('วิธีเดินทาง', 'Getting there'), 'model' => $branch, 'type' => 'textarea', 'rows' => 2,
        ])
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ติดต่อและเวลาทำการ', 'Contact & opening hours') }}</div>

        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('เบอร์โทร', 'Phone') }}</label>
            <input class="form-control" name="phone" value="{{ old('phone', $branch->phone) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">LINE ID</label>
            <input class="form-control" name="line_id" value="{{ old('line_id', $branch->line_id) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('อีเมล', 'Email') }}</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $branch->email) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('ลิงก์ Google Maps', 'Google Maps link') }}</label>
            <input class="form-control" name="google_map_url" value="{{ old('google_map_url', $branch->google_map_url) }}">
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">{{ __t('เปิด', 'Opens') }} <span class="text-danger">*</span></label>
            <input class="form-control" type="time" name="open_time"
                   value="{{ old('open_time', substr($branch->open_time ?? '07:00:00', 0, 5)) }}" required>
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">{{ __t('ปิด', 'Closes') }} <span class="text-danger">*</span></label>
            <input class="form-control" type="time" name="close_time"
                   value="{{ old('close_time', substr($branch->close_time ?? '21:00:00', 0, 5)) }}" required>
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">{{ __t('ละติจูด', 'Latitude') }}</label>
            <input class="form-control" name="lat" value="{{ old('lat', $branch->lat) }}">
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">{{ __t('ลองจิจูด', 'Longitude') }}</label>
            <input class="form-control" name="lng" value="{{ old('lng', $branch->lng) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('ลำดับการแสดง', 'Display order') }}</label>
            <input class="form-control" type="number" name="sort_order" value="{{ old('sort_order', $branch->sort_order ?? 0) }}">
          </div>
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">{{ __t('เปิดใช้งานสาขานี้', 'This branch is active') }}</label>
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('บัญชีรับชำระเงิน', 'Payment account') }}</div>
        <div class="form-text small mb-2">{{ __t('ลูกค้าจะเห็นข้อมูลนี้ตอนโอนเงินและแนบสลิป แต่ละสาขาใช้คนละบัญชีได้', 'Customers see this when transferring and uploading a slip. Each branch can use its own account.') }}</div>

        <div class="row g-2">
          <div class="col-md-4 mb-2">
            <label class="form-label">{{ __t('ธนาคาร', 'Bank') }}</label>
            <input class="form-control" name="bank_name" value="{{ old('bank_name', $branch->bank_name) }}"
                   placeholder="{{ __t('เช่น SCB', 'e.g. SCB') }}">
          </div>
          <div class="col-md-8 mb-2">
            <label class="form-label">{{ __t('ชื่อบัญชี', 'Account name') }}</label>
            <input class="form-control" name="bank_account_name" value="{{ old('bank_account_name', $branch->bank_account_name) }}"
                   placeholder="{{ __t('เช่น DRIP Pilates and Wellness club', 'e.g. DRIP Pilates and Wellness club') }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('เลขที่บัญชี', 'Account number') }}</label>
            <input class="form-control" name="bank_account_number" value="{{ old('bank_account_number', $branch->bank_account_number) }}">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __t('พร้อมเพย์', 'PromptPay') }}</label>
            <input class="form-control" name="promptpay_id" value="{{ old('promptpay_id', $branch->promptpay_id) }}"
                   placeholder="{{ __t('เบอร์โทรหรือเลขประจำตัวผู้เสียภาษี', 'Phone number or tax ID') }}">
          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
        <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>

        @if($branch->exists)
          <span class="ms-auto"></span>
        @endif
      </div>
    </form>

    @if($branch->exists)
      <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="mt-2"
            data-confirm="{{ __t('ยืนยันลบสาขานี้?', 'Delete this branch?') }}">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> {{ __t('ลบสาขา', 'Delete branch') }}</button>
      </form>
    @endif
  </div>

  <div class="col-lg-5">
    @if($branch->exists)
      <div class="card-panel">
        <div class="ttl">{{ __t('ห้องในสาขานี้', 'Rooms at this branch') }}</div>

        @forelse($branch->rooms as $room)
          <form method="POST" action="{{ route('admin.rooms.update', $room) }}"
                class="border rounded-3 p-2 mb-2" style="border-color:var(--line) !important;">
            @csrf @method('PUT')
            <div class="row g-2">
              <div class="col-6">
                <input class="form-control form-control-sm" name="name_th" value="{{ $room->name_th }}" placeholder="{{ __t('ชื่อห้อง (ไทย)', 'Room name (Thai)') }}" required>
              </div>
              <div class="col-6">
                <input class="form-control form-control-sm" name="name_en" value="{{ $room->name_en }}" placeholder="Room name (EN)" required>
              </div>
              <div class="col-4">
                <div class="input-group input-group-sm">
                  <input class="form-control" type="number" name="capacity" value="{{ $room->capacity }}" min="1" required>
                  <span class="input-group-text">{{ __t('ที่', 'seats') }}</span>
                </div>
              </div>
              <div class="col-5">
                <select class="form-select form-select-sm" name="equipment_type">
                  @foreach(['reformer' => 'Reformer', 'mat' => 'Mat', 'cadillac' => 'Cadillac', 'chair' => 'Chair', 'mixed' => __t('ผสม', 'Mixed')] as $v => $l)
                    <option value="{{ $v }}" @selected($room->equipment_type === $v)>{{ $l }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-3 d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary flex-fill" type="submit" title="{{ __t('บันทึก', 'Save') }}">
                  <i class="bi bi-check-lg"></i>
                </button>
              </div>
              <div class="col-12">
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="room_active_{{ $room->id }}"
                         name="is_active" value="1" {{ $room->is_active ? 'checked' : '' }}>
                  <label class="form-check-label small" for="room_active_{{ $room->id }}">{{ __t('เปิดใช้งาน', 'Active') }}</label>
                </div>
              </div>
            </div>
          </form>

          <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" class="mb-3"
                data-confirm="{{ __t('ยืนยันลบห้อง', 'Delete room') }} {{ $room->name }}?">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-link text-danger p-0 small" type="submit">{{ __t('ลบห้องนี้', 'Delete this room') }}</button>
          </form>
        @empty
          <div class="empty-note mb-3"><i class="bi bi-door-open"></i>{{ __t('ยังไม่มีห้องในสาขานี้', 'No rooms at this branch yet') }}</div>
        @endforelse

        <hr style="border-color:var(--line);">

        <form method="POST" action="{{ route('admin.branches.rooms.store', $branch) }}">
          @csrf
          <div class="ttl">{{ __t('เพิ่มห้องใหม่', 'Add a room') }}</div>
          <div class="row g-2">
            <div class="col-6">
              <input class="form-control form-control-sm" name="name_th" placeholder="{{ __t('ชื่อห้อง (ไทย)', 'Room name (Thai)') }}" required>
            </div>
            <div class="col-6">
              <input class="form-control form-control-sm" name="name_en" placeholder="Room name (EN)" required>
            </div>
            <div class="col-4">
              <div class="input-group input-group-sm">
                <input class="form-control" type="number" name="capacity" value="8" min="1" required>
                <span class="input-group-text">{{ __t('ที่', 'seats') }}</span>
              </div>
            </div>
            <div class="col-5">
              <select class="form-select form-select-sm" name="equipment_type">
                <option value="reformer">Reformer</option>
                <option value="mat">Mat</option>
                <option value="cadillac">Cadillac</option>
                <option value="chair">Chair</option>
                <option value="mixed" selected>{{ __t('ผสม', 'Mixed') }}</option>
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
        <div class="empty-note"><i class="bi bi-info-circle"></i>{{ __t('บันทึกสาขาก่อน แล้วจึงเพิ่มห้องได้', 'Save the branch first, then add rooms') }}</div>
      </div>
    @endif
  </div>
</div>
@endsection

@extends('admin.layouts.app')
@section('title', $announcement->exists ? 'แก้ไขบทความ/ประกาศ' : 'เพิ่มบทความ/ประกาศ')

@section('topbar-actions')
  @if($announcement->exists)
    <a href="{{ route('articles.show', $announcement) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-eye"></i> ดูหน้าที่ลูกค้าเห็น
    </a>
  @endif
@endsection

@section('content')
<form method="POST" enctype="multipart/form-data"
      action="{{ $announcement->exists ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}">
  @csrf
  @if($announcement->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">เนื้อหาประกาศ</div>

        @include('admin.partials.bilingual-field', [
          'name' => 'title', 'label' => 'หัวข้อ', 'model' => $announcement, 'required' => true,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'body', 'label' => 'รายละเอียด', 'model' => $announcement, 'type' => 'textarea', 'rows' => 4,
        ])

        <div class="mb-2">
          <label class="form-label">ลิงก์ภายนอกเพิ่มเติม (ถ้ามี)</label>
          <input class="form-control" name="link_url" value="{{ old('link_url', $announcement->link_url) }}"
                 placeholder="https://">
          <div class="form-text">แสดงเป็นปุ่ม "ดูเพิ่มเติม" ในหน้าบทความ นอกเหนือจากหน้ารายละเอียดที่ระบบสร้างให้อัตโนมัติ</div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">รูปภาพ</div>
        @if($announcement->image)
          <img src="{{ asset($announcement->image) }}" alt=""
               style="width:100%;max-height:160px;object-fit:cover;border-radius:12px;margin-bottom:.6rem;">
        @endif
        <input class="form-control form-control-sm" type="file" name="image_file" accept="image/*">
        <div class="form-text small">ไฟล์ภาพไม่เกิน 2 MB แสดงเป็นภาพหน้าปกในหน้าบทความ</div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">การแสดงผล</div>

        <div class="mb-2">
          <label class="form-label">ชนิด <span class="text-danger">*</span></label>
          <select class="form-select" name="type" required>
            @foreach(['info'=>'ข้อมูลทั่วไป','promo'=>'โปรโมชัน','warning'=>'แจ้งเตือนสำคัญ'] as $v => $l)
              <option value="{{ $v }}" @selected(old('type', $announcement->type) === $v)>{{ $l }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">แสดงที่สาขา</label>
          <select class="form-select" name="branch_id">
            <option value="">ทุกสาขา</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}" @selected(old('branch_id', $announcement->branch_id) == $b->id)>{{ $b->name_th }}</option>
            @endforeach
          </select>
        </div>

        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">เริ่มแสดง</label>
            <input class="form-control" type="datetime-local" name="starts_at"
                   value="{{ old('starts_at', $announcement->starts_at?->format('Y-m-d\TH:i')) }}">
          </div>
          <div class="col-6">
            <label class="form-label">สิ้นสุด</label>
            <input class="form-control" type="datetime-local" name="ends_at"
                   value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d\TH:i')) }}">
          </div>
          <div class="col-6">
            <label class="form-label">ลำดับ</label>
            <input class="form-control" type="number" name="sort_order"
                   value="{{ old('sort_order', $announcement->sort_order ?? 0) }}">
          </div>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $announcement->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">เปิดแสดง</label>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> บันทึก</button>
    <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
  </div>
</form>

@if($announcement->exists)
  <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="mt-2"
        data-confirm="ยืนยันลบประกาศนี้?">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> ลบ</button>
  </form>
@endif
@endsection

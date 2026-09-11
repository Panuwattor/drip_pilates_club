@extends('admin.layouts.app')
@section('title', $video->exists ? 'แก้ไขคลิป' : 'เพิ่มคลิป')

@section('topbar-actions')
  <a href="{{ url('/') }}#videos" target="_blank" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-eye"></i> ดูหน้าที่ลูกค้าเห็น
  </a>
@endsection

@section('content')
<form method="POST" enctype="multipart/form-data"
      action="{{ $video->exists ? route('admin.videos.update', $video) : route('admin.videos.store') }}">
  @csrf
  @if($video->exists) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card-panel mb-3">
        <div class="ttl">ลิงก์คลิป</div>

        <div class="mb-2">
          <label class="form-label">URL คลิป <span class="text-danger">*</span></label>
          <input class="form-control" name="url" required
                 value="{{ old('url', $video->url) }}"
                 placeholder="https://www.instagram.com/reel/...">
          <div class="form-text">
            วางลิงก์คลิปจาก Instagram, YouTube, TikTok หรือ Facebook — ระบบจะตรวจจับแพลตฟอร์มให้อัตโนมัติ
            แล้วสร้างตัวเล่นแบบทางการที่ปลอดภัย (ไม่ต้องวางโค้ด embed เอง)
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">แพลตฟอร์ม</label>
          <select class="form-select" name="provider">
            <option value="">— ตรวจจับอัตโนมัติจากลิงก์ —</option>
            @foreach(['instagram'=>'Instagram','youtube'=>'YouTube','tiktok'=>'TikTok','facebook'=>'Facebook'] as $v => $l)
              <option value="{{ $v }}" @selected(old('provider', $video->provider) === $v)>{{ $l }}</option>
            @endforeach
          </select>
          <div class="form-text small">เลือกเองเฉพาะกรณีที่ระบบตรวจจับผิด</div>
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">ข้อความประกอบ (ถ้ามี)</div>

        @include('admin.partials.bilingual-field', [
          'name' => 'title', 'label' => 'หัวข้อ', 'model' => $video,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'caption', 'label' => 'คำบรรยายสั้น', 'model' => $video, 'type' => 'textarea',
        ])
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">รูปปก <span class="text-secondary small">(แนะนำให้ใส่)</span></div>
        @php $currentPoster = $video->thumbnail ? asset($video->thumbnail) : $video->remote_thumbnail; @endphp
        @if($currentPoster)
          <img src="{{ $currentPoster }}" alt=""
               style="width:100%;max-height:200px;object-fit:cover;border-radius:12px;margin-bottom:.6rem;">
        @endif
        <input class="form-control form-control-sm" type="file" name="thumbnail_file" accept="image/*">
        <div class="form-text small">
          ไฟล์ภาพไม่เกิน 2 MB ใช้เป็นภาพหน้าปกในการ์ด (สัดส่วนแนวตั้ง 9:16 สวยที่สุด)<br>
          <i class="bi bi-info-circle"></i> Instagram/TikTok ไม่อนุญาตให้ดึงรูปปกอัตโนมัติ — ควรอัปรูปปกเองเพื่อให้การ์ดสวยเป๊ะ ถ้าไม่ใส่จะแสดงพื้นหลังโลโก้แทน
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">การแสดงผล</div>

        <div class="mb-2">
          <label class="form-label">ลำดับ</label>
          <input class="form-control" type="number" name="sort_order"
                 value="{{ old('sort_order', $video->sort_order ?? 0) }}">
          <div class="form-text small">เลขน้อยแสดงก่อน</div>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $video->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">เปิดแสดงหน้าแรก</label>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> บันทึก</button>
    <a href="{{ route('admin.videos.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
  </div>
</form>

@if($video->exists)
  <form method="POST" action="{{ route('admin.videos.destroy', $video) }}" class="mt-2"
        data-confirm="ยืนยันลบคลิปนี้?">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> ลบ</button>
  </form>
@endif
@endsection

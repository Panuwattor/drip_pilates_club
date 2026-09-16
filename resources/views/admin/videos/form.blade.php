@extends('admin.layouts.app')
@section('title', $video->exists ? __t('แก้ไขคลิป', 'Edit video') : __t('เพิ่มคลิป', 'Add video'))

@section('topbar-actions')
  <a href="{{ url('/') }}#videos" target="_blank" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-eye"></i> {{ __t('ดูหน้าที่ลูกค้าเห็น', 'View public page') }}
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
        <div class="ttl">{{ __t('ลิงก์คลิป', 'Video link') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('URL คลิป', 'Video URL') }} <span class="text-danger">*</span></label>
          <input class="form-control" name="url" required
                 value="{{ old('url', $video->url) }}"
                 placeholder="https://www.instagram.com/reel/...">
          <div class="form-text">
            {{ __t('วางลิงก์คลิปจาก Instagram, YouTube, TikTok หรือ Facebook — ระบบจะตรวจจับแพลตฟอร์มให้อัตโนมัติ แล้วสร้างตัวเล่นแบบทางการที่ปลอดภัย (ไม่ต้องวางโค้ด embed เอง)', 'Paste a link from Instagram, YouTube, TikTok or Facebook — the platform is detected automatically and a safe official player is built for you (no embed code needed).') }}
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('แพลตฟอร์ม', 'Platform') }}</label>
          <select class="form-select" name="provider">
            <option value="">— {{ __t('ตรวจจับอัตโนมัติจากลิงก์', 'Detect automatically from the link') }} —</option>
            @foreach(['instagram'=>'Instagram','youtube'=>'YouTube','tiktok'=>'TikTok','facebook'=>'Facebook'] as $v => $l)
              <option value="{{ $v }}" @selected(old('provider', $video->provider) === $v)>{{ $l }}</option>
            @endforeach
          </select>
          <div class="form-text small">{{ __t('เลือกเองเฉพาะกรณีที่ระบบตรวจจับผิด', 'Only set this if detection gets it wrong') }}</div>
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('ข้อความประกอบ (ถ้ามี)', 'Text (optional)') }}</div>

        @include('admin.partials.bilingual-field', [
          'name' => 'title', 'label' => __t('หัวข้อ', 'Title'), 'model' => $video,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'caption', 'label' => __t('คำบรรยายสั้น', 'Short caption'), 'model' => $video, 'type' => 'textarea',
        ])
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('รูปปก', 'Cover image') }} <span class="text-secondary small">({{ __t('แนะนำให้ใส่', 'recommended') }})</span></div>
        @php $currentPoster = $video->thumbnail ? asset($video->thumbnail) : $video->remote_thumbnail; @endphp
        @if($currentPoster)
          <img src="{{ $currentPoster }}" alt=""
               style="width:100%;max-height:200px;object-fit:cover;border-radius:12px;margin-bottom:.6rem;">
        @endif
        <input class="form-control form-control-sm" type="file" name="thumbnail_file" accept="image/*">
        <div class="form-text small">
          {{ __t('ไฟล์ภาพไม่เกิน 2 MB ใช้เป็นภาพหน้าปกในการ์ด (สัดส่วนแนวตั้ง 9:16 สวยที่สุด)', 'Image up to 2 MB, used as the card cover (9:16 portrait looks best)') }}<br>
          <i class="bi bi-info-circle"></i> {{ __t('Instagram/TikTok ไม่อนุญาตให้ดึงรูปปกอัตโนมัติ — ควรอัปรูปปกเองเพื่อให้การ์ดสวยเป๊ะ ถ้าไม่ใส่จะแสดงพื้นหลังโลโก้แทน', 'Instagram/TikTok block automatic cover fetching — upload one yourself for a clean card. Without it the logo background is shown.') }}
        </div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('การแสดงผล', 'Display') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ลำดับ', 'Order') }}</label>
          <input class="form-control" type="number" name="sort_order"
                 value="{{ old('sort_order', $video->sort_order ?? 0) }}">
          <div class="form-text small">{{ __t('เลขน้อยแสดงก่อน', 'Lower numbers appear first') }}</div>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $video->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">{{ __t('เปิดแสดงหน้าแรก', 'Show on landing page') }}</label>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
    <a href="{{ route('admin.videos.index') }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
  </div>
</form>

@if($video->exists)
  <form method="POST" action="{{ route('admin.videos.destroy', $video) }}" class="mt-2"
        data-confirm="{{ __t('ยืนยันลบคลิปนี้?', 'Delete this video?') }}">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> {{ __t('ลบ', 'Delete') }}</button>
  </form>
@endif
@endsection

@extends('admin.layouts.app')
@section('title', $announcement->exists ? __t('แก้ไขบทความ/ประกาศ', 'Edit article') : __t('เพิ่มบทความ/ประกาศ', 'Add article'))

@section('topbar-actions')
  @if($announcement->exists)
    <a href="{{ route('articles.show', $announcement) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-eye"></i> {{ __t('ดูหน้าที่ลูกค้าเห็น', 'View public page') }}
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
        <div class="ttl">{{ __t('เนื้อหาประกาศ', 'Article content') }}</div>

        @include('admin.partials.bilingual-field', [
          'name' => 'title', 'label' => __t('หัวข้อ', 'Title'), 'model' => $announcement, 'required' => true,
        ])

        @include('admin.partials.bilingual-field', [
          'name' => 'body', 'label' => __t('รายละเอียด', 'Body'), 'model' => $announcement, 'type' => 'richtext',
        ])

        <div class="mb-2">
          <label class="form-label">{{ __t('ลิงก์ภายนอกเพิ่มเติม (ถ้ามี)', 'Extra external link (optional)') }}</label>
          <input class="form-control" name="link_url" value="{{ old('link_url', $announcement->link_url) }}"
                 placeholder="https://">
          <div class="form-text">{{ __t('แสดงเป็นปุ่ม "ดูเพิ่มเติม" ในหน้าบทความ นอกเหนือจากหน้ารายละเอียดที่ระบบสร้างให้อัตโนมัติ', 'Shown as a "Read more" button on the article page, in addition to the detail page generated automatically.') }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('รูปภาพ', 'Image') }}</div>
        @if($announcement->image)
          <img src="{{ asset($announcement->image) }}" alt=""
               style="width:100%;max-height:160px;object-fit:cover;border-radius:12px;margin-bottom:.6rem;">
        @endif
        <input class="form-control form-control-sm" type="file" name="image_file" accept="image/*">
        <div class="form-text small">{{ __t('ไฟล์ภาพไม่เกิน 2 MB แสดงเป็นภาพหน้าปกในหน้าบทความ', 'Image up to 2 MB, used as the article cover') }}</div>
      </div>

      <div class="card-panel mb-3">
        <div class="ttl">{{ __t('การแสดงผล', 'Display') }}</div>

        <div class="mb-2">
          <label class="form-label">{{ __t('ชนิด', 'Type') }} <span class="text-danger">*</span></label>
          <select class="form-select" name="type" required>
            @foreach(['info'=>__t('ข้อมูลทั่วไป','General info'),'promo'=>__t('โปรโมชัน','Promotion'),'warning'=>__t('แจ้งเตือนสำคัญ','Important notice')] as $v => $l)
              <option value="{{ $v }}" @selected(old('type', $announcement->type) === $v)>{{ $l }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">{{ __t('แสดงที่สาขา', 'Show at branch') }}</label>
          <select class="form-select" name="branch_id">
            <option value="">{{ __t('ทุกสาขา', 'All branches') }}</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}" @selected(old('branch_id', $announcement->branch_id) == $b->id)>{{ $b->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">{{ __t('เริ่มแสดง', 'Starts') }}</label>
            <input class="form-control" type="datetime-local" name="starts_at"
                   value="{{ old('starts_at', $announcement->starts_at?->format('Y-m-d\TH:i')) }}">
          </div>
          <div class="col-6">
            <label class="form-label">{{ __t('สิ้นสุด', 'Ends') }}</label>
            <input class="form-control" type="datetime-local" name="ends_at"
                   value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d\TH:i')) }}">
          </div>
          <div class="col-6">
            <label class="form-label">{{ __t('ลำดับ', 'Order') }}</label>
            <input class="form-control" type="number" name="sort_order"
                   value="{{ old('sort_order', $announcement->sort_order ?? 0) }}">
          </div>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $announcement->is_active ?? true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">{{ __t('เปิดแสดง', 'Visible') }}</label>
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" id="show_on_homepage" name="show_on_homepage" value="1"
                 {{ old('show_on_homepage', $announcement->exists ? $announcement->show_on_homepage : true) ? 'checked' : '' }}>
          <label class="form-check-label" for="show_on_homepage">{{ __t('แสดงหน้าแรกสาธารณะ (แนะนำสำหรับ SEO)', 'Show on public homepage (recommended for SEO)') }}</label>
        </div>

        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" id="show_on_customer" name="show_on_customer" value="1"
                 {{ old('show_on_customer', $announcement->show_on_customer ?? false) ? 'checked' : '' }}>
          <label class="form-check-label" for="show_on_customer">{{ __t('แสดงในหน้าลูกค้า', 'Show in customer dashboard') }}</label>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> {{ __t('บันทึก', 'Save') }}</button>
    <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">{{ __t('ยกเลิก', 'Cancel') }}</a>
  </div>
</form>

@if($announcement->exists)
  <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="mt-2"
        data-confirm="{{ __t('ยืนยันลบประกาศนี้?', 'Delete this article?') }}">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> {{ __t('ลบ', 'Delete') }}</button>
  </form>
@endif
@endsection

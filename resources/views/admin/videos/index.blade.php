@extends('admin.layouts.app')
@section('title', __t('คลิปวิดีโอ', 'Videos'))

@section('topbar-actions')
  <a href="{{ route('admin.videos.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> {{ __t('เพิ่มคลิป', 'Add video') }}
  </a>
@endsection

@section('content')
<div class="card-panel">
  <p class="text-secondary small mb-3">
    {{ __t('คลิปที่เปิดแสดงจะไปโผล่ที่หน้าแรก (landing) ในส่วน "วิดีโอ" — วางแค่ลิงก์คลิปจาก Instagram / YouTube / TikTok / Facebook ระบบสร้างตัวเล่นให้อัตโนมัติ', 'Active videos appear in the Videos section of the landing page — just paste an Instagram / YouTube / TikTok / Facebook link and the player is built automatically.') }}
  </p>

  @if($videos->isEmpty())
    <div class="empty-note"><i class="bi bi-play-btn"></i>{{ __t('ยังไม่มีคลิป', 'No videos yet') }}</div>
  @else
    <div class="table-wrap">
      <table class="table align-middle">
        <thead><tr><th></th><th>{{ __t('หัวข้อ', 'Title') }}</th><th>{{ __t('แพลตฟอร์ม', 'Platform') }}</th><th>{{ __t('ลิงก์', 'Link') }}</th><th>{{ __t('ลำดับ', 'Order') }}</th><th>{{ __t('สถานะ', 'Status') }}</th><th></th></tr></thead>
        <tbody>
          @php $pm = [
            'instagram'=>['Instagram','bi-instagram'],
            'youtube'=>['YouTube','bi-youtube'],
            'tiktok'=>['TikTok','bi-tiktok'],
            'facebook'=>['Facebook','bi-facebook'],
          ]; @endphp
          @foreach($videos as $v)
            <tr>
              <td>
                @if($v->thumbnail)
                  <img src="{{ asset($v->thumbnail) }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                @else
                  <div class="d-flex align-items-center justify-content-center text-secondary" style="width:48px;height:48px;border-radius:8px;background:var(--ground,#EEF1F6);">
                    <i class="bi {{ $pm[$v->provider][1] ?? 'bi-play-btn' }}"></i>
                  </div>
                @endif
              </td>
              <td>
                <div class="fw-semibold">{{ $v->title ?: __t('(ไม่มีหัวข้อ)', '(untitled)') }}</div>
                @php $altTitle = app()->getLocale() === 'en' ? $v->title_th : $v->title_en; @endphp
                @if($altTitle)<div class="small text-secondary">{{ $altTitle }}</div>@endif
              </td>
              <td><span class="badge-soft"><i class="bi {{ $pm[$v->provider][1] ?? 'bi-play-btn' }}"></i> {{ $pm[$v->provider][0] ?? $v->provider }}</span></td>
              <td class="small text-secondary" style="max-width:220px;">
                <a href="{{ $v->url }}" target="_blank" rel="noopener" class="text-truncate d-inline-block" style="max-width:200px;">{{ $v->url }}</a>
              </td>
              <td class="small text-secondary">{{ $v->sort_order }}</td>
              <td>
                @if($v->is_active)
                  <span class="badge-soft badge-ok">{{ __t('แสดงอยู่', 'Visible') }}</span>
                @else
                  <span class="badge-soft badge-danger">{{ __t('ปิด', 'Off') }}</span>
                @endif
              </td>
              <td class="text-end">
                <a href="{{ route('admin.videos.edit', $v) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-pencil"></i>
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection

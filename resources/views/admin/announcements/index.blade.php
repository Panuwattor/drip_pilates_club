@extends('admin.layouts.app')
@section('title', 'บทความ/ประกาศ')

@section('topbar-actions')
  <a href="{{ route('admin.announcements.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> เพิ่มประกาศ
  </a>
@endsection

@section('content')
<div class="card-panel">
  @if($announcements->isEmpty())
    <div class="empty-note"><i class="bi bi-megaphone"></i>ยังไม่มีประกาศ</div>
  @else
    <div class="table-wrap">
      <table class="table align-middle">
        <thead><tr><th></th><th>หัวข้อ</th><th>ชนิด</th><th>สาขา</th><th>ช่วงเวลา</th><th>สถานะ</th><th></th></tr></thead>
        <tbody>
          @foreach($announcements as $a)
            <tr>
              <td>
                @if($a->image)
                  <img src="{{ asset($a->image) }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                @else
                  <div class="d-flex align-items-center justify-content-center text-secondary" style="width:48px;height:48px;border-radius:8px;background:var(--ground,#EEF1F6);">
                    <i class="bi bi-image"></i>
                  </div>
                @endif
              </td>
              <td>
                <div class="fw-semibold">{{ $a->title_th }}</div>
                <div class="small text-secondary">{{ $a->title_en }}</div>
              </td>
              <td>
                @php $tm = ['info'=>['ข้อมูล','badge-soft'],'promo'=>['โปรโมชัน','badge-accent'],'warning'=>['แจ้งเตือน','badge-warn']]; @endphp
                <span class="badge-soft {{ $tm[$a->type][1] }}">{{ $tm[$a->type][0] }}</span>
              </td>
              <td class="small text-secondary">{{ $a->branch?->name_th ?? 'ทุกสาขา' }}</td>
              <td class="small text-secondary">
                {{ $a->starts_at?->format('d/m/Y') ?? 'ทันที' }} –
                {{ $a->ends_at?->format('d/m/Y') ?? 'ไม่กำหนด' }}
              </td>
              <td>
                @if($a->is_active)
                  <span class="badge-soft badge-ok">แสดงอยู่</span>
                @else
                  <span class="badge-soft badge-danger">ปิด</span>
                @endif
              </td>
              <td class="text-end">
                <a href="{{ route('articles.show', $a) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="ดูหน้าที่ลูกค้าเห็น">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('admin.announcements.edit', $a) }}" class="btn btn-sm btn-outline-secondary">
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

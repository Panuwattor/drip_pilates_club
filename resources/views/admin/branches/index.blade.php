@extends('admin.layouts.app')
@section('title', 'สาขาและห้อง')

@section('topbar-actions')
  <a href="{{ route('admin.branches.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> เพิ่มสาขา
  </a>
@endsection

@section('content')
<div class="row g-3">
  @foreach($branches as $branch)
    <div class="col-md-6">
      <div class="card-panel h-100">
        <div class="d-flex align-items-start gap-2 mb-2">
          <div>
            <div class="fw-bold" style="font-size:1.05rem;">{{ $branch->name_th }}</div>
            <div class="small text-secondary">{{ $branch->name_en }}</div>
          </div>
          <div class="ms-auto">
            @if($branch->is_active)
              <span class="badge-soft badge-ok">เปิดใช้งาน</span>
            @else
              <span class="badge-soft badge-danger">ปิดใช้งาน</span>
            @endif
          </div>
        </div>

        @if($branch->address_th)
          <div class="small text-secondary mb-2">
            <i class="bi bi-geo-alt"></i> {{ $branch->address_th }}
          </div>
        @endif

        <div class="d-flex gap-3 small text-secondary mb-3">
          <span><i class="bi bi-door-open"></i> {{ $branch->rooms_count }} ห้อง</span>
          <span><i class="bi bi-calendar3"></i> {{ number_format($branch->class_sessions_count) }} รอบ</span>
          <span><i class="bi bi-clock"></i>
            {{ substr($branch->open_time, 0, 5) }}–{{ substr($branch->close_time, 0, 5) }}
          </span>
        </div>

        <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-pencil"></i> แก้ไขและจัดการห้อง
        </a>
      </div>
    </div>
  @endforeach
</div>
@endsection

@extends('admin.layouts.app')
@section('title', 'ประเภทคลาส')

@section('topbar-actions')
  <a href="{{ route('admin.class-types.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> เพิ่มประเภทคลาส
  </a>
@endsection

@section('content')
<div class="card-panel">
  <div class="table-wrap">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>ชื่อคลาส</th><th>ระดับ</th><th>อุปกรณ์</th>
          <th class="text-center">นาที</th><th class="text-center">ที่นั่ง</th>
          <th class="text-center">เครดิต</th><th class="text-center">ใช้ในตาราง</th>
          <th>สถานะ</th><th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($classTypes as $ct)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px;height:26px;border-radius:3px;background:{{ $ct->color ?: 'var(--accent)' }};"></span>
                <div>
                  <div class="fw-semibold">{{ $ct->name_th }}</div>
                  <div class="small text-secondary">{{ $ct->name_en }}</div>
                </div>
              </div>
            </td>
            <td class="small">
              @php $levels = ['all'=>'ทุกระดับ','beginner'=>'เริ่มต้น','intermediate'=>'กลาง','advanced'=>'สูง']; @endphp
              {{ $levels[$ct->level] ?? $ct->level }}
            </td>
            <td class="small text-secondary">{{ ucfirst($ct->equipment_type) }}</td>
            <td class="text-center">{{ $ct->duration_min }}</td>
            <td class="text-center">{{ $ct->default_capacity }}</td>
            <td class="text-center">{{ rtrim(rtrim(number_format($ct->credit_cost, 2), '0'), '.') }}</td>
            <td class="text-center">{{ $ct->schedules_count }}</td>
            <td>
              @if($ct->is_active)
                <span class="badge-soft badge-ok">ใช้งาน</span>
              @else
                <span class="badge-soft badge-danger">ปิด</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('admin.class-types.edit', $ct) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i>
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@extends('admin.layouts.app')
@section('title', __t('ประเภทคลาส', 'Class Types'))

@section('topbar-actions')
  <a href="{{ route('admin.class-types.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> {{ __t('เพิ่มประเภทคลาส', 'Add class type') }}
  </a>
@endsection

@section('content')
<div class="card-panel">
  <div class="table-wrap">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>{{ __t('ชื่อคลาส', 'Class name') }}</th><th>{{ __t('ระดับ', 'Level') }}</th><th>{{ __t('อุปกรณ์', 'Equipment') }}</th>
          <th class="text-center">{{ __t('นาที', 'Min') }}</th><th class="text-center">{{ __t('ที่นั่ง', 'Seats') }}</th>
          <th class="text-center">{{ __t('เครดิต', 'Credits') }}</th><th class="text-center">{{ __t('ใช้ในตาราง', 'In schedule') }}</th>
          <th>{{ __t('สถานะ', 'Status') }}</th><th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($classTypes as $ct)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px;height:26px;border-radius:3px;background:{{ $ct->color ?: 'var(--accent)' }};"></span>
                <div>
                  <div class="fw-semibold">{{ $ct->name }}</div>
                  <div class="small text-secondary">{{ app()->getLocale() === 'en' ? $ct->name_th : $ct->name_en }}</div>
                </div>
              </div>
            </td>
            <td class="small">
              @php $levels = ['all'=>__t('ทุกระดับ','All levels'),'beginner'=>__t('เริ่มต้น','Beginner'),'intermediate'=>__t('กลาง','Intermediate'),'advanced'=>__t('สูง','Advanced')]; @endphp
              {{ $levels[$ct->level] ?? $ct->level }}
            </td>
            <td class="small text-secondary">{{ ucfirst($ct->equipment_type) }}</td>
            <td class="text-center">{{ $ct->duration_min }}</td>
            <td class="text-center">{{ $ct->default_capacity }}</td>
            <td class="text-center">{{ rtrim(rtrim(number_format($ct->credit_cost, 2), '0'), '.') }}</td>
            <td class="text-center">{{ $ct->schedules_count }}</td>
            <td>
              @if($ct->is_active)
                <span class="badge-soft badge-ok">{{ __t('ใช้งาน', 'Active') }}</span>
              @else
                <span class="badge-soft badge-danger">{{ __t('ปิด', 'Off') }}</span>
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

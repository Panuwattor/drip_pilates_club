@extends('admin.layouts.app')
@section('title', __t('ผู้ใช้งานระบบ', 'System Users'))

@section('topbar-actions')
  <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-person-plus"></i> {{ __t('เพิ่มผู้ใช้งาน', 'Add user') }}
  </a>
@endsection

@section('content')
<div class="alert-soft mb-3">
  <i class="bi bi-shield-lock"></i>
  <strong>{{ __t('เจ้าของ', 'Owner') }}</strong> {{ __t('จัดการได้ทุกอย่าง', 'full access') }} ·
  <strong>{{ __t('ผู้จัดการ', 'Manager') }}</strong> {{ __t('เห็นทุกสาขาแต่ตั้งค่าระบบไม่ได้', 'all branches, no system settings') }} ·
  <strong>{{ __t('พนักงาน', 'Staff') }}</strong> {{ __t('เห็นเฉพาะสาขาที่สังกัด', 'own branch only') }}
</div>

<div class="card-panel">
  <div class="table-wrap">
    <table class="table align-middle">
      <thead><tr><th>{{ __t('ชื่อ', 'Name') }}</th><th>{{ __t('อีเมล', 'Email') }}</th><th>{{ __t('สิทธิ์', 'Role') }}</th><th>{{ __t('สาขา', 'Branch') }}</th><th>{{ __t('เข้าล่าสุด', 'Last login') }}</th><th>{{ __t('สถานะ', 'Status') }}</th><th></th></tr></thead>
      <tbody>
        @foreach($users as $u)
          <tr>
            <td class="fw-semibold">
              {{ $u->name }}
              @if($u->id === auth()->id())<span class="badge-soft badge-accent">{{ __t('คุณ', 'You') }}</span>@endif
            </td>
            <td class="small">{{ $u->email }}</td>
            <td>
              @php $rm = ['owner'=>[__t('เจ้าของ','Owner'),'badge-accent'],'manager'=>[__t('ผู้จัดการ','Manager'),'badge-soft'],'staff'=>[__t('พนักงาน','Staff'),'badge-soft']]; @endphp
              <span class="badge-soft {{ $rm[$u->role][1] }}">{{ $rm[$u->role][0] }}</span>
            </td>
            <td class="small text-secondary">{{ $u->branch?->name ?? __t('ทุกสาขา', 'All branches') }}</td>
            <td class="small text-secondary">{{ $u->last_login_at?->format('d/m/Y H:i') ?? __t('ยังไม่เคย', 'Never') }}</td>
            <td>
              @if($u->is_active)
                <span class="badge-soft badge-ok">{{ __t('ใช้งาน', 'Active') }}</span>
              @else
                <span class="badge-soft badge-danger">{{ __t('ระงับ', 'Suspended') }}</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary">
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

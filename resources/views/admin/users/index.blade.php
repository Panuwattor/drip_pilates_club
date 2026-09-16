@extends('admin.layouts.app')
@section('title', 'ผู้ใช้งานระบบ')

@section('topbar-actions')
  <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-person-plus"></i> เพิ่มผู้ใช้งาน
  </a>
@endsection

@section('content')
<div class="alert-soft mb-3">
  <i class="bi bi-shield-lock"></i>
  <strong>เจ้าของ</strong> จัดการได้ทุกอย่าง ·
  <strong>ผู้จัดการ</strong> เห็นทุกสาขาแต่ตั้งค่าระบบไม่ได้ ·
  <strong>พนักงาน</strong> เห็นเฉพาะสาขาที่สังกัด
</div>

<div class="card-panel">
  <div class="table-wrap">
    <table class="table align-middle">
      <thead><tr><th>ชื่อ</th><th>อีเมล</th><th>สิทธิ์</th><th>สาขา</th><th>เข้าล่าสุด</th><th>สถานะ</th><th></th></tr></thead>
      <tbody>
        @foreach($users as $u)
          <tr>
            <td class="fw-semibold">
              {{ $u->name }}
              @if($u->id === auth()->id())<span class="badge-soft badge-accent">คุณ</span>@endif
            </td>
            <td class="small">{{ $u->email }}</td>
            <td>
              @php $rm = ['owner'=>['เจ้าของ','badge-accent'],'manager'=>['ผู้จัดการ','badge-soft'],'staff'=>['พนักงาน','badge-soft']]; @endphp
              <span class="badge-soft {{ $rm[$u->role][1] }}">{{ $rm[$u->role][0] }}</span>
            </td>
            <td class="small text-secondary">{{ $u->branch?->name_th ?? 'ทุกสาขา' }}</td>
            <td class="small text-secondary">{{ $u->last_login_at?->format('d/m/Y H:i') ?? 'ยังไม่เคย' }}</td>
            <td>
              @if($u->is_active)
                <span class="badge-soft badge-ok">ใช้งาน</span>
              @else
                <span class="badge-soft badge-danger">ระงับ</span>
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

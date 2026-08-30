@extends('admin.layouts.app')
@section('title', 'ลูกค้า')

@section('topbar-actions')
  <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-person-plus"></i> เพิ่มลูกค้า
  </a>
@endsection

@section('content')
<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
  <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
         placeholder="ค้นหาชื่อ เบอร์โทร อีเมล หรือรหัสสมาชิก" style="max-width:340px;">
  <select class="form-select form-select-sm" name="status" style="width:auto;">
    <option value="">ทุกสถานะ</option>
    <option value="active" @selected(request('status') === 'active')>ใช้งานอยู่</option>
    <option value="inactive" @selected(request('status') === 'inactive')>ไม่ใช้งาน</option>
    <option value="banned" @selected(request('status') === 'banned')>ถูกระงับ</option>
  </select>
  <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> ค้นหา</button>
  @if(request()->hasAny(['q', 'status']))
    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary">ล้าง</a>
  @endif
</form>

<div class="card-panel">
  @if($customers->isEmpty())
    <div class="empty-note"><i class="bi bi-people"></i>ไม่พบลูกค้า</div>
  @else
    <div class="table-wrap">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>รหัส</th><th>ชื่อ</th><th>เบอร์โทร</th><th>สาขาประจำ</th>
            <th class="text-center">เครดิต</th><th class="text-center">จองค้าง</th>
            <th>สถานะ</th><th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($customers as $c)
            <tr>
              <td class="small text-secondary">{{ $c->code }}</td>
              <td>
                <a href="{{ route('admin.customers.show', $c) }}" class="fw-semibold text-decoration-none" style="color:var(--ink);">
                  {{ $c->full_name }}
                </a>
                @if($c->nickname)<span class="small text-secondary">({{ $c->nickname }})</span>@endif
                @if($c->medical_note)
                  <i class="bi bi-heart-pulse ms-1" style="color:var(--warn);" title="{{ $c->medical_note }}"></i>
                @endif
              </td>
              <td class="small">{{ $c->phone }}</td>
              <td class="small text-secondary">{{ $c->homeBranch?->short_name_th ?? $c->homeBranch?->name_th ?? '—' }}</td>
              <td class="text-center">
                @if($c->hasUnlimited())
                  <span class="badge-soft badge-accent">เหมาจ่าย</span>
                @else
                  <span style="font-variant-numeric:tabular-nums;">{{ $c->totalCredits() }}</span>
                @endif
              </td>
              <td class="text-center">{{ $c->upcoming_count }}</td>
              <td>
                @php $sm = ['active'=>['ใช้งาน','badge-ok'],'inactive'=>['ไม่ใช้งาน','badge-soft'],'banned'=>['ระงับ','badge-danger']]; @endphp
                <span class="badge-soft {{ $sm[$c->status][1] ?? 'badge-soft' }}">{{ $sm[$c->status][0] ?? $c->status }}</span>
              </td>
              <td class="text-end">
                <a href="{{ route('admin.customers.show', $c) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $customers->links() }}</div>
  @endif
</div>
@endsection

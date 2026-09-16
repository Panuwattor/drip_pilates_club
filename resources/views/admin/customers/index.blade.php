@extends('admin.layouts.app')
@section('title', __t('ลูกค้า', 'Customers'))

@section('topbar-actions')
  <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-person-plus"></i> {{ __t('เพิ่มลูกค้า', 'Add customer') }}
  </a>
@endsection

@section('content')
<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
  <input class="form-control form-control-sm" name="q" value="{{ request('q') }}"
         placeholder="{{ __t('ค้นหาชื่อ เบอร์โทร อีเมล หรือรหัสสมาชิก', 'Search name, phone, email or member code') }}" style="max-width:340px;">
  <select class="form-select form-select-sm" name="status" style="width:auto;">
    <option value="">{{ __t('ทุกสถานะ', 'All statuses') }}</option>
    <option value="active" @selected(request('status') === 'active')>{{ __t('ใช้งานอยู่', 'Active') }}</option>
    <option value="inactive" @selected(request('status') === 'inactive')>{{ __t('ไม่ใช้งาน', 'Inactive') }}</option>
    <option value="banned" @selected(request('status') === 'banned')>{{ __t('ถูกระงับ', 'Banned') }}</option>
  </select>
  <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> {{ __t('ค้นหา', 'Search') }}</button>
  @if(request()->hasAny(['q', 'status']))
    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary">{{ __t('ล้าง', 'Clear') }}</a>
  @endif
</form>

<div class="card-panel">
  @if($customers->isEmpty())
    <div class="empty-note"><i class="bi bi-people"></i>{{ __t('ไม่พบลูกค้า', 'No customers found') }}</div>
  @else
    <div class="table-wrap">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>{{ __t('รหัส', 'Code') }}</th><th>{{ __t('ชื่อ', 'Name') }}</th><th>{{ __t('เบอร์โทร', 'Phone') }}</th><th>{{ __t('สาขาประจำ', 'Home branch') }}</th>
            <th class="text-center">{{ __t('เครดิต', 'Credits') }}</th><th class="text-center">{{ __t('จองค้าง', 'Upcoming') }}</th>
            <th>{{ __t('สถานะ', 'Status') }}</th><th></th>
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
              <td class="small text-secondary">{{ $c->homeBranch?->short_name ?? $c->homeBranch?->name ?? '—' }}</td>
              <td class="text-center">
                @if($c->hasUnlimited())
                  <span class="badge-soft badge-accent">{{ __t('เหมาจ่าย', 'Unlimited') }}</span>
                @else
                  <span style="font-variant-numeric:tabular-nums;">{{ $c->totalCredits() }}</span>
                @endif
              </td>
              <td class="text-center">{{ $c->upcoming_count }}</td>
              <td>
                @php $sm = ['active'=>[__t('ใช้งาน','Active'),'badge-ok'],'inactive'=>[__t('ไม่ใช้งาน','Inactive'),'badge-soft'],'banned'=>[__t('ระงับ','Banned'),'badge-danger']]; @endphp
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

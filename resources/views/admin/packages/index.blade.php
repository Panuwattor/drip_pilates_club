@extends('admin.layouts.app')
@section('title', __t('แพ็กเกจ', 'Packages'))

@section('topbar-actions')
  <a href="{{ route('admin.packages.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> {{ __t('เพิ่มแพ็กเกจ', 'Add package') }}
  </a>
@endsection

@section('content')
<div class="alert-soft mb-3">
  <i class="bi bi-info-circle"></i>
  {{ __t('เครดิตใช้ได้ทุกสาขา · แพ็กเหมาจ่ายไม่ตัดเครดิตแต่จำกัดจำนวนครั้งต่อวัน/สัปดาห์ได้', 'Credits work at every branch · Unlimited packages do not deduct credits but can cap visits per day/week') }}
</div>

<div class="card-panel">
  <div class="table-wrap">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>{{ __t('แพ็กเกจ', 'Package') }}</th><th>{{ __t('ชนิด', 'Type') }}</th><th class="text-center">{{ __t('เครดิต', 'Credits') }}</th>
          <th class="text-end">{{ __t('ราคา', 'Price') }}</th><th class="text-center">{{ __t('อายุ', 'Validity') }}</th>
          <th>{{ __t('โควตา', 'Quota') }}</th><th>{{ __t('สถานะ', 'Status') }}</th><th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($packages as $p)
          <tr>
            <td>
              <div class="fw-semibold">{{ $p->name }}</div>
              <div class="small text-secondary">{{ app()->getLocale() === 'en' ? $p->name_th : $p->name_en }} · {{ $p->code }}</div>
            </td>
            <td>
              @php $tm = ['credit_pack'=>[__t('นับครั้ง','Credit pack'),'badge-soft'],'unlimited'=>[__t('เหมาจ่าย','Unlimited'),'badge-accent'],'trial'=>[__t('ทดลอง','Trial'),'badge-warn']]; @endphp
              <span class="badge-soft {{ $tm[$p->type][1] }}">{{ $tm[$p->type][0] }}</span>
              @if($p->once_per_customer)
                <div class="small text-secondary">{{ __t('ซื้อได้ครั้งเดียว', 'One per customer') }}</div>
              @endif
            </td>
            <td class="text-center">
              {{ $p->credit_amount === null ? '∞' : $p->credit_amount }}
            </td>
            <td class="text-end" style="font-variant-numeric:tabular-nums;">
              {{ number_format($p->price) }}
              @if($p->compare_at_price)
                <div class="small text-secondary text-decoration-line-through">{{ number_format($p->compare_at_price) }}</div>
              @endif
            </td>
            <td class="text-center">{{ $p->valid_days }} {{ __t('วัน', 'days') }}</td>
            <td class="small text-secondary">
              @if($p->max_per_day || $p->max_per_week)
                @if($p->max_per_day){{ $p->max_per_day }}/{{ __t('วัน', 'day') }} @endif
                @if($p->max_per_week)· {{ $p->max_per_week }}/{{ __t('สัปดาห์', 'week') }}@endif
              @else
                —
              @endif
            </td>
            <td>
              @if($p->is_active)
                <span class="badge-soft badge-ok">{{ __t('ขายอยู่', 'On sale') }}</span>
              @else
                <span class="badge-soft badge-danger">{{ __t('ปิด', 'Off') }}</span>
              @endif
              @unless($p->is_public)
                <div class="small text-secondary">{{ __t('หน้าร้านเท่านั้น', 'In-store only') }}</div>
              @endunless
            </td>
            <td class="text-end">
              <a href="{{ route('admin.packages.edit', $p) }}" class="btn btn-sm btn-outline-secondary">
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

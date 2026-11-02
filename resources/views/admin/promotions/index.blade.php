@extends('admin.layouts.app')
@section('title', __t('โปรโมชั่น', 'Promotions'))

@section('topbar-actions')
  <a href="{{ route('admin.promotions.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> {{ __t('เพิ่มโปรโมชั่น', 'Add promotion') }}
  </a>
@endsection

@section('content')
<div class="alert-soft mb-3">
  <i class="bi bi-info-circle"></i>
  {{ __t('โปรที่กำลังใช้งานจะลดราคาแพ็กให้อัตโนมัติ ทั้งหน้าลูกค้าซื้อเองและหน้าเปิดบิล', 'An active promotion automatically discounts packages — both on the customer store and when creating orders') }}
</div>

<div class="card-panel">
  <div class="table-wrap">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>{{ __t('โปรโมชั่น', 'Promotion') }}</th>
          <th class="text-end">{{ __t('ส่วนลด', 'Discount') }}</th>
          <th>{{ __t('ใช้กับ', 'Applies to') }}</th>
          <th>{{ __t('ช่วงเวลา', 'Period') }}</th>
          <th>{{ __t('สถานะ', 'Status') }}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($promotions as $promo)
          <tr>
            <td>
              <div class="fw-semibold">{{ $promo->name }}</div>
              <div class="small text-secondary">{{ app()->getLocale() === 'en' ? $promo->name_th : $promo->name_en }} · {{ $promo->code }}</div>
            </td>
            <td class="text-end" style="font-variant-numeric:tabular-nums;">
              @if($promo->discount_type === 'percent')
                {{ rtrim(rtrim(number_format($promo->discount_value, 2), '0'), '.') }}%
              @else
                {{ number_format($promo->discount_value) }} ฿
              @endif
            </td>
            <td class="small">
              @if($promo->applies_to_all)
                {{ __t('ทุกแพ็ก', 'All packages') }}
              @else
                {{ __t($promo->packages_count . ' แพ็ก', $promo->packages_count . ' packages') }}
              @endif
            </td>
            <td class="small text-secondary">
              {{ $promo->starts_at->locale(app()->getLocale())->isoFormat('D MMM YY') }}
              – {{ $promo->ends_at->locale(app()->getLocale())->isoFormat('D MMM YY') }}
            </td>
            <td>
              @if(! $promo->is_active)
                <span class="badge-soft badge-danger">{{ __t('ปิด', 'Off') }}</span>
              @elseif($promo->starts_at > now())
                <span class="badge-soft badge-warn">{{ __t('ยังไม่เริ่ม', 'Scheduled') }}</span>
              @elseif($promo->ends_at < now())
                <span class="badge-soft">{{ __t('หมดอายุ', 'Expired') }}</span>
              @else
                <span class="badge-soft badge-ok">{{ __t('กำลังใช้', 'Running') }}</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('admin.promotions.edit', $promo) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i>
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-secondary py-4">
              {{ __t('ยังไม่มีโปรโมชั่น', 'No promotions yet') }}
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

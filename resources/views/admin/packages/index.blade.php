@extends('admin.layouts.app')
@section('title', 'แพ็กเกจ')

@section('topbar-actions')
  <a href="{{ route('admin.packages.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> เพิ่มแพ็กเกจ
  </a>
@endsection

@section('content')
<div class="alert-soft mb-3">
  <i class="bi bi-info-circle"></i>
  เครดิตใช้ได้ทุกสาขา · แพ็กเหมาจ่ายไม่ตัดเครดิตแต่จำกัดจำนวนครั้งต่อวัน/สัปดาห์ได้
</div>

<div class="card-panel">
  <div class="table-wrap">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>แพ็กเกจ</th><th>ชนิด</th><th class="text-center">เครดิต</th>
          <th class="text-end">ราคา</th><th class="text-center">อายุ</th>
          <th>โควตา</th><th>สถานะ</th><th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($packages as $p)
          <tr>
            <td>
              <div class="fw-semibold">{{ $p->name_th }}</div>
              <div class="small text-secondary">{{ $p->name_en }} · {{ $p->code }}</div>
            </td>
            <td>
              @php $tm = ['credit_pack'=>['นับครั้ง','badge-soft'],'unlimited'=>['เหมาจ่าย','badge-accent'],'trial'=>['ทดลอง','badge-warn']]; @endphp
              <span class="badge-soft {{ $tm[$p->type][1] }}">{{ $tm[$p->type][0] }}</span>
              @if($p->once_per_customer)
                <div class="small text-secondary">ซื้อได้ครั้งเดียว</div>
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
            <td class="text-center">{{ $p->valid_days }} วัน</td>
            <td class="small text-secondary">
              @if($p->max_per_day || $p->max_per_week)
                @if($p->max_per_day){{ $p->max_per_day }}/วัน @endif
                @if($p->max_per_week)· {{ $p->max_per_week }}/สัปดาห์@endif
              @else
                —
              @endif
            </td>
            <td>
              @if($p->is_active)
                <span class="badge-soft badge-ok">ขายอยู่</span>
              @else
                <span class="badge-soft badge-danger">ปิด</span>
              @endif
              @unless($p->is_public)
                <div class="small text-secondary">หน้าร้านเท่านั้น</div>
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

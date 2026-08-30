@php
  $map = [
    'confirmed'      => ['ยืนยันแล้ว', 'badge-ok'],
    'attended'       => ['เข้าเรียนแล้ว', 'badge-accent'],
    'waitlisted'     => ['คิวสำรอง', 'badge-warn'],
    'cancelled'      => ['ยกเลิกแล้ว', 'badge-soft'],
    'late_cancelled' => ['ยกเลิกช้า', 'badge-danger'],
    'no_show'        => ['ไม่มาเรียน', 'badge-danger'],
  ];
  [$label, $class] = $map[$status] ?? [$status, 'badge-soft'];
@endphp
<span class="badge-soft {{ $class }}">{{ $label }}</span>

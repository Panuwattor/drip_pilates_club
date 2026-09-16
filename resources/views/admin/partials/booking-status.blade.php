@php
  $map = [
    'confirmed'      => [__t('ยืนยันแล้ว', 'Confirmed'), 'badge-ok'],
    'attended'       => [__t('เข้าเรียนแล้ว', 'Attended'), 'badge-accent'],
    'waitlisted'     => [__t('คิวสำรอง', 'Waitlisted'), 'badge-warn'],
    'cancelled'      => [__t('ยกเลิกแล้ว', 'Cancelled'), 'badge-soft'],
    'late_cancelled' => [__t('ยกเลิกช้า', 'Late cancelled'), 'badge-danger'],
    'no_show'        => [__t('ไม่มาเรียน', 'No show'), 'badge-danger'],
  ];
  [$label, $class] = $map[$status] ?? [$status, 'badge-soft'];
@endphp
<span class="badge-soft {{ $class }}">{{ $label }}</span>

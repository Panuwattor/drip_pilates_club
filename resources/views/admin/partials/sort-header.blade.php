{{--
  หัวตารางที่กดเรียงได้
  $key   = คีย์คอลัมน์ (ต้องตรงกับ BookingController::SORTABLE)
  $label = ข้อความบนหัวตาราง
  $sort  = คีย์ที่กำลังเรียงอยู่   $dir = ทิศทางปัจจุบัน (asc/desc)

  กดคอลัมน์เดิมซ้ำ = สลับทิศทาง, กดคอลัมน์ใหม่ = เริ่มที่ asc
  ยกเว้นวันเวลา/ลำดับการจองที่เริ่มจากใหม่ไปเก่า เพราะเป็นสิ่งที่ต้องการดูก่อน
--}}
@php
    $active = $sort === $key;
    $defaultDir = in_array($key, ['date', 'booked'], true) ? 'desc' : 'asc';
    $nextDir = $active ? ($dir === 'asc' ? 'desc' : 'asc') : $defaultDir;
    $url = request()->fullUrlWithQuery(['sort' => $key, 'dir' => $nextDir, 'page' => null]);
@endphp
<th class="{{ $align ?? '' }}">
  <a href="{{ $url }}" class="sort-link {{ $active ? 'active' : '' }}"
     aria-sort="{{ $active ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    {{ $label }}
    @if($active)
      <i class="bi bi-caret-{{ $dir === 'asc' ? 'up' : 'down' }}-fill"></i>
    @else
      <i class="bi bi-arrow-down-up sort-idle"></i>
    @endif
  </a>
</th>

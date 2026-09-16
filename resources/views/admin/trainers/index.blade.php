@extends('admin.layouts.app')
@section('title', 'ครูผู้สอน')

@section('topbar-actions')
  <a href="{{ route('admin.trainers.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> เพิ่มครู
  </a>
@endsection

@section('content')
<div class="alert-soft mb-3">
  <i class="bi bi-info-circle"></i>
  ครูไม่ต้องเข้าสู่ระบบ ส่ง "ลิงก์ตารางสอน" ให้ครูเปิดดูตารางและรายชื่อผู้เรียนได้เลย
  ถ้าลิงก์หลุดให้กดสร้างลิงก์ใหม่
</div>

<div class="card-panel">
  <div class="table-wrap">
    <table class="table align-middle">
      <thead>
        <tr>
          <th></th><th>ชื่อ</th><th>ความถนัด</th><th>สาขา</th>
          <th class="text-center">ตารางข้างหน้า</th><th>สถานะ</th><th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($trainers as $trainer)
          <tr>
            <td style="width:44px;">
              @if($trainer->avatar)
                <img src="{{ asset($trainer->avatar) }}" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
              @else
                <div style="width:36px;height:36px;border-radius:50%;background:var(--sage-soft);color:var(--sage);display:flex;align-items:center;justify-content:center;">
                  <i class="bi bi-person"></i>
                </div>
              @endif
            </td>
            <td>
              <div class="fw-semibold">{{ $trainer->name_th }}</div>
              <div class="small text-secondary">{{ $trainer->name_en }} · {{ $trainer->code }}</div>
            </td>
            <td class="small text-secondary">{{ $trainer->specialties_th ?: '—' }}</td>
            <td class="small text-secondary">
              {{ $trainer->branches->pluck('short_name_th')->filter()->join(', ') ?: 'ทุกสาขา' }}
            </td>
            <td class="text-center" style="font-variant-numeric:tabular-nums;">{{ $trainer->class_sessions_count }}</td>
            <td>
              @if($trainer->is_active)
                <span class="badge-soft badge-ok">ใช้งาน</span>
              @else
                <span class="badge-soft badge-danger">ปิด</span>
              @endif
            </td>
            <td class="text-end" style="white-space:nowrap;">
              <button class="btn btn-sm btn-outline-secondary copy-link"
                      data-link="{{ route('trainer.schedule', $trainer->public_token) }}"
                      type="button" title="คัดลอกลิงก์ตารางสอน">
                <i class="bi bi-link-45deg"></i>
              </button>
              <a href="{{ route('trainer.schedule', $trainer->public_token) }}" target="_blank"
                 class="btn btn-sm btn-outline-secondary" title="เปิดดูตาราง">
                <i class="bi bi-box-arrow-up-right"></i>
              </a>
              <a href="{{ route('admin.trainers.edit', $trainer) }}" class="btn btn-sm btn-outline-secondary">
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

@push('scripts')
<script>
// แสดงลิงก์ให้คัดลอกเองเมื่อ clipboard ใช้ไม่ได้ (เช่นหน้าเว็บไม่ได้อยู่บน https)
function showLinkFallback(link){
  if(!window.Swal){ window.prompt('คัดลอกลิงก์นี้ส่งให้ครู', link); return; }
  Swal.fire({
    title: 'คัดลอกลิงก์นี้ส่งให้ครู',
    input: 'text',
    inputValue: link,
    inputAttributes: { readonly: 'readonly' },
    confirmButtonText: 'ปิด',
    buttonsStyling: false,
    customClass: { popup: 'swal-admin', confirmButton: 'swal2-confirm swal2-styled', input: 'form-control' },
    didOpen: function(){
      var el = Swal.getInput();
      el && el.select();
    }
  });
}

document.querySelectorAll('.copy-link').forEach(function(btn){
  btn.addEventListener('click', function(){
    var link = this.dataset.link;
    var done = this;
    // clipboard API ไม่มีบน http จึงต้องเช็คก่อน ไม่งั้น .then โยน error
    if(!navigator.clipboard){ showLinkFallback(link); return; }
    navigator.clipboard.writeText(link).then(function(){
      var old = done.innerHTML;
      done.innerHTML = '<i class="bi bi-check-lg"></i>';
      setTimeout(function(){ done.innerHTML = old; }, 1400);
      if(window.Swal){
        Swal.fire({
          toast: true, position: 'top-end', icon: 'success',
          title: 'คัดลอกลิงก์แล้ว', showConfirmButton: false, timer: 1800, timerProgressBar: true
        });
      }
    }).catch(function(){
      showLinkFallback(link);
    });
  });
});
</script>
@endpush

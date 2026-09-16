@extends('admin.layouts.app')
@section('title', __t('ครูผู้สอน', 'Trainers'))

@section('topbar-actions')
  <a href="{{ route('admin.trainers.create') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> {{ __t('เพิ่มครู', 'Add trainer') }}
  </a>
@endsection

@section('content')
<div class="alert-soft mb-3">
  <i class="bi bi-info-circle"></i>
  {{ __t('ครูไม่ต้องเข้าสู่ระบบ ส่ง "ลิงก์ตารางสอน" ให้ครูเปิดดูตารางและรายชื่อผู้เรียนได้เลย ถ้าลิงก์หลุดให้กดสร้างลิงก์ใหม่', 'Trainers do not need an account — send them their schedule link to view classes and rosters. If a link leaks, generate a new one.') }}
</div>

<div class="card-panel">
  <div class="table-wrap">
    <table class="table align-middle">
      <thead>
        <tr>
          <th></th><th>{{ __t('ชื่อ', 'Name') }}</th><th>{{ __t('ความถนัด', 'Specialties') }}</th><th>{{ __t('สาขา', 'Branch') }}</th>
          <th class="text-center">{{ __t('ตารางข้างหน้า', 'Upcoming') }}</th><th>{{ __t('สถานะ', 'Status') }}</th><th></th>
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
              <div class="fw-semibold">{{ $trainer->name }}</div>
              <div class="small text-secondary">{{ app()->getLocale() === 'en' ? $trainer->name_th : $trainer->name_en }} · {{ $trainer->code }}</div>
            </td>
            <td class="small text-secondary">{{ $trainer->specialties ?: '—' }}</td>
            <td class="small text-secondary">
              {{ $trainer->branches->map->short_name->filter()->join(', ') ?: __t('ทุกสาขา', 'All branches') }}
            </td>
            <td class="text-center" style="font-variant-numeric:tabular-nums;">{{ $trainer->class_sessions_count }}</td>
            <td>
              @if($trainer->is_active)
                <span class="badge-soft badge-ok">{{ __t('ใช้งาน', 'Active') }}</span>
              @else
                <span class="badge-soft badge-danger">{{ __t('ปิด', 'Off') }}</span>
              @endif
            </td>
            <td class="text-end" style="white-space:nowrap;">
              <button class="btn btn-sm btn-outline-secondary copy-link"
                      data-link="{{ route('trainer.schedule', $trainer->public_token) }}"
                      type="button" title="{{ __t('คัดลอกลิงก์ตารางสอน', 'Copy schedule link') }}">
                <i class="bi bi-link-45deg"></i>
              </button>
              <a href="{{ route('trainer.schedule', $trainer->public_token) }}" target="_blank"
                 class="btn btn-sm btn-outline-secondary" title="{{ __t('เปิดดูตาราง', 'Open schedule') }}">
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
  if(!window.Swal){ window.prompt(@json(__t('คัดลอกลิงก์นี้ส่งให้ครู', 'Copy this link and send it to the trainer')), link); return; }
  Swal.fire({
    title: @json(__t('คัดลอกลิงก์นี้ส่งให้ครู', 'Copy this link and send it to the trainer')),
    input: 'text',
    inputValue: link,
    inputAttributes: { readonly: 'readonly' },
    confirmButtonText: @json(__t('ปิด', 'Close')),
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
          title: @json(__t('คัดลอกลิงก์แล้ว', 'Link copied')), showConfirmButton: false, timer: 1800, timerProgressBar: true
        });
      }
    }).catch(function(){
      showLinkFallback(link);
    });
  });
});
</script>
@endpush

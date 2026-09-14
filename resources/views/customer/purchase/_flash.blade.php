@if(session('status'))
  <div class="alert alert-success py-2 px-3 small mb-3" style="border-radius:12px;">
    <i class="bi bi-check-circle"></i> {{ session('status') }}
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger py-2 px-3 small mb-3" style="border-radius:12px;">
    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger py-2 px-3 small mb-3" style="border-radius:12px;">
    <ul class="mb-0 ps-3">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

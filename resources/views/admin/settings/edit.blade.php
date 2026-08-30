@extends('admin.layouts.app')
@section('title', 'ตั้งค่าระบบ')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}">
  @csrf @method('PUT')

  <div class="row g-3">
    @foreach($settings as $group => $rows)
      <div class="col-lg-6">
        <div class="card-panel h-100">
          <div class="ttl">{{ $groupLabels[$group] ?? $group }}</div>

          @foreach($rows as $setting)
            <div class="mb-3">
              @if($setting->type === 'bool')
                <div class="form-check">
                  <input class="form-check-input" type="checkbox"
                         id="s_{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="1"
                         {{ filter_var($setting->value, FILTER_VALIDATE_BOOL) ? 'checked' : '' }}>
                  <label class="form-check-label" for="s_{{ $setting->key }}">
                    {{ $setting->label_th ?? $setting->key }}
                  </label>
                </div>
              @else
                <label class="form-label" for="s_{{ $setting->key }}">
                  {{ $setting->label_th ?? $setting->key }}
                </label>
                <input class="form-control" id="s_{{ $setting->key }}"
                       type="{{ $setting->type === 'int' ? 'number' : 'text' }}"
                       name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
              @endif

              <div class="form-text small text-secondary">{{ $setting->key }}</div>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-3">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> บันทึกการตั้งค่า</button>
  </div>
</form>
@endsection

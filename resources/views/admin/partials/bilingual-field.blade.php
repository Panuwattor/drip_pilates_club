{{--
  ช่องกรอกข้อมูล 2 ภาษา มีแท็บ ไทย/EN สลับกัน บังคับกรอกครบทั้งคู่
  ใช้: @include('admin.partials.bilingual-field', [
        'name' => 'name', 'label' => 'ชื่อ', 'model' => $branch,
        'type' => 'text'|'textarea', 'required' => true, 'rows' => 3,
      ])
--}}
@php
  $type = $type ?? 'text';
  $required = $required ?? false;
  $rows = $rows ?? 3;
  $uid = $name . '_' . uniqid();
@endphp

<div class="mb-3" data-lang-group>
  <div class="d-flex align-items-center gap-2 mb-1">
    <label class="form-label mb-0">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <div class="lang-tabs ms-auto mb-0">
      <button class="lang-tab active" type="button" data-lang="th">ไทย</button>
      <button class="lang-tab" type="button" data-lang="en">EN</button>
    </div>
  </div>

  @foreach(['th' => 'ไทย', 'en' => 'English'] as $lang => $langLabel)
    <div class="lang-pane {{ $lang === 'th' ? 'active' : '' }}" data-lang="{{ $lang }}">
      @if($type === 'textarea')
        <textarea class="form-control @error($name . '_' . $lang) is-invalid @enderror"
                  name="{{ $name }}_{{ $lang }}" rows="{{ $rows }}"
                  placeholder="{{ $placeholder ?? $label }} ({{ $langLabel }})"
                  @if($required) required @endif>{{ old($name . '_' . $lang, $model->{$name . '_' . $lang} ?? '') }}</textarea>
      @else
        <input class="form-control @error($name . '_' . $lang) is-invalid @enderror"
               type="text" name="{{ $name }}_{{ $lang }}"
               value="{{ old($name . '_' . $lang, $model->{$name . '_' . $lang} ?? '') }}"
               placeholder="{{ $placeholder ?? $label }} ({{ $langLabel }})"
               @if($required) required @endif>
      @endif

      @error($name . '_' . $lang)
        <div class="text-danger small mt-1">{{ $message }}</div>
      @enderror
    </div>
  @endforeach

  @if(($errors->has($name . '_th') || $errors->has($name . '_en')))
    <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle"></i> ต้องกรอกครบทั้ง 2 ภาษา</div>
  @endif
</div>

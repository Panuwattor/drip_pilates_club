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

  @foreach(['th' => __t('ไทย', 'Thai'), 'en' => __t('อังกฤษ', 'English')] as $lang => $langLabel)
    <div class="lang-pane {{ $lang === 'th' ? 'active' : '' }}" data-lang="{{ $lang }}">
      @if($type === 'richtext')
        @php $fieldId = $uid . '_' . $lang; @endphp
        <div class="richtext-field" data-richtext-field>
          <div id="{{ $fieldId }}_editor" class="richtext-editor @error($name . '_' . $lang) is-invalid @enderror"></div>
          <textarea id="{{ $fieldId }}" name="{{ $name }}_{{ $lang }}" hidden
                    @if($required) required @endif>{{ old($name . '_' . $lang, $model->{$name . '_' . $lang} ?? '') }}</textarea>
        </div>
      @elseif($type === 'textarea')
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
    <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle"></i> {{ __t('ต้องกรอกครบทั้ง 2 ภาษา', 'Both languages are required') }}</div>
  @endif
</div>

@if($type === 'richtext')
  @once
    @push('styles')
      <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
      <style>
        .richtext-editor{ background:var(--panel); border-radius:var(--r-sm); }
        .richtext-editor .ql-toolbar{ border-color:var(--line); border-radius:var(--r-sm) var(--r-sm) 0 0; }
        .richtext-editor .ql-container{ border-color:var(--line); border-radius:0 0 var(--r-sm) var(--r-sm); font-size:.87rem; min-height:160px; }
        .richtext-editor.is-invalid{ border:1px solid var(--danger); border-radius:var(--r-sm); }
        .richtext-editor .ql-editor{ min-height:160px; color:var(--ink); }
      </style>
    @endpush
    @push('scripts')
      <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          document.querySelectorAll('[data-richtext-field]').forEach(function (wrap) {
            var textarea = wrap.querySelector('textarea');
            var editorEl = wrap.querySelector('.richtext-editor');
            var quill = new Quill(editorEl, {
              theme: 'snow',
              modules: {
                toolbar: [
                  [{ header: [2, 3, false] }],
                  ['bold', 'italic', 'underline', 'strike'],
                  [{ list: 'ordered' }, { list: 'bullet' }],
                  ['blockquote', 'link'],
                  ['clean'],
                ],
              },
            });
            quill.root.innerHTML = textarea.value;
            quill.on('text-change', function () {
              var html = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
              textarea.value = html;
            });
            wrap.closest('form').addEventListener('submit', function () {
              var html = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
              textarea.value = html;
            });
          });
        });
      </script>
    @endpush
  @endonce
@endif

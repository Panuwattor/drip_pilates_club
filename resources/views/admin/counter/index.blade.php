@extends('admin.layouts.app')
@section('title', 'เคาน์เตอร์')

@section('content')

{{-- ── ค้นหาลูกค้า ───────────────────────────────── --}}
<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
  <input class="form-control" name="q" value="{{ $search }}" autofocus
         placeholder="ค้นหาลูกค้า — ชื่อ ชื่อเล่น เบอร์โทร หรือรหัสสมาชิก"
         style="max-width:420px;font-size:1rem;">
  <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> ค้นหา</button>
  @if($search !== '' || $customer)
    <a href="{{ route('admin.counter.index') }}" class="btn btn-outline-secondary">เริ่มใหม่</a>
  @endif
</form>

{{-- ── ผลค้นหาหลายคน ให้เลือก ───────────────────── --}}
@if($customers->isNotEmpty())
  <div class="card-panel mb-3">
    <div class="ttl">พบ {{ $customers->count() }} คน — เลือกลูกค้า</div>
    <div class="table-wrap">
      <table class="table align-middle mb-0">
        <tbody>
          @foreach($customers as $c)
            <tr>
              <td style="width:1%;white-space:nowrap;" class="small text-secondary">{{ $c->code }}</td>
              <td>
                <span class="fw-semibold">{{ $c->full_name }}</span>
                @if($c->nickname)<span class="small text-secondary">({{ $c->nickname }})</span>@endif
              </td>
              <td class="small">{{ $c->phone }}</td>
              <td class="small text-secondary">{{ $c->homeBranch?->short_name_th ?? $c->homeBranch?->name_th ?? '—' }}</td>
              <td class="text-center num-cell">
                @if($c->hasUnlimited())
                  <span class="badge-soft badge-accent">เหมาจ่าย</span>
                @else
                  {{ $c->totalCredits() }} เครดิต
                @endif
              </td>
              <td class="text-end" style="width:1%;">
                <a href="{{ route('admin.counter.index', ['customer' => $c->id]) }}" class="btn btn-sm btn-primary">
                  เลือก <i class="bi bi-chevron-right"></i>
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif

@if(! $customer)
  @if($search !== '' && $customers->isEmpty())
    <div class="empty-note"><i class="bi bi-person-x"></i>ไม่พบลูกค้าที่ตรงกับ "{{ $search }}"</div>
  @else
    <div class="empty-note">
      <i class="bi bi-search"></i>
      ค้นหาลูกค้าก่อน แล้วเลือกงานที่ต้องการ — หักเครดิต เพิ่มเครดิต หรือขายแพ็ก
    </div>
  @endif
@else

  @php
    $credits = $customer->totalCredits();
    $unlimited = $customer->hasUnlimited();
    $nearest = $usablePackages->first();
  @endphp

  {{-- ── การ์ดลูกค้า ─────────────────────────────── --}}
  <div class="card-panel mb-3">
    <div class="d-flex flex-wrap align-items-center gap-3">
      <div class="flex-grow-1" style="min-width:220px;">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <h2 style="font-size:1.2rem;font-weight:700;margin:0;">{{ $customer->full_name }}</h2>
          @if($customer->nickname)<span class="text-secondary">({{ $customer->nickname }})</span>@endif
          <span class="badge-soft">{{ $customer->code }}</span>
          @if($customer->status !== 'active')
            <span class="badge-soft badge-danger">{{ $customer->status === 'banned' ? 'ถูกระงับ' : 'ไม่ใช้งาน' }}</span>
          @endif
        </div>
        <div class="small text-secondary mt-1">
          <i class="bi bi-telephone"></i> {{ $customer->phone }}
          @if($customer->homeBranch)
            · <i class="bi bi-geo-alt"></i> {{ $customer->homeBranch->short_name_th ?? $customer->homeBranch->name_th }}
          @endif
        </div>
        @if($customer->medical_note)
          <div class="small mt-1" style="color:var(--warn);">
            <i class="bi bi-heart-pulse"></i> {{ $customer->medical_note }}
          </div>
        @endif
      </div>

      <div class="text-center px-3">
        <div style="font-size:2rem;font-weight:700;line-height:1;color:var(--accent-deep);" class="num-cell">
          {{ $unlimited ? '∞' : $credits }}
        </div>
        <div class="lbl" style="font-size:.72rem;color:var(--ink-soft);">เครดิตคงเหลือ</div>
      </div>

      <div>
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-person"></i> โปรไฟล์เต็ม
        </a>
      </div>
    </div>

    @if($nearest)
      <div class="small text-secondary mt-2 pt-2" style="border-top:1px solid var(--line);">
        ใบที่จะถูกตัดก่อน: <strong>{{ $nearest->package->name_th }}</strong>
        เหลือ {{ $nearest->credit_remaining }} · หมดอายุ {{ $nearest->expires_at->format('d/m/Y') }}
        @if($usablePackages->count() > 1)
          <span class="text-secondary">(มีอีก {{ $usablePackages->count() - 1 }} ใบ)</span>
        @endif
      </div>
    @elseif(! $unlimited)
      <div class="small mt-2 pt-2" style="border-top:1px solid var(--line);color:var(--danger);">
        <i class="bi bi-exclamation-triangle"></i> ลูกค้าไม่มีเครดิตที่ใช้ได้ — ต้องขายแพ็กก่อน
      </div>
    @endif
  </div>

  {{-- ── ปุ่มลัดงานหน้าร้าน ───────────────────────── --}}
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card-panel h-100">
        <div class="ttl"><i class="bi bi-dash-circle"></i> หักเครดิต</div>

        @if($unlimited)
          <div class="empty-note py-3">ลูกค้าถือแพ็กเหมาจ่าย ไม่ต้องหักเครดิต</div>
        @elseif($credits <= 0)
          <div class="empty-note py-3">ไม่มีเครดิตให้หัก</div>
        @else
          {{-- เลือกโหมด: ผูกกับคลาสจริง หรือปรับเครดิตล้วนๆ --}}
          <div class="btn-group w-100 mb-3" role="group">
            <button type="button" class="btn btn-sm btn-primary mode-btn" data-mode="walkin">
              <i class="bi bi-person-walking"></i> มาเรียนคลาสนี้
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary mode-btn" data-mode="adjust">
              <i class="bi bi-sliders"></i> ปรับเครดิตอย่างเดียว
            </button>
          </div>

          {{-- ── โหมด walk-in: สร้าง booking จริง นับยอดคนเข้าคลาส ── --}}
          <div id="modeWalkin">
            @if($todaySessions->isEmpty())
              <div class="empty-note py-3">
                วันนี้ไม่มีคลาสที่ยังไม่จบ — ถ้าต้องแก้ย้อนหลังให้ใช้ "ปรับเครดิตอย่างเดียว"
              </div>
            @else
              <form method="POST" action="{{ route('admin.counter.walkin', $customer) }}" id="walkinForm">
                @csrf
                <label class="form-label small">เลือกคลาสที่ลูกค้าเข้าเรียน</label>
                <select class="form-select form-select-sm mb-2" name="class_session_id" required>
                  @foreach($todaySessions as $s)
                    @php $full = $s->booked_count >= $s->capacity; @endphp
                    <option value="{{ $s->id }}" @if($full) data-full="1" @endif>
                      {{ $s->start_at->format('H:i') }}–{{ $s->end_at->format('H:i') }}
                      · {{ $s->classType->name_th }}
                      @if($s->actualTrainer()) · {{ $s->actualTrainer()->nickname ?? $s->actualTrainer()->first_name }} @endif
                      · {{ $s->booked_count }}/{{ $s->capacity }}{{ $full ? ' (เต็ม)' : '' }}
                      · {{ rtrim(rtrim(number_format((float) $s->credit_cost, 2), '0'), '.') }} เครดิต
                    </option>
                  @endforeach
                </select>

                @if(session('error') && str_contains(session('error'), 'เต็มแล้ว'))
                  <input type="hidden" name="confirm_overbook" value="1">
                  <div class="small mb-2" style="color:var(--warn);">
                    <i class="bi bi-exclamation-triangle"></i> กดอีกครั้งเพื่อยืนยันรับเกินความจุ
                  </div>
                @endif

                <button class="btn btn-danger w-100" type="submit">
                  <i class="bi bi-box-arrow-in-right"></i> บันทึกเข้าคลาส + เช็คอิน
                </button>
                <div class="form-text small mt-1">
                  หักเครดิตตามที่คลาสนั้นกำหนด และนับเป็นคนเข้าเรียนในรายงาน
                </div>
              </form>
            @endif
          </div>

          {{-- ── โหมดปรับเครดิตล้วนๆ: ไม่มีคลาสมาเกี่ยว ── --}}
          <div id="modeAdjust" style="display:none;">
          <form method="POST" action="{{ route('admin.counter.deduct', $customer) }}" id="deductForm"
                data-confirm="ยืนยันหักเครดิตของ {{ $customer->full_name }}?">
            @csrf
            <input type="hidden" name="preset" id="deductPreset" value="correction_deduct">

            <div class="small text-secondary mb-2">เลือกเหตุผล</div>
            <div class="d-flex flex-wrap gap-2 mb-3">
              @foreach($presets as $key => [$label, $dir, $type])
                {{-- walk_in/manual_class ย้ายไปโหมดบนแล้ว เพราะต้องผูกกับคลาสจริง --}}
                @if($dir === -1 && ! in_array($key, ['walk_in', 'manual_class'], true))
                  <button type="button" class="btn btn-sm preset-btn {{ $key === 'correction_deduct' ? 'btn-primary' : 'btn-outline-secondary' }}"
                          data-target="deductPreset" data-value="{{ $key }}">{{ $label }}</button>
                @endif
              @endforeach
              <button type="button" class="btn btn-sm btn-outline-secondary preset-btn"
                      data-target="deductPreset" data-value="other" data-needs-note="1">อื่นๆ</button>
            </div>

            <div class="row g-2 align-items-end">
              <div class="col-auto">
                <label class="form-label small">จำนวน</label>
                <div class="d-flex gap-1">
                  @foreach([1, 2, 3] as $n)
                    <button type="button" class="btn btn-sm amount-btn {{ $n === 1 ? 'btn-primary' : 'btn-outline-secondary' }}"
                            data-target="deductAmount" data-value="{{ $n }}" style="min-width:38px;">{{ $n }}</button>
                  @endforeach
                  <input class="form-control form-control-sm num-cell" type="number" name="amount" id="deductAmount"
                         value="1" min="0.5" max="{{ $credits }}" step="0.5" required style="width:78px;">
                </div>
              </div>
              <div class="col">
                <label class="form-label small">หมายเหตุ <span class="text-secondary" id="deductNoteHint"></span></label>
                <input class="form-control form-control-sm" name="note" maxlength="200"
                       placeholder="ไม่บังคับ" value="{{ old('note') }}">
              </div>
            </div>

            <button class="btn btn-danger w-100 mt-3" type="submit">
              <i class="bi bi-dash-lg"></i> หักเครดิต
            </button>
            <div class="form-text small mt-1">ตัดจากแพ็กที่ใกล้หมดอายุก่อนอัตโนมัติ · ไม่นับเป็นคนเข้าคลาส</div>
          </form>
          </div>
        @endif
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card-panel h-100">
        <div class="ttl"><i class="bi bi-plus-circle"></i> เพิ่มเครดิต</div>

        @if($customer->packages->where('type', '!=', 'unlimited')->isEmpty())
          <div class="empty-note py-3">
            ลูกค้ายังไม่มีแพ็ก — ต้องขายแพ็กก่อนถึงจะเพิ่มเครดิตได้
            <div class="mt-2">
              <a href="{{ route('admin.orders.create', ['customer' => $customer->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-cart-plus"></i> ขายแพ็กให้ลูกค้า
              </a>
            </div>
          </div>
        @else
          <form method="POST" action="{{ route('admin.counter.add', $customer) }}">
            @csrf
            <input type="hidden" name="preset" id="addPreset" value="compensate_class">

            <div class="small text-secondary mb-2">เลือกเหตุผล</div>
            <div class="d-flex flex-wrap gap-2 mb-3">
              @foreach($presets as $key => [$label, $dir, $type])
                @if($dir === 1)
                  <button type="button" class="btn btn-sm preset-btn {{ $loop->first ? 'btn-primary' : 'btn-outline-secondary' }}"
                          data-target="addPreset" data-value="{{ $key }}">{{ $label }}</button>
                @endif
              @endforeach
              <button type="button" class="btn btn-sm btn-outline-secondary preset-btn"
                      data-target="addPreset" data-value="other" data-needs-note="1">อื่นๆ</button>
            </div>

            <div class="row g-2 align-items-end">
              <div class="col-auto">
                <label class="form-label small">จำนวน</label>
                <div class="d-flex gap-1">
                  @foreach([1, 2, 3] as $n)
                    <button type="button" class="btn btn-sm amount-btn {{ $n === 1 ? 'btn-primary' : 'btn-outline-secondary' }}"
                            data-target="addAmount" data-value="{{ $n }}" style="min-width:38px;">{{ $n }}</button>
                  @endforeach
                  <input class="form-control form-control-sm num-cell" type="number" name="amount" id="addAmount"
                         value="1" min="0.5" max="99" step="0.5" required style="width:78px;">
                </div>
              </div>
              <div class="col">
                <label class="form-label small">หมายเหตุ</label>
                <input class="form-control form-control-sm" name="note" maxlength="200" placeholder="ไม่บังคับ">
              </div>
            </div>

            <div class="mt-2">
              <label class="form-label small">เติมเข้าแพ็ก</label>
              <select class="form-select form-select-sm" name="customer_package_id">
                <option value="">ใบที่ใกล้หมดอายุที่สุด (แนะนำ)</option>
                @foreach($customer->packages->where('type', '!=', 'unlimited')->whereIn('status', ['active', 'used_up', 'frozen']) as $cp)
                  <option value="{{ $cp->id }}">
                    {{ $cp->package->name_th }} — เหลือ {{ $cp->credit_remaining }} (หมดอายุ {{ $cp->expires_at->format('d/m/Y') }})
                  </option>
                @endforeach
              </select>
            </div>

            <button class="btn btn-primary w-100 mt-3" type="submit">
              <i class="bi bi-plus-lg"></i> เพิ่มเครดิต
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>

  {{-- ── งานอื่นที่ทำบ่อย ─────────────────────────── --}}
  <div class="card-panel mt-3">
    <div class="ttl">งานอื่น</div>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('admin.orders.create', ['customer' => $customer->id]) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-cart-plus"></i> ขายแพ็ก/คอร์ส
      </a>
      <a href="{{ route('admin.sessions.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-calendar-plus"></i> จองคลาสให้ลูกค้า
      </a>
      <a href="{{ route('admin.bookings.index', ['q' => $customer->code]) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-check2-square"></i> การจองของลูกค้า
      </a>
      <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-snow"></i> ฟรีซแพ็ก / แก้ข้อมูล
      </a>
    </div>
  </div>

  {{-- ── ประวัติล่าสุด ───────────────────────────── --}}
  @if($recent->isNotEmpty())
    <div class="card-panel mt-3">
      <div class="ttl">ความเคลื่อนไหวเครดิตล่าสุด</div>
      <div class="table-wrap">
        <table class="table table-sm align-middle mb-0">
          <tbody>
            @foreach($recent as $tx)
              <tr>
                <td class="small text-secondary" style="width:1%;white-space:nowrap;">
                  {{ $tx->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="num-cell" style="width:1%;white-space:nowrap;">
                  @if($tx->amount > 0)
                    <span style="color:var(--ok);font-weight:600;">+{{ rtrim(rtrim(number_format($tx->amount, 2), '0'), '.') }}</span>
                  @elseif($tx->amount < 0)
                    <span style="color:var(--danger);font-weight:600;">{{ rtrim(rtrim(number_format($tx->amount, 2), '0'), '.') }}</span>
                  @else
                    <span class="text-secondary">0</span>
                  @endif
                </td>
                <td class="small">{{ $tx->reason_th }}</td>
                <td class="small text-secondary text-end" style="width:1%;white-space:nowrap;">
                  {{ $tx->user?->name ?? 'ระบบ' }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
@endif
@endsection

@push('scripts')
<script>
  // สลับโหมดหักเครดิต: ผูกกับคลาสจริง หรือปรับเครดิตล้วนๆ
  document.querySelectorAll('.mode-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var walkin = btn.dataset.mode === 'walkin';

      document.querySelectorAll('.mode-btn').forEach(function (b) {
        var on = b === btn;
        b.classList.toggle('btn-primary', on);
        b.classList.toggle('btn-outline-secondary', !on);
      });

      var mw = document.getElementById('modeWalkin');
      var ma = document.getElementById('modeAdjust');
      if (mw) mw.style.display = walkin ? '' : 'none';
      if (ma) ma.style.display = walkin ? 'none' : '';
    });
  });

  // ปุ่มเลือกเหตุผล/จำนวน: เขียนค่าลง hidden input แล้วไฮไลต์ปุ่มที่เลือก
  document.querySelectorAll('.preset-btn, .amount-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var target = document.getElementById(btn.dataset.target);
      if (!target) return;

      target.value = btn.dataset.value;

      // ไฮไลต์เฉพาะปุ่มกลุ่มเดียวกัน (กลุ่มแยกด้วย data-target)
      var group = btn.classList.contains('preset-btn') ? '.preset-btn' : '.amount-btn';
      document.querySelectorAll(group + '[data-target="' + btn.dataset.target + '"]').forEach(function (b) {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-secondary');
      });
      btn.classList.remove('btn-outline-secondary');
      btn.classList.add('btn-primary');

      // "อื่นๆ" ต้องพิมพ์เหตุผลเอง โฟกัสช่องหมายเหตุให้เลย
      if (btn.dataset.needsNote) {
        var note = btn.closest('form').querySelector('input[name="note"]');
        if (note) {
          note.required = true;
          note.placeholder = 'ระบุเหตุผล (บังคับ)';
          note.focus();
        }
      } else if (btn.classList.contains('preset-btn')) {
        var n = btn.closest('form').querySelector('input[name="note"]');
        if (n) { n.required = false; n.placeholder = 'ไม่บังคับ'; }
      }
    });
  });

  // พิมพ์จำนวนเองแล้วปุ่มลัดต้องเลิกไฮไลต์ ไม่งั้นเข้าใจผิดว่าเลือกอยู่
  ['deductAmount', 'addAmount'].forEach(function (id) {
    var input = document.getElementById(id);
    if (!input) return;

    input.addEventListener('input', function () {
      document.querySelectorAll('.amount-btn[data-target="' + id + '"]').forEach(function (b) {
        var match = b.dataset.value === input.value;
        b.classList.toggle('btn-primary', match);
        b.classList.toggle('btn-outline-secondary', !match);
      });
    });
  });
</script>
@endpush

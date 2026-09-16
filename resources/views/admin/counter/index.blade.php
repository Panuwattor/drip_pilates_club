@extends('admin.layouts.app')
@section('title', __t('เคาน์เตอร์', 'Front Desk'))

@section('content')

{{-- ── ค้นหาลูกค้า ───────────────────────────────── --}}
<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
  <input class="form-control" name="q" value="{{ $search }}" autofocus
         placeholder="{{ __t('ค้นหาลูกค้า — ชื่อ ชื่อเล่น เบอร์โทร หรือรหัสสมาชิก', 'Search customers — name, nickname, phone or member code') }}"
         style="max-width:420px;font-size:1rem;">
  <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> {{ __t('ค้นหา', 'Search') }}</button>
  @if($search !== '' || $customer)
    <a href="{{ route('admin.counter.index') }}" class="btn btn-outline-secondary">{{ __t('เริ่มใหม่', 'Start over') }}</a>
  @endif
</form>

{{-- ── ผลค้นหาหลายคน ให้เลือก ───────────────────── --}}
@if($customers->isNotEmpty())
  <div class="card-panel mb-3">
    <div class="ttl">{{ __t('พบ', 'Found') }} {{ $customers->count() }} — {{ __t('เลือกลูกค้า', 'select a customer') }}</div>
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
              <td class="small text-secondary">{{ $c->homeBranch?->short_name ?? $c->homeBranch?->name ?? '—' }}</td>
              <td class="text-center num-cell">
                @if($c->hasUnlimited())
                  <span class="badge-soft badge-accent">{{ __t('เหมาจ่าย', 'Unlimited') }}</span>
                @else
                  {{ $c->totalCredits() }} {{ __t('เครดิต', 'credits') }}
                @endif
              </td>
              <td class="text-end" style="width:1%;">
                <a href="{{ route('admin.counter.index', ['customer' => $c->id]) }}" class="btn btn-sm btn-primary">
                  {{ __t('เลือก', 'Select') }} <i class="bi bi-chevron-right"></i>
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
    <div class="empty-note"><i class="bi bi-person-x"></i>{{ __t('ไม่พบลูกค้าที่ตรงกับ', 'No customers match') }} "{{ $search }}"</div>
  @else
    <div class="empty-note">
      <i class="bi bi-search"></i>
      {{ __t('ค้นหาลูกค้าก่อน แล้วเลือกงานที่ต้องการ — หักเครดิต เพิ่มเครดิต หรือขายแพ็ก', 'Search for a customer first, then pick a task — deduct credits, add credits or sell a package.') }}
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
            <span class="badge-soft badge-danger">{{ $customer->status === 'banned' ? __t('ถูกระงับ', 'Banned') : __t('ไม่ใช้งาน', 'Inactive') }}</span>
          @endif
        </div>
        <div class="small text-secondary mt-1">
          <i class="bi bi-telephone"></i> {{ $customer->phone }}
          @if($customer->homeBranch)
            · <i class="bi bi-geo-alt"></i> {{ $customer->homeBranch->short_name ?? $customer->homeBranch->name }}
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
        <div class="lbl" style="font-size:.72rem;color:var(--ink-soft);">{{ __t('เครดิตคงเหลือ', 'Credits left') }}</div>
      </div>

      <div>
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-person"></i> {{ __t('โปรไฟล์เต็ม', 'Full profile') }}
        </a>
      </div>
    </div>

    @if($nearest)
      <div class="small text-secondary mt-2 pt-2" style="border-top:1px solid var(--line);">
        {{ __t('ใบที่จะถูกตัดก่อน', 'Deducted from first') }}: <strong>{{ $nearest->package->name }}</strong>
        {{ __t('เหลือ', 'left') }} {{ $nearest->credit_remaining }} · {{ __t('หมดอายุ', 'expires') }} {{ $nearest->expires_at->format('d/m/Y') }}
        @if($usablePackages->count() > 1)
          <span class="text-secondary">(+{{ $usablePackages->count() - 1 }} {{ __t('ใบ', 'more') }})</span>
        @endif
      </div>
    @elseif(! $unlimited)
      <div class="small mt-2 pt-2" style="border-top:1px solid var(--line);color:var(--danger);">
        <i class="bi bi-exclamation-triangle"></i> {{ __t('ลูกค้าไม่มีเครดิตที่ใช้ได้ — ต้องขายแพ็กก่อน', 'This customer has no usable credits — sell a package first') }}
      </div>
    @endif
  </div>

  {{-- ── ปุ่มลัดงานหน้าร้าน ───────────────────────── --}}
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card-panel h-100">
        <div class="ttl"><i class="bi bi-dash-circle"></i> {{ __t('หักเครดิต', 'Deduct credits') }}</div>

        @if($unlimited)
          <div class="empty-note py-3">{{ __t('ลูกค้าถือแพ็กเหมาจ่าย ไม่ต้องหักเครดิต', 'This customer has an unlimited package — no credits to deduct') }}</div>
        @elseif($credits <= 0)
          <div class="empty-note py-3">{{ __t('ไม่มีเครดิตให้หัก', 'No credits to deduct') }}</div>
        @else
          {{-- เลือกโหมด: ผูกกับคลาสจริง หรือปรับเครดิตล้วนๆ --}}
          <div class="btn-group w-100 mb-3" role="group">
            <button type="button" class="btn btn-sm btn-primary mode-btn" data-mode="walkin">
              <i class="bi bi-person-walking"></i> {{ __t('มาเรียนคลาสนี้', 'Attending a class') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary mode-btn" data-mode="adjust">
              <i class="bi bi-sliders"></i> {{ __t('ปรับเครดิตอย่างเดียว', 'Adjust credits only') }}
            </button>
          </div>

          {{-- ── โหมด walk-in: สร้าง booking จริง นับยอดคนเข้าคลาส ── --}}
          <div id="modeWalkin">
            @if($todaySessions->isEmpty())
              <div class="empty-note py-3">
                {{ __t('วันนี้ไม่มีคลาสที่ยังไม่จบ — ถ้าต้องแก้ย้อนหลังให้ใช้ "ปรับเครดิตอย่างเดียว"', 'No classes left today — use "Adjust credits only" for retroactive corrections.') }}
              </div>
            @else
              <form method="POST" action="{{ route('admin.counter.walkin', $customer) }}" id="walkinForm">
                @csrf
                <label class="form-label small">{{ __t('เลือกคลาสที่ลูกค้าเข้าเรียน', 'Select the class attended') }}</label>
                <select class="form-select form-select-sm mb-2" name="class_session_id" required>
                  @foreach($todaySessions as $s)
                    @php $full = $s->booked_count >= $s->capacity; @endphp
                    <option value="{{ $s->id }}" @if($full) data-full="1" @endif>
                      {{ $s->start_at->format('H:i') }}–{{ $s->end_at->format('H:i') }}
                      · {{ $s->classType->name }}
                      @if($s->actualTrainer()) · {{ $s->actualTrainer()->nickname ?? $s->actualTrainer()->first_name }} @endif
                      · {{ $s->booked_count }}/{{ $s->capacity }}{{ $full ? ' (' . __t('เต็ม', 'full') . ')' : '' }}
                      · {{ rtrim(rtrim(number_format((float) $s->credit_cost, 2), '0'), '.') }} {{ __t('เครดิต', 'credits') }}
                    </option>
                  @endforeach
                </select>

                @if(session('overbook_confirm'))
                  <input type="hidden" name="confirm_overbook" value="1">
                  <div class="small mb-2" style="color:var(--warn);">
                    <i class="bi bi-exclamation-triangle"></i> {{ __t('กดอีกครั้งเพื่อยืนยันรับเกินความจุ', 'Press again to confirm overbooking') }}
                  </div>
                @endif

                <button class="btn btn-danger w-100" type="submit">
                  <i class="bi bi-box-arrow-in-right"></i> {{ __t('บันทึกเข้าคลาส + เช็คอิน', 'Book into class + check in') }}
                </button>
                <div class="form-text small mt-1">
                  {{ __t('หักเครดิตตามที่คลาสนั้นกำหนด และนับเป็นคนเข้าเรียนในรายงาน', "Deducts that class's credit cost and counts as an attendance in reports.") }}
                </div>
              </form>
            @endif
          </div>

          {{-- ── โหมดปรับเครดิตล้วนๆ: ไม่มีคลาสมาเกี่ยว ── --}}
          <div id="modeAdjust" style="display:none;">
          <form method="POST" action="{{ route('admin.counter.deduct', $customer) }}" id="deductForm"
                data-confirm="{{ __t('ยืนยันหักเครดิต', 'Deduct credits from') }} {{ $customer->full_name }}?">
            @csrf
            <input type="hidden" name="preset" id="deductPreset" value="correction_deduct">

            <div class="small text-secondary mb-2">{{ __t('เลือกเหตุผล', 'Choose a reason') }}</div>
            <div class="d-flex flex-wrap gap-2 mb-3">
              @foreach($presets as $key => [$label, $dir, $type])
                {{-- walk_in/manual_class ย้ายไปโหมดบนแล้ว เพราะต้องผูกกับคลาสจริง --}}
                @if($dir === -1 && ! in_array($key, ['walk_in', 'manual_class'], true))
                  <button type="button" class="btn btn-sm preset-btn {{ $key === 'correction_deduct' ? 'btn-primary' : 'btn-outline-secondary' }}"
                          data-target="deductPreset" data-value="{{ $key }}">{{ $label }}</button>
                @endif
              @endforeach
              <button type="button" class="btn btn-sm btn-outline-secondary preset-btn"
                      data-target="deductPreset" data-value="other" data-needs-note="1">{{ __t('อื่นๆ', 'Other') }}</button>
            </div>

            <div class="row g-2 align-items-end">
              <div class="col-auto">
                <label class="form-label small">{{ __t('จำนวน', 'Amount') }}</label>
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
                <label class="form-label small">{{ __t('หมายเหตุ', 'Note') }} <span class="text-secondary" id="deductNoteHint"></span></label>
                <input class="form-control form-control-sm" name="note" maxlength="200"
                       placeholder="{{ __t('ไม่บังคับ', 'optional') }}" value="{{ old('note') }}">
              </div>
            </div>

            <button class="btn btn-danger w-100 mt-3" type="submit">
              <i class="bi bi-dash-lg"></i> {{ __t('หักเครดิต', 'Deduct credits') }}
            </button>
            <div class="form-text small mt-1">{{ __t('ตัดจากแพ็กที่ใกล้หมดอายุก่อนอัตโนมัติ · ไม่นับเป็นคนเข้าคลาส', 'Taken from the package expiring soonest · does not count as attendance') }}</div>
          </form>
          </div>
        @endif
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card-panel h-100">
        <div class="ttl"><i class="bi bi-plus-circle"></i> {{ __t('เพิ่มเครดิต', 'Add credits') }}</div>

        @if($customer->packages->where('type', '!=', 'unlimited')->isEmpty())
          <div class="empty-note py-3">
            {{ __t('ลูกค้ายังไม่มีแพ็ก — ต้องขายแพ็กก่อนถึงจะเพิ่มเครดิตได้', 'This customer has no package yet — sell one before adding credits.') }}
            <div class="mt-2">
              <a href="{{ route('admin.orders.create', ['customer' => $customer->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-cart-plus"></i> {{ __t('ขายแพ็กให้ลูกค้า', 'Sell a package') }}
              </a>
            </div>
          </div>
        @else
          <form method="POST" action="{{ route('admin.counter.add', $customer) }}">
            @csrf
            <input type="hidden" name="preset" id="addPreset" value="compensate_class">

            <div class="small text-secondary mb-2">{{ __t('เลือกเหตุผล', 'Choose a reason') }}</div>
            <div class="d-flex flex-wrap gap-2 mb-3">
              @foreach($presets as $key => [$label, $dir, $type])
                @if($dir === 1)
                  <button type="button" class="btn btn-sm preset-btn {{ $loop->first ? 'btn-primary' : 'btn-outline-secondary' }}"
                          data-target="addPreset" data-value="{{ $key }}">{{ $label }}</button>
                @endif
              @endforeach
              <button type="button" class="btn btn-sm btn-outline-secondary preset-btn"
                      data-target="addPreset" data-value="other" data-needs-note="1">{{ __t('อื่นๆ', 'Other') }}</button>
            </div>

            <div class="row g-2 align-items-end">
              <div class="col-auto">
                <label class="form-label small">{{ __t('จำนวน', 'Amount') }}</label>
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
                <label class="form-label small">{{ __t('หมายเหตุ', 'Note') }}</label>
                <input class="form-control form-control-sm" name="note" maxlength="200" placeholder="{{ __t('ไม่บังคับ', 'optional') }}">
              </div>
            </div>

            <div class="mt-2">
              <label class="form-label small">{{ __t('เติมเข้าแพ็ก', 'Add to package') }}</label>
              <select class="form-select form-select-sm" name="customer_package_id">
                <option value="">{{ __t('ใบที่ใกล้หมดอายุที่สุด (แนะนำ)', 'The one expiring soonest (recommended)') }}</option>
                @foreach($customer->packages->where('type', '!=', 'unlimited')->whereIn('status', ['active', 'used_up', 'frozen']) as $cp)
                  <option value="{{ $cp->id }}">
                    {{ $cp->package->name }} — {{ __t('เหลือ', 'left') }} {{ $cp->credit_remaining }} ({{ __t('หมดอายุ', 'expires') }} {{ $cp->expires_at->format('d/m/Y') }})
                  </option>
                @endforeach
              </select>
            </div>

            <button class="btn btn-primary w-100 mt-3" type="submit">
              <i class="bi bi-plus-lg"></i> {{ __t('เพิ่มเครดิต', 'Add credits') }}
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>

  {{-- ── งานอื่นที่ทำบ่อย ─────────────────────────── --}}
  <div class="card-panel mt-3">
    <div class="ttl">{{ __t('งานอื่น', 'Other tasks') }}</div>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('admin.orders.create', ['customer' => $customer->id]) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-cart-plus"></i> {{ __t('ขายแพ็ก/คอร์ส', 'Sell a package') }}
      </a>
      <a href="{{ route('admin.sessions.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-calendar-plus"></i> {{ __t('จองคลาสให้ลูกค้า', 'Book a class') }}
      </a>
      <a href="{{ route('admin.bookings.index', ['q' => $customer->code]) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-check2-square"></i> {{ __t('การจองของลูกค้า', 'Their bookings') }}
      </a>
      <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-snow"></i> {{ __t('ฟรีซแพ็ก / แก้ข้อมูล', 'Freeze package / edit details') }}
      </a>
    </div>
  </div>

  {{-- ── ประวัติล่าสุด ───────────────────────────── --}}
  @if($recent->isNotEmpty())
    <div class="card-panel mt-3">
      <div class="ttl">{{ __t('ความเคลื่อนไหวเครดิตล่าสุด', 'Recent credit activity') }}</div>
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
                <td class="small">{{ $tx->reason }}</td>
                <td class="small text-secondary text-end" style="width:1%;white-space:nowrap;">
                  {{ $tx->user?->name ?? __t('ระบบ', 'System') }}
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
          note.placeholder = @json(__t('ระบุเหตุผล (บังคับ)', 'Reason required'));
          note.focus();
        }
      } else if (btn.classList.contains('preset-btn')) {
        var n = btn.closest('form').querySelector('input[name="note"]');
        if (n) { n.required = false; n.placeholder = @json(__t('ไม่บังคับ', 'optional')); }
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

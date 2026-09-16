@extends('admin.layouts.app')
@section('title', $customer->full_name)

@section('topbar-actions')
  <a href="{{ route('admin.orders.create', ['customer' => $customer->id]) }}" class="btn btn-sm btn-primary">
    <i class="bi bi-cart-plus"></i> ขายแพ็กเกจ
  </a>
  <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-pencil"></i> แก้ไข
  </a>
@endsection

@section('content')
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card-panel mb-3">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div style="width:60px;height:60px;border-radius:50%;background:var(--accent-soft);color:var(--accent-deep);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex:0 0 auto;">
          <i class="bi bi-person"></i>
        </div>
        <div class="min-width-0">
          <div class="fw-bold" style="font-size:1.1rem;">{{ $customer->full_name }}</div>
          <div class="small text-secondary">{{ $customer->code }}</div>
        </div>
      </div>

      <table class="table table-sm mb-0">
        <tbody>
          <tr><td class="text-secondary small">เบอร์โทร</td><td class="text-end">{{ $customer->phone ?: '—' }}</td></tr>
          <tr><td class="text-secondary small">อีเมล</td><td class="text-end small">{{ $customer->email ?: '—' }}</td></tr>
          <tr>
            <td class="text-secondary small">LINE</td>
            <td class="text-end small">
              @if($customer->hasLineLinked())
                <span style="color:#06C755;"><i class="bi bi-check-circle-fill"></i></span>
                {{ $customer->line_display_name ?: 'ผูกแล้ว' }}
              @else
                <span class="text-secondary">ยังไม่ได้ผูก</span>
              @endif
            </td>
          </tr>
          <tr><td class="text-secondary small">สาขาประจำ</td><td class="text-end small">{{ $customer->homeBranch?->name_th ?? '—' }}</td></tr>
          <tr><td class="text-secondary small">วันเกิด</td><td class="text-end small">{{ $customer->birth_date?->format('j M Y') ?? '—' }}</td></tr>
          <tr><td class="text-secondary small">สมาชิกตั้งแต่</td><td class="text-end small">{{ $customer->created_at->format('j M Y') }}</td></tr>
        </tbody>
      </table>

      @if($customer->medical_note || $customer->is_pregnant)
        <div class="mt-2 p-2 rounded-3" style="background:var(--warn-soft);color:var(--warn);font-size:.82rem;">
          <strong><i class="bi bi-heart-pulse"></i> ข้อมูลสุขภาพ</strong>
          @if($customer->is_pregnant)<div>กำลังตั้งครรภ์</div>@endif
          @if($customer->medical_note)<div>{{ $customer->medical_note }}</div>@endif
        </div>
      @endif

      @if($customer->admin_note)
        <div class="mt-2 p-2 rounded-3 small" style="background:var(--ground);color:var(--ink-soft);">
          <strong>โน้ตภายใน</strong><div>{{ $customer->admin_note }}</div>
        </div>
      @endif
    </div>

    <div class="row g-2 mb-3">
      <div class="col-6">
        <div class="stat-tile">
          <div class="num">{{ $hasUnlimited ? '∞' : $totalCredits }}</div>
          <div class="lbl">เครดิตคงเหลือ</div>
        </div>
      </div>
      <div class="col-6">
        <div class="stat-tile">
          <div class="num">{{ $history->where('status', 'attended')->count() + $customer->bookings()->where('status','attended')->count() }}</div>
          <div class="lbl">คลาสที่เรียนแล้ว</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card-panel mb-3">
      <div class="ttl">แพ็กเกจ</div>

      @if($packages->isEmpty())
        <div class="empty-note"><i class="bi bi-ticket-perforated"></i>ยังไม่มีแพ็กเกจ</div>
      @else
        <div class="table-wrap">
          <table class="table align-middle">
            <thead>
              <tr><th>แพ็กเกจ</th><th>เครดิต</th><th>หมดอายุ</th><th>สถานะ</th><th></th></tr>
            </thead>
            <tbody>
              @foreach($packages as $cp)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $cp->package->name_th }}</div>
                    <div class="small text-secondary">{{ $cp->code }}</div>
                  </td>
                  <td>
                    @if($cp->isUnlimited())
                      <span class="badge-soft badge-accent">ไม่จำกัด</span>
                      @if($cp->max_per_day)<div class="small text-secondary">สูงสุด {{ $cp->max_per_day }}/วัน</div>@endif
                    @else
                      <span style="font-variant-numeric:tabular-nums;">
                        {{ $cp->credit_remaining }}<span class="text-secondary">/{{ $cp->credit_total }}</span>
                      </span>
                    @endif
                  </td>
                  <td class="small">
                    {{ $cp->expires_at->format('j M Y') }}
                    @php $days = $cp->daysUntilExpiry(); @endphp
                    @if($cp->status === 'active' && $days >= 0 && $days <= 14)
                      <div><span class="badge-soft {{ $days <= 3 ? 'badge-danger' : 'badge-warn' }}">เหลือ {{ $days }} วัน</span></div>
                    @endif
                  </td>
                  <td>
                    @php $pm = ['active'=>['ใช้ได้','badge-ok'],'expired'=>['หมดอายุ','badge-danger'],
                                'used_up'=>['ใช้หมด','badge-soft'],'frozen'=>['ฟรีซ','badge-warn'],
                                'cancelled'=>['ยกเลิก','badge-soft']]; @endphp
                    <span class="badge-soft {{ $pm[$cp->status][1] ?? 'badge-soft' }}">{{ $pm[$cp->status][0] ?? $cp->status }}</span>
                  </td>
                  <td class="text-end">
                    @if(in_array($cp->status, ['active', 'frozen']))
                      <form method="POST" action="{{ route('admin.customers.freeze', $cp) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary" type="submit"
                                title="{{ $cp->status === 'frozen' ? 'ยกเลิกฟรีซ' : 'ฟรีซแพ็ก' }}">
                          <i class="bi bi-{{ $cp->status === 'frozen' ? 'play' : 'pause' }}"></i>
                        </button>
                      </form>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <button class="btn btn-sm btn-outline-secondary mt-2" data-bs-toggle="modal" data-bs-target="#creditModal">
          <i class="bi bi-plus-slash-minus"></i> ปรับเครดิตเอง
        </button>
      @endif
    </div>

    <div class="card-panel mb-3">
      <div class="ttl">คลาสที่กำลังจะถึง</div>
      @if($upcoming->isEmpty())
        <div class="empty-note"><i class="bi bi-calendar3"></i>ไม่มีคลาสที่จองไว้</div>
      @else
        <div class="table-wrap">
          <table class="table align-middle">
            <tbody>
              @foreach($upcoming as $b)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $b->classSession->classType->name_th }}</div>
                    <div class="small text-secondary">
                      {{ $b->classSession->start_at->locale('th')->isoFormat('ddd D MMM') }}
                      {{ $b->classSession->start_at->format('H:i') }} ·
                      {{ $b->classSession->branch->short_name_th ?? $b->classSession->branch->name_th }}
                    </div>
                  </td>
                  <td class="text-end">
                    @include('admin.partials.booking-status', ['status' => $b->status])
                    @if($b->status === 'waitlisted')
                      <div class="small text-secondary">คิวที่ {{ $b->waitlist_position }}</div>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    <div class="row g-3">
      <div class="col-md-6">
        <div class="card-panel h-100">
          <div class="ttl">ประวัติการเรียน</div>
          @if($history->isEmpty())
            <div class="empty-note"><i class="bi bi-clock-history"></i>ยังไม่มีประวัติ</div>
          @else
            <div class="table-wrap" style="max-height:320px;overflow-y:auto;">
              <table class="table table-sm">
                <tbody>
                  @foreach($history as $b)
                    <tr>
                      <td class="small">
                        {{ $b->classSession->classType->name_th }}
                        <div class="text-secondary">{{ $b->classSession->start_at->format('d/m/Y H:i') }}</div>
                      </td>
                      <td class="text-end">@include('admin.partials.booking-status', ['status' => $b->status])</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>

      <div class="col-md-6">
        <div class="card-panel h-100">
          <div class="ttl">ประวัติเครดิต</div>
          @if($credits->isEmpty())
            <div class="empty-note"><i class="bi bi-inbox"></i>ยังไม่มีรายการ</div>
          @else
            <div class="table-wrap" style="max-height:320px;overflow-y:auto;">
              <table class="table table-sm">
                <tbody>
                  @foreach($credits as $tx)
                    <tr>
                      <td class="small">
                        {{ $tx->reason_th }}
                        <div class="text-secondary">
                          {{ $tx->created_at->format('d/m/Y H:i') }}
                          @if($tx->user)· {{ $tx->user->name }}@endif
                        </div>
                      </td>
                      <td class="text-end" style="white-space:nowrap;font-variant-numeric:tabular-nums;">
                        @if($tx->amount > 0)
                          <span class="text-success">+{{ rtrim(rtrim(number_format($tx->amount, 2), '0'), '.') }}</span>
                        @elseif($tx->amount < 0)
                          <span class="text-danger">{{ rtrim(rtrim(number_format($tx->amount, 2), '0'), '.') }}</span>
                        @else
                          <span class="text-secondary">—</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ปรับเครดิต --}}
<div class="modal fade" id="creditModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('admin.customers.credit', $customer) }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title" style="font-size:1rem;">ปรับเครดิตเอง</h5>
        <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">แพ็กเกจ <span class="text-danger">*</span></label>
          <select class="form-select" name="customer_package_id" required>
            @foreach($packages->where('status', '!=', 'cancelled')->where('type', '!=', 'unlimited') as $cp)
              <option value="{{ $cp->id }}">
                {{ $cp->package->name_th }} — เหลือ {{ $cp->credit_remaining }} (หมดอายุ {{ $cp->expires_at->format('d/m/Y') }})
              </option>
            @endforeach
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">จำนวน <span class="text-danger">*</span></label>
          <input class="form-control" type="number" name="amount" required placeholder="ใส่บวกเพื่อเพิ่ม ใส่ลบเพื่อหัก">
          <div class="form-text small">เช่น 2 = เพิ่ม 2 เครดิต, -1 = หัก 1 เครดิต</div>
        </div>
        <div class="mb-2">
          <label class="form-label">เหตุผล <span class="text-danger">*</span></label>
          <input class="form-control" name="reason" required placeholder="เช่น ชดเชยคลาสที่ครูยกเลิก">
          <div class="form-text small">บันทึกไว้เป็นหลักฐาน ตรวจสอบย้อนหลังได้</div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">ยกเลิก</button>
        <button class="btn btn-primary" type="submit">บันทึก</button>
      </div>
    </form>
  </div>
</div>
@endsection

<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Services\BookingService;
use App\Services\CreditAdjuster;
use Illuminate\Http\Request;

/**
 * หน้าเคาน์เตอร์ — งานหน้าร้านที่ทำบ่อยที่สุดรวมไว้ที่เดียว
 *
 * ออกแบบให้จบใน 2 ขั้น: ค้นหาลูกค้า -> กดปุ่มที่ตรงกับสถานการณ์
 * ไม่ต้องเข้าหน้าโปรไฟล์ลูกค้าแล้วหาปุ่มเอง และไม่ต้องพิมพ์เลขติดลบเอง
 */
class CounterController extends Controller
{
    public function __construct(
        private CreditAdjuster $credits,
        private BookingService $bookings,
    ) {}

    public function index(Request $request)
    {
        $search = trim((string) $request->input('q'));
        $customers = collect();
        $customer = null;

        if ($request->filled('customer')) {
            $customer = Customer::with([
                'homeBranch',
                'packages' => fn ($q) => $q->with('package')->orderBy('expires_at'),
            ])->find($request->input('customer'));
        }

        // ค้นหาแบบกว้าง พนักงานจำได้แค่ชื่อเล่นหรือเลขท้ายเบอร์ก็เจอ
        if ($search !== '' && ! $customer) {
            $customers = Customer::with('homeBranch')
                ->where(fn ($q) => $q
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('nickname', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"))
                ->orderBy('first_name')
                ->limit(20)
                ->get();

            // เจอคนเดียวก็เปิดให้เลย ไม่ต้องกดซ้ำ
            if ($customers->count() === 1) {
                $customer = $customers->first()->load([
                    'homeBranch',
                    'packages' => fn ($q) => $q->with('package')->orderBy('expires_at'),
                ]);
                $customers = collect();
            }
        }

        return view('admin.counter.index', [
            'search' => $search,
            'customers' => $customers,
            'customer' => $customer,
            'presets' => CreditAdjuster::PRESETS,
            'recent' => $customer
                ? CreditTransaction::where('customer_id', $customer->id)
                    ->with('user')
                    ->latest('id')
                    ->limit(10)
                    ->get()
                : collect(),
            'usablePackages' => $customer
                ? $customer->packages->where('status', 'active')
                    ->where('type', '!=', 'unlimited')
                    ->filter(fn ($p) => $p->expires_at->gte(now()->startOfDay()))
                : collect(),
            // คลาสวันนี้ที่ยังไม่จบ ให้เลือกตอนบันทึก walk-in
            // เอาที่ยังไม่จบเท่านั้น กันกดผิดไปลงคลาสเมื่อวาน
            'todaySessions' => $customer
                ? ClassSession::with(['classType', 'trainer', 'room'])
                    ->where('status', 'scheduled')
                    ->whereDate('start_at', now()->toDateString())
                    ->where('end_at', '>', now())
                    ->orderBy('start_at')
                    ->get()
                : collect(),
        ]);
    }

    /**
     * ลูกค้า walk-in มาเรียนสด — สร้าง booking จริงแล้วเช็คอินให้เลย
     *
     * ต่างจาก deduct() ตรงที่อันนี้ผูกกับคลาส ทำให้ยอดคนเข้าเรียน รายงานครู
     * และเครดิตที่หัก (ตาม credit_cost ของคลาสนั้น) ตรงกับความจริงทั้งหมด
     */
    public function walkIn(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'class_session_id' => ['required', 'integer', 'exists:class_sessions,id'],
            // พนักงานเห็นคำเตือนว่าคลาสเต็มแล้วกดยืนยันมา ถึงจะรับเกินความจุได้
            'confirm_overbook' => ['nullable', 'boolean'],
        ], [], ['class_session_id' => __t('คลาส', 'class')]);

        $session = ClassSession::with('classType')->findOrFail($data['class_session_id']);

        $isFull = $session->booked_count >= $session->capacity;

        if ($isFull && ! $request->boolean('confirm_overbook')) {
            // ใช้ session key แยก ไม่ให้หน้า Blade ต้องเดาจากข้อความ (ข้อความแปลตามภาษาได้)
            return back()
                ->with('overbook_confirm', $session->id)
                ->with('error', __t(
                    "คลาส {$session->classType->name} เต็มแล้ว ({$session->booked_count}/{$session->capacity}) — กดยืนยันอีกครั้งถ้าต้องการรับเพิ่ม",
                    "{$session->classType->name} is full ({$session->booked_count}/{$session->capacity}) — press again to confirm overbooking"
                ));
        }

        try {
            $booking = $this->bookings->book($customer, $session, 'walk_in', auth()->id());
            // มาเรียนสดอยู่แล้ว เช็คอินให้เลย ไม่ต้องให้พนักงานกดซ้ำอีกหน้า
            $this->bookings->checkIn($booking, auth()->id());
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage());
        }

        $credit = (float) $booking->credit_used;
        $remaining = $customer->fresh()->totalCredits();
        $detail = $credit > 0
            ? __t("หักเครดิต {$credit} เหลือ {$remaining}", "{$credit} credits deducted, {$remaining} left")
            : __t('แพ็กเหมาจ่าย ไม่หักเครดิต', 'Unlimited package — no credits deducted');

        return back()->with('status', __t(
            "บันทึก {$customer->full_name} เข้าคลาส {$session->classType->name} แล้ว — {$detail}",
            "{$customer->full_name} booked into {$session->classType->name} — {$detail}"
        ));
    }

    /** หักเครดิต — ตัดจากแพ็กที่ใกล้หมดอายุก่อนอัตโนมัติ */
    public function deduct(Request $request, Customer $customer)
    {
        $data = $this->validateAdjustment($request);

        try {
            $result = $this->credits->deduct(
                $customer,
                $data['amount'],
                $data['reason'],
                auth()->id(),
                $data['type'],
            );
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage());
        }

        $packageCount = count($result['packages']);
        $detail = $packageCount > 1
            ? __t(" (ตัดจาก {$packageCount} แพ็ก)", " (across {$packageCount} packages)")
            : '';
        $remaining = $customer->fresh()->totalCredits();

        return back()->with('status', __t(
            "หักเครดิต {$data['amount']} ของ {$customer->full_name} แล้ว{$detail} เหลือ {$remaining}",
            "Deducted {$data['amount']} credits from {$customer->full_name}{$detail} — {$remaining} left"
        ));
    }

    /** เพิ่มเครดิต — ชดเชย คืนเครดิต หรือแก้ยอดที่ผิด */
    public function add(Request $request, Customer $customer)
    {
        $data = $this->validateAdjustment($request);

        $packageId = $request->input('customer_package_id');

        if ($packageId) {
            // กันเติมเครดิตเข้าแพ็กของลูกค้าคนอื่น
            $owned = CustomerPackage::where('customer_id', $customer->id)
                ->whereKey($packageId)
                ->exists();

            if (! $owned) {
                return back()->with('error', __t('แพ็กที่เลือกไม่ใช่ของลูกค้าคนนี้', 'That package does not belong to this customer'));
            }
        }

        try {
            $this->credits->add(
                $customer,
                $data['amount'],
                $data['reason'],
                auth()->id(),
                $data['type'],
                $packageId ? (int) $packageId : null,
            );
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage());
        }

        $total = $customer->fresh()->totalCredits();

        return back()->with('status', __t(
            "เพิ่มเครดิต {$data['amount']} ให้ {$customer->full_name} แล้ว รวมเป็น {$total}",
            "Added {$data['amount']} credits for {$customer->full_name} — {$total} total"
        ));
    }

    /**
     * ตรวจข้อมูลที่ส่งมา แล้วประกอบเหตุผลกับประเภทที่จะบันทึก
     *
     * @return array{amount: float, reason: string, type: string}
     */
    private function validateAdjustment(Request $request): array
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.5', 'max:99'],
            'preset' => ['required', 'string', 'in:' . implode(',', array_keys(CreditAdjuster::PRESETS))],
            // เลือก "อื่นๆ" ต้องพิมพ์เหตุผลเอง ไม่งั้นประวัติจะอ่านไม่รู้เรื่อง
            'note' => ['nullable', 'required_if:preset,other', 'string', 'max:200'],
        ], [
            'note.required_if' => __t('เลือก "อื่นๆ" ต้องระบุเหตุผลด้วย', 'A reason is required when you choose "Other"'),
        ], [
            'amount' => __t('จำนวนเครดิต', 'credit amount'),
            'preset' => __t('เหตุผล', 'reason'),
            'note' => __t('หมายเหตุ', 'note'),
        ]);

        $label = CreditAdjuster::presetLabel($data['preset']);
        $note = trim((string) ($data['note'] ?? ''));

        $reason = $data['preset'] === 'other'
            ? $note
            : ($note !== '' ? "{$label} — {$note}" : $label);

        return [
            'amount' => (float) $data['amount'],
            'reason' => $reason,
            'type' => CreditAdjuster::presetType($data['preset']),
        ];
    }
}

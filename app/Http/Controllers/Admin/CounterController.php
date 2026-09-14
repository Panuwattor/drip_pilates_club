<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\CustomerPackage;
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
    public function __construct(private CreditAdjuster $credits) {}

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
        ]);
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
            return back()->with('error', $e->localizedMessage('th'));
        }

        $detail = count($result['packages']) > 1
            ? ' (ตัดจาก ' . count($result['packages']) . ' แพ็ก)'
            : '';

        return back()->with('status',
            "หักเครดิต {$data['amount']} ของ {$customer->full_name} แล้ว{$detail} เหลือ {$customer->fresh()->totalCredits()}");
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
                return back()->with('error', 'แพ็กที่เลือกไม่ใช่ของลูกค้าคนนี้');
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
            return back()->with('error', $e->localizedMessage('th'));
        }

        return back()->with('status',
            "เพิ่มเครดิต {$data['amount']} ให้ {$customer->full_name} แล้ว รวมเป็น {$customer->fresh()->totalCredits()}");
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
            'note.required_if' => 'เลือก "อื่นๆ" ต้องระบุเหตุผลด้วย',
        ], [
            'amount' => 'จำนวนเครดิต',
            'preset' => 'เหตุผล',
            'note' => 'หมายเหตุ',
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('homeBranch')->withCount([
            'bookings as upcoming_count' => fn ($q) => $q
                ->where('status', 'confirmed')
                ->whereHas('classSession', fn ($s) => $s->where('start_at', '>', now())),
        ]);

        if ($search = $request->input('q')) {
            $query->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('nickname', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // ใช้กับช่องค้นหาลูกค้าในหน้าจองแทน
        if ($request->expectsJson()) {
            return response()->json([
                'customers' => $query->take(15)->get()->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->full_name,
                    'phone' => $c->phone,
                    'credits' => $c->hasUnlimited() ? 'เหมาจ่าย' : $c->totalCredits(),
                ]),
            ]);
        }

        $customers = $query->latest('id')->paginate(25)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.form', [
            'customer' => new Customer,
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] = $this->nextCode();

        // แอดมินกรอกชื่อ+เบอร์ให้แล้ว ถือว่าข้อมูลครบ ลูกค้าไม่ต้องกรอกซ้ำตอนผูก LINE
        $data['profile_completed_at'] = now();

        if (! empty($data['password'])) {
            $data['password'] = $data['password'];
        } else {
            unset($data['password']);
        }

        $customer = Customer::create($data);

        return redirect()->route('admin.customers.show', $customer)
            ->with('status', 'เพิ่มลูกค้าเรียบร้อยแล้ว รหัสสมาชิก ' . $customer->code);
    }

    public function show(Customer $customer)
    {
        $customer->load('homeBranch');

        $packages = $customer->packages()->with('package')
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', ['active'])
            ->orderBy('expires_at')
            ->get();

        $upcoming = $customer->bookings()
            ->with(['classSession.classType', 'classSession.branch'])
            ->whereIn('status', ['confirmed', 'waitlisted'])
            ->whereHas('classSession', fn ($q) => $q->where('start_at', '>', now()))
            ->get()
            ->sortBy(fn ($b) => $b->classSession->start_at);

        $history = $customer->bookings()
            ->with(['classSession.classType', 'classSession.branch'])
            ->whereIn('status', ['attended', 'no_show', 'cancelled', 'late_cancelled'])
            ->latest('id')
            ->take(20)
            ->get();

        $credits = $customer->creditTransactions()
            ->with('user')
            ->latest('id')
            ->take(30)
            ->get();

        return view('admin.customers.show', [
            'customer' => $customer,
            'packages' => $packages,
            'upcoming' => $upcoming,
            'history' => $history,
            'credits' => $credits,
            'availablePackages' => Package::active()->orderBy('sort_order')->get(),
            'totalCredits' => $customer->totalCredits(),
            'hasUnlimited' => $customer->hasUnlimited(),
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.form', [
            'customer' => $customer,
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $this->validated($request, $customer);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $customer->update($data);

        return back()->with('status', 'บันทึกข้อมูลลูกค้าแล้ว');
    }

    /** แอดมินปรับเครดิตเอง ต้องระบุเหตุผลเสมอ */
    public function adjustCredit(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'customer_package_id' => ['required', 'exists:customer_packages,id'],
            'amount' => ['required', 'integer', 'not_in:0', 'between:-99,99'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $package = CustomerPackage::where('customer_id', $customer->id)
            ->findOrFail($data['customer_package_id']);

        if ($package->isUnlimited()) {
            return back()->with('error', 'แพ็กเหมาจ่ายไม่มีเครดิตให้ปรับ');
        }

        $newRemaining = $package->credit_remaining + $data['amount'];

        if ($newRemaining < 0) {
            return back()->with('error', 'หักเครดิตเกินจำนวนที่เหลืออยู่ไม่ได้');
        }

        DB::transaction(function () use ($package, $customer, $data, $newRemaining) {
            $package->update([
                'credit_remaining' => $newRemaining,
                'credit_used' => max(0, $package->credit_used - $data['amount']),
                'status' => $newRemaining > 0 ? 'active' : 'used_up',
            ]);

            CreditTransaction::create([
                'customer_id' => $customer->id,
                'customer_package_id' => $package->id,
                'amount' => $data['amount'],
                'balance_after' => $customer->totalCredits(),
                'type' => 'admin_adjust',
                'reason_th' => $data['reason'],
                'reason_en' => $data['reason'],
                'user_id' => auth()->id(),
            ]);
        });

        $verb = $data['amount'] > 0 ? 'เพิ่ม' : 'หัก';

        return back()->with('status', "{$verb}เครดิต " . abs($data['amount']) . ' เรียบร้อยแล้ว');
    }

    /** ฟรีซ/ยกเลิกฟรีซแพ็ก */
    public function toggleFreeze(Request $request, CustomerPackage $customerPackage)
    {
        if ($customerPackage->status === 'frozen') {
            $days = $customerPackage->frozen_from
                ? (int) $customerPackage->frozen_from->diffInDays(now())
                : 0;

            $customerPackage->update([
                'status' => 'active',
                // ขยายวันหมดอายุออกไปเท่าจำนวนวันที่ฟรีซ
                'expires_at' => $customerPackage->expires_at->addDays($days),
                'frozen_days_used' => $customerPackage->frozen_days_used + $days,
                'frozen_from' => null,
                'frozen_until' => null,
            ]);

            return back()->with('status', "ยกเลิกฟรีซแล้ว ขยายวันหมดอายุออกไป {$days} วัน");
        }

        $customerPackage->update([
            'status' => 'frozen',
            'frozen_from' => now()->toDateString(),
        ]);

        return back()->with('status', 'ฟรีซแพ็กเกจแล้ว ลูกค้าจะจองด้วยแพ็กนี้ไม่ได้จนกว่าจะยกเลิกฟรีซ');
    }

    private function nextCode(): string
    {
        $last = Customer::orderByDesc('id')->value('id') ?? 0;

        return 'DP-' . str_pad((string) ($last + 1), 5, '0', STR_PAD_LEFT);
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $id = $customer?->id;

        // เก็บเบอร์เป็นตัวเลขล้วนเสมอ ไม่งั้นตอนลูกค้าผูก LINE จะหาบัญชีเดิมไม่เจอ
        if ($request->filled('phone')) {
            $request->merge([
                'phone' => app(\App\Services\PhoneVerificationService::class)
                    ->normalize($request->input('phone')),
            ]);
        }

        return $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'nickname' => ['nullable', 'string', 'max:60'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone' . ($id ? ',' . $id : '')],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email' . ($id ? ',' . $id : '')],
            'password' => ['nullable', 'string', 'min:6'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:female,male,other'],
            'home_branch_id' => ['nullable', 'exists:branches,id'],
            'preferred_locale' => ['required', 'in:th,en'],
            'medical_note' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive,banned'],
            'admin_note' => ['nullable', 'string'],
        ]) + ['is_pregnant' => $request->boolean('is_pregnant')];
    }
}

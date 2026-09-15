<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Models\Package;
use App\Models\Payment;
use App\Services\PackageFulfillment;
use App\Support\MediaStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'branch', 'items', 'payments']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('q')) {
            $query->where(fn ($q) => $q
                ->where('code', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($c) => $c
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")));
        }

        $orders = $query->latest('id')->paginate(25)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'pendingCount' => Payment::where('status', 'pending')->count(),
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.orders.form', [
            'customer' => $request->input('customer')
                ? Customer::find($request->input('customer'))
                : null,
            'packages' => Package::active()->orderBy('sort_order')->get(),
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discount_note' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'mark_paid' => ['nullable', 'boolean'],
            'payment_method' => ['nullable', 'in:cash,transfer,promptpay,credit_card,other'],
            'slip_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $package = Package::findOrFail($data['package_id']);
        $customer = Customer::findOrFail($data['customer_id']);

        // แพ็กทดลองซื้อได้ครั้งเดียวต่อคน
        if ($package->once_per_customer) {
            $already = CustomerPackage::where('customer_id', $customer->id)
                ->where('package_id', $package->id)
                ->exists();

            if ($already) {
                return back()->with('error', 'ลูกค้าคนนี้เคยซื้อแพ็กทดลองไปแล้ว ซื้อซ้ำไม่ได้');
            }
        }

        $qty = (int) $data['quantity'];
        $subtotal = $package->price * $qty;
        $discount = (float) ($data['discount'] ?? 0);
        $total = max(0, $subtotal - $discount);

        $order = DB::transaction(function () use ($data, $package, $customer, $qty, $subtotal, $discount, $total, $request) {
            $order = Order::create([
                'code' => $this->nextOrderCode(),
                'customer_id' => $customer->id,
                'branch_id' => $data['branch_id'] ?? $customer->home_branch_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_note' => $data['discount_note'] ?? null,
                'total' => $total,
                'status' => 'pending',
                'user_id' => auth()->id(),
                'note' => $data['note'] ?? null,
            ]);

            $order->items()->create([
                'package_id' => $package->id,
                'name_th_snapshot' => $package->name_th,
                'name_en_snapshot' => $package->name_en,
                'unit_price' => $package->price,
                'quantity' => $qty,
                'subtotal' => $subtotal,
            ]);

            // ชำระเงินสดหน้าร้าน ยืนยันได้เลย
            if ($request->boolean('mark_paid')) {
                // แนบสลิป/หลักฐานได้ ไม่บังคับ — กันแอดมินเปิดบิลจ่ายแล้วลอยๆ
                $slip = $request->hasFile('slip_image')
                    ? MediaStorage::store($request->file('slip_image'), 'slips')
                    : null;

                $order->payments()->create([
                    'amount' => $total,
                    'method' => $data['payment_method'] ?? 'cash',
                    'slip_image' => $slip,
                    'paid_at' => now(),
                    'status' => 'verified',
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);

                app(PackageFulfillment::class)->fulfill($order, auth()->id());
            }

            return $order;
        });

        return redirect()->route('admin.orders.show', $order)
            ->with('status', 'สร้างคำสั่งซื้อเรียบร้อยแล้ว');
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'branch', 'items.package', 'payments.verifiedBy', 'customerPackages', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    /** ยืนยันสลิปโอนเงิน แล้วออกแพ็กให้ลูกค้าอัตโนมัติ */
    public function verifyPayment(Request $request, Payment $payment)
    {
        if ($payment->status === 'verified') {
            return back()->with('error', 'รายการนี้ยืนยันไปแล้ว');
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'paid_at' => $payment->paid_at ?? now(),
            ]);

            app(PackageFulfillment::class)->fulfill($payment->order, auth()->id());
        });

        return back()->with('status', 'ยืนยันการชำระเงินและออกแพ็กเกจให้ลูกค้าแล้ว');
    }

    public function rejectPayment(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'max:255'],
        ]);

        $payment->update([
            'status' => 'rejected',
            'reject_reason' => $data['reject_reason'],
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('status', 'ปฏิเสธรายการชำระเงินแล้ว');
    }

    public function addPayment(Request $request, Order $order)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'in:cash,transfer,promptpay,credit_card,other'],
            'reference' => ['nullable', 'string', 'max:100'],
            'slip_image' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('slip_image')) {
            $data['slip_image'] = MediaStorage::store($request->file('slip_image'), 'slips');
        }

        $order->payments()->create($data + [
            'status' => 'pending',
            'paid_at' => now(),
        ]);

        return back()->with('status', 'บันทึกรายการชำระเงินแล้ว รอยืนยัน');
    }

    public function cancel(Order $order)
    {
        if ($order->status === 'paid') {
            return back()->with('error', 'คำสั่งซื้อที่ชำระแล้วยกเลิกไม่ได้ ให้ทำรายการคืนเงินแทน');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('status', 'ยกเลิกคำสั่งซื้อแล้ว');
    }

    private function nextOrderCode(): string
    {
        $prefix = 'ORD-' . now()->format('ym');
        $count = Order::where('code', 'like', $prefix . '%')->count();

        return $prefix . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }
}

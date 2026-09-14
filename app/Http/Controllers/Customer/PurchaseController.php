<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Models\Package;
use App\Models\Setting;
use App\Support\MediaStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * โฟลว์ซื้อแพ็กของลูกค้า:
 *   เลือกแพ็ก -> ยืนยันคำสั่งซื้อ -> โอนเงินตามบัญชีที่โชว์ -> แนบสลิป -> รอแอดมินอนุมัติ
 * แอดมินกดอนุมัติที่ /admin/orders แล้วระบบออกแพ็กและเพิ่มเครดิตให้อัตโนมัติ
 */
class PurchaseController extends Controller
{
    /** รายการแพ็กที่ซื้อได้ */
    public function index()
    {
        $customer = auth('customer')->user();

        $packages = Package::active()->public()
            ->with('classTypes')
            ->orderBy('sort_order')
            ->get();

        // แพ็กทดลองที่เคยซื้อไปแล้ว ซื้อซ้ำไม่ได้ ต้องบอกลูกค้าตั้งแต่หน้ารายการ
        $purchasedOnceIds = CustomerPackage::where('customer_id', $customer->id)
            ->whereIn('package_id', $packages->where('once_per_customer', true)->pluck('id'))
            ->pluck('package_id')
            ->unique();

        return view('customer.purchase.index', [
            'groups' => $packages->groupBy(fn ($p) => $p->classTypes->first()?->name ?? __t('อื่นๆ', 'Others')),
            'purchasedOnceIds' => $purchasedOnceIds,
            'pendingOrders' => Order::where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->with('items')
                ->latest('id')
                ->get(),
        ]);
    }

    /** หน้ายืนยันก่อนสร้างคำสั่งซื้อ */
    public function checkout(Package $package)
    {
        $customer = auth('customer')->user();

        if ($error = $this->packageBlockReason($package, $customer)) {
            return redirect()->route('customer.purchase.index')->with('error', $error);
        }

        return view('customer.purchase.checkout', [
            'package' => $package->load('classTypes'),
            'customer' => $customer,
        ]);
    }

    /** สร้างคำสั่งซื้อสถานะ pending แล้วพาไปหน้าชำระเงิน */
    public function store(Request $request, Package $package)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $customer = auth('customer')->user();
        $qty = (int) $data['quantity'];

        if ($error = $this->packageBlockReason($package, $customer)) {
            return back()->with('error', $error);
        }

        // แพ็กทดลองซื้อได้ใบเดียว ห้ามเพิ่มจำนวน
        if ($package->once_per_customer) {
            $qty = 1;
        }

        $subtotal = (float) $package->price * $qty;

        $order = DB::transaction(function () use ($customer, $package, $qty, $subtotal) {
            $order = Order::create([
                'code' => $this->nextOrderCode(),
                'customer_id' => $customer->id,
                'branch_id' => $customer->home_branch_id,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $subtotal,
                'status' => 'pending',
                'note' => 'ลูกค้าสั่งซื้อผ่านแอป',
            ]);

            $order->items()->create([
                'package_id' => $package->id,
                'name_th_snapshot' => $package->name_th,
                'name_en_snapshot' => $package->name_en,
                'unit_price' => $package->price,
                'quantity' => $qty,
                'subtotal' => $subtotal,
            ]);

            return $order;
        });

        return redirect()->route('customer.purchase.pay', $order);
    }

    /** หน้าชำระเงิน โชว์บัญชีธนาคารและฟอร์มแนบสลิป */
    public function pay(Order $order)
    {
        $this->authorizeOrder($order);

        $order->load(['items', 'payments' => fn ($q) => $q->latest('id')]);

        return view('customer.purchase.pay', [
            'order' => $order,
            'bank' => $this->bankInfo(),
            'latestPayment' => $order->payments->first(),
        ]);
    }

    /** ลูกค้าแนบสลิป -> สร้างรายการชำระเงินสถานะ pending รอแอดมินตรวจ */
    public function uploadSlip(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        if ($order->status !== 'pending') {
            return back()->with('error', __t('คำสั่งซื้อนี้ปิดไปแล้ว', 'This order is already closed'));
        }

        // มีสลิปรออนุมัติอยู่แล้ว ส่งซ้ำได้ไม่มีประโยชน์ แถมแอดมินต้องมานั่งไล่ดูซ้ำ
        if ($order->payments()->where('status', 'pending')->exists()) {
            return back()->with('error', __t('มีสลิปรอตรวจสอบอยู่แล้ว กรุณารอแอดมินยืนยัน', 'A slip is already awaiting review'));
        }

        $data = $request->validate([
            'slip_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'method' => ['required', 'in:transfer,promptpay'],
            'reference' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['nullable', 'date', 'before_or_equal:now'],
        ]);

        $order->payments()->create([
            'amount' => $order->total,
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'slip_image' => MediaStorage::store($request->file('slip_image'), 'slips'),
            'paid_at' => $data['paid_at'] ?? now(),
            'status' => 'pending',
        ]);

        return redirect()->route('customer.purchase.pay', $order)
            ->with('status', __t('ส่งสลิปแล้ว รอแอดมินตรวจสอบ', 'Slip submitted. Waiting for admin approval.'));
    }

    /** ประวัติคำสั่งซื้อของลูกค้า */
    public function orders()
    {
        $orders = Order::where('customer_id', auth('customer')->id())
            ->with(['items', 'payments'])
            ->latest('id')
            ->paginate(20);

        return view('customer.purchase.orders', compact('orders'));
    }

    /** ลูกค้ายกเลิกคำสั่งซื้อที่ยังไม่จ่าย */
    public function cancel(Order $order)
    {
        $this->authorizeOrder($order);

        if ($order->status !== 'pending') {
            return back()->with('error', __t('ยกเลิกไม่ได้', 'Cannot cancel this order'));
        }

        if ($order->payments()->where('status', 'pending')->exists()) {
            return back()->with('error', __t('ส่งสลิปแล้ว ยกเลิกเองไม่ได้ กรุณาติดต่อแอดมิน', 'Slip submitted. Please contact admin to cancel.'));
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->route('customer.purchase.index')
            ->with('status', __t('ยกเลิกคำสั่งซื้อแล้ว', 'Order cancelled'));
    }

    /** เหตุผลที่ซื้อแพ็กนี้ไม่ได้ คืน null = ซื้อได้ */
    private function packageBlockReason(Package $package, $customer): ?string
    {
        if (! $package->is_active || ! $package->is_public) {
            return __t('แพ็กเกจนี้ไม่เปิดขายแล้ว', 'This package is no longer available');
        }

        if ($package->once_per_customer) {
            $already = CustomerPackage::where('customer_id', $customer->id)
                ->where('package_id', $package->id)
                ->exists();

            if ($already) {
                return __t('แพ็กทดลองซื้อได้คนละครั้งเดียว', 'Trial package can only be purchased once');
            }
        }

        return null;
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->customer_id === auth('customer')->id(), 403);
    }

    /** ข้อมูลบัญชีรับเงินจากตั้งค่าระบบ */
    private function bankInfo(): array
    {
        return [
            'bank' => Setting::get('payment_bank_name'),
            'account_name' => Setting::get('payment_bank_account_name'),
            'account_no' => Setting::get('payment_bank_account_no'),
            'promptpay' => Setting::get('payment_promptpay_id'),
            'qr_image' => Setting::get('payment_qr_image'),
            'note' => app()->getLocale() === 'en'
                ? Setting::get('payment_note_en')
                : Setting::get('payment_note_th'),
        ];
    }

    private function nextOrderCode(): string
    {
        $prefix = 'ORD-' . now()->format('ym');
        $count = Order::where('code', 'like', $prefix . '%')->count();

        return $prefix . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }
}

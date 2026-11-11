<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Services\BookingService;
use App\Services\CreditAdjuster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * อายุแพ็กเริ่มนับจากคลาสแรกที่ใช้ ไม่ใช่วันที่ซื้อ
 */
class PackageExpiryFromFirstClassTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->artisan('classes:generate', ['--days' => 30]);

        $this->service = app(BookingService::class);
    }

    private function customer(): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create([
            'code' => 'EX-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'first_name' => 'อายุแพ็ก',
            'last_name' => (string) $n,
            'phone' => '09' . str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            'status' => 'active',
        ]);
    }

    /** แพ็กที่ซื้อแล้วแต่ยังไม่เคยใช้ แบบที่ PackageFulfillment ออกให้ */
    private function unstartedPackage(Customer $customer, array $overrides = []): CustomerPackage
    {
        $package = Package::where('code', 'trio-10')->firstOrFail();

        return CustomerPackage::create(array_merge([
            'code' => 'CP-' . uniqid(),
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'type' => $package->type,
            'purchased_at' => now(),
            'starts_at' => null,
            'expires_at' => null,
            'valid_days' => 30,
            'credit_total' => 10,
            'credit_used' => 0,
            'credit_remaining' => 10,
            'status' => 'active',
        ], $overrides));
    }

    /** @return \Illuminate\Support\Collection<int, ClassSession> คลาส trio คนละวัน เรียงตามวัน */
    private function sessionsOnDifferentDays(int $count)
    {
        return ClassSession::where('start_at', '>', now()->addDays(2))
            ->whereHas('classType', fn ($q) => $q->where('code', 'trio-reformer'))
            ->orderBy('start_at')
            ->get()
            ->unique(fn ($s) => $s->start_at->toDateString())
            ->values()
            ->take($count);
    }

    public function test_unstarted_package_has_no_expiry_and_is_usable(): void
    {
        $customer = $this->customer();
        // ซื้อมานานกว่าอายุแพ็กแล้ว แต่ยังไม่เคยใช้ ต้องยังใช้ได้
        $cp = $this->unstartedPackage($customer, ['purchased_at' => now()->subDays(200)]);

        $this->assertFalse($cp->isActivated());
        $this->assertTrue($cp->isUsable());
        $this->assertNull($cp->daysUntilExpiry());
        $this->assertSame(10, $customer->totalCredits());

        $this->service->expirePackages();
        $this->assertSame('active', $cp->fresh()->status);
    }

    public function test_first_booking_starts_the_countdown_from_class_date(): void
    {
        $customer = $this->customer();
        $cp = $this->unstartedPackage($customer);
        $session = $this->sessionsOnDifferentDays(1)->first();

        $this->service->book($customer, $session);

        $cp->refresh();
        $classDay = $session->start_at->copy()->startOfDay();
        $this->assertTrue($cp->starts_at->isSameDay($classDay));
        $this->assertTrue($cp->expires_at->isSameDay($classDay->copy()->addDays(30)));
    }

    public function test_cancelling_the_only_booking_resets_the_package_to_unstarted(): void
    {
        $customer = $this->customer();
        $cp = $this->unstartedPackage($customer);
        $booking = $this->service->book($customer, $this->sessionsOnDifferentDays(1)->first());

        $this->service->cancel($booking, 'admin');

        $cp->refresh();
        $this->assertNull($cp->starts_at);
        $this->assertNull($cp->expires_at);
        $this->assertSame(10, $cp->credit_remaining);
    }

    public function test_start_follows_the_earliest_remaining_class(): void
    {
        $customer = $this->customer();
        $cp = $this->unstartedPackage($customer);
        [$early, $late] = $this->sessionsOnDifferentDays(2)->all();

        // จองคลาสที่ไกลกว่าก่อน แล้วค่อยจองคลาสที่ใกล้กว่า วันเริ่มต้องเลื่อนมาเป็นคลาสที่ใกล้กว่า
        $this->service->book($customer, $late);
        $earlyBooking = $this->service->book($customer, $early);
        $this->assertTrue($cp->fresh()->starts_at->isSameDay($early->start_at));

        // ยกเลิกคลาสที่ใกล้กว่า วันเริ่มเลื่อนกลับไปคลาสที่เหลือ
        $this->service->cancel($earlyBooking, 'admin');
        $this->assertTrue($cp->fresh()->starts_at->isSameDay($late->start_at));
    }

    public function test_started_package_is_used_before_an_unstarted_one(): void
    {
        $customer = $this->customer();
        $unstarted = $this->unstartedPackage($customer);
        $started = $this->unstartedPackage($customer, [
            'valid_days' => null, // แพ็กแบบเดิมที่นับจากวันซื้อ
            'starts_at' => now()->subDays(5)->toDateString(),
            'expires_at' => now()->addDays(60)->toDateString(),
        ]);

        $booking = $this->service->book($customer, $this->sessionsOnDifferentDays(1)->first());

        $this->assertSame($started->id, $booking->customer_package_id);
        $this->assertFalse($unstarted->fresh()->isActivated());
    }

    public function test_backfill_converts_old_packages_without_shortening_expiry(): void
    {
        $customer = $this->customer();
        $legacy = [
            'valid_days' => null,
            'purchased_at' => now()->subDays(10),
            'starts_at' => now()->subDays(10)->toDateString(),
            'expires_at' => now()->addDays(20)->toDateString(), // ซื้อแพ็ก 30 วันเมื่อ 10 วันก่อน
        ];

        $unused = $this->unstartedPackage($customer, $legacy);
        $used = $this->unstartedPackage($customer, $legacy);

        $session = $this->sessionsOnDifferentDays(1)->first();
        // จองตรงด้วยแพ็ก $used (เลี่ยงตรรกะเลือกแพ็ก)
        \App\Models\Booking::create([
            'code' => 'BK-' . uniqid(),
            'customer_id' => $customer->id,
            'class_session_id' => $session->id,
            'customer_package_id' => $used->id,
            'status' => 'confirmed',
            'credit_used' => 1,
            'booked_at' => now(),
            'booked_via' => 'customer',
        ]);

        // โหมดดูตัวอย่างต้องไม่บันทึก
        $this->artisan('packages:backfill-first-class-expiry')->assertSuccessful();
        $this->assertNull($unused->fresh()->valid_days);

        $this->artisan('packages:backfill-first-class-expiry', ['--apply' => true])->assertSuccessful();

        $unused->refresh();
        $this->assertSame(30, $unused->valid_days);
        $this->assertNull($unused->expires_at);

        $used->refresh();
        $this->assertSame(30, $used->valid_days);
        $this->assertTrue($used->starts_at->isSameDay($session->start_at));
        $this->assertTrue($used->expires_at->isSameDay($session->start_at->copy()->addDays(30)));
    }

    public function test_paid_order_issues_unstarted_package_and_all_pages_render(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = \App\Models\User::where('role', 'owner')->firstOrFail();
        $customer = $this->customer();
        $package = Package::where('code', 'trio-10')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'quantity' => 1,
            'discount' => 0,
            'mark_paid' => 1,
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $cp = CustomerPackage::where('customer_id', $customer->id)->firstOrFail();
        $this->assertNull($cp->starts_at);
        $this->assertNull($cp->expires_at);
        $this->assertSame($package->valid_days, $cp->valid_days);

        $notStarted = 'ยังไม่เริ่มใช้';

        // ทุกหน้าที่โชว์วันหมดอายุต้องเปิดได้ ไม่พังเพราะ expires_at เป็น null
        $this->actingAs($admin)->get(route('admin.customers.show', $customer))->assertOk()->assertSee($notStarted);
        $this->actingAs($admin)->get(route('admin.counter.index', ['customer' => $customer->id]))->assertOk()->assertSee($notStarted);
        $this->actingAs($admin)->get(route('admin.orders.show', $cp->order_id))->assertOk()->assertSee($notStarted);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($customer, 'customer')->get(route('home'))->assertOk()->assertSee($notStarted);
        $this->actingAs($customer, 'customer')->get(route('customer.profile.index'))->assertOk()->assertSee($notStarted);

        // เริ่มใช้แล้ว หน้าเดิมต้องโชว์วันหมดอายุจริง
        $session = $this->sessionsOnDifferentDays(1)->first();
        $this->service->book($customer, $session);
        $expiry = $cp->fresh()->expires_at->format('d/m/Y');
        $this->actingAs($admin)->get(route('admin.counter.index', ['customer' => $customer->id]))->assertOk()->assertSee($expiry);
        $this->actingAs($customer, 'customer')->get(route('customer.profile.index'))->assertOk()->assertDontSee($notStarted);
    }

    public function test_freeze_extension_survives_a_later_recalculation(): void
    {
        $admin = \App\Models\User::where('role', 'owner')->firstOrFail();
        $customer = $this->customer();
        $cp = $this->unstartedPackage($customer);
        [$first, $second] = $this->sessionsOnDifferentDays(2)->all();

        $this->service->book($customer, $first);
        $secondBooking = $this->service->book($customer, $second);
        $before = $cp->fresh()->expires_at->copy();

        // ฟรีซ 5 วันแล้วปลด
        $cp->update(['status' => 'frozen', 'frozen_from' => now()->subDays(5)->toDateString()]);
        $this->actingAs($admin)->post(route('admin.customers.freeze', $cp))->assertRedirect();
        $this->assertTrue($cp->fresh()->expires_at->isSameDay($before->copy()->addDays(5)));

        // ยกเลิกคลาสที่สอง ระบบคำนวณใหม่ ต้องไม่ทำวันที่ได้จากฟรีซหาย
        $this->service->cancel($secondBooking, 'admin');
        $this->assertTrue($cp->fresh()->expires_at->isSameDay($before->copy()->addDays(5)));
    }

    public function test_unfreezing_an_unstarted_package_adds_no_days(): void
    {
        $admin = \App\Models\User::where('role', 'owner')->firstOrFail();
        $customer = $this->customer();
        $cp = $this->unstartedPackage($customer, ['status' => 'frozen', 'frozen_from' => now()->subDays(7)->toDateString()]);

        $this->actingAs($admin)->post(route('admin.customers.freeze', $cp))->assertRedirect();

        $cp->refresh();
        $this->assertSame('active', $cp->status);
        $this->assertNull($cp->expires_at);
        $this->assertSame(0, (int) $cp->frozen_days_used);
    }

    public function test_cancelling_whole_session_resets_unstarted_package(): void
    {
        $customer = $this->customer();
        $cp = $this->unstartedPackage($customer);
        $session = $this->sessionsOnDifferentDays(1)->first();
        $this->service->book($customer, $session);
        $this->assertTrue($cp->fresh()->isActivated());

        $this->service->cancelSession($session, 'ครูป่วย', 'Trainer sick');

        $this->assertFalse($cp->fresh()->isActivated());
    }

    public function test_waitlist_does_not_start_countdown_until_promoted(): void
    {
        $customer = $this->customer();
        $other = $this->customer();
        $cp = $this->unstartedPackage($customer);
        $this->unstartedPackage($other);

        $session = $this->sessionsOnDifferentDays(1)->first();
        $session->update(['capacity' => 1]);

        $otherBooking = $this->service->book($other, $session);
        $waiting = $this->service->book($customer, $session);
        $this->assertSame('waitlisted', $waiting->status);
        $this->assertFalse($cp->fresh()->isActivated());

        $this->service->cancel($otherBooking, 'admin');

        $this->assertSame('confirmed', $waiting->fresh()->status);
        $this->assertTrue($cp->fresh()->starts_at->isSameDay($session->start_at));
    }

    public function test_unlimited_package_also_starts_on_first_class(): void
    {
        $customer = $this->customer();
        $unlimitedCode = Package::where('type', 'unlimited')->value('code');

        if (! $unlimitedCode) {
            $this->markTestSkipped('ไม่มีแพ็ก unlimited ใน seeder');
        }

        $package = Package::where('code', $unlimitedCode)->first();
        $cp = $this->unstartedPackage($customer, [
            'package_id' => $package->id,
            'type' => 'unlimited',
            'credit_total' => null,
            'credit_remaining' => null,
        ]);

        $session = ClassSession::where('start_at', '>', now()->addDays(2))->orderBy('start_at')->get()
            ->first(fn ($s) => $cp->allowsBranch($s->branch_id) && $cp->allowsClassType($s->class_type_id));

        $this->assertTrue($customer->hasUnlimited());
        $this->service->book($customer, $session);
        $this->assertTrue($cp->fresh()->starts_at->isSameDay($session->start_at));
    }

    public function test_admin_credit_deduction_on_customer_page_starts_countdown(): void
    {
        $admin = \App\Models\User::where('role', 'owner')->firstOrFail();
        $customer = $this->customer();
        $cp = $this->unstartedPackage($customer);

        $this->actingAs($admin)->post(route('admin.customers.credit', $customer), [
            'customer_package_id' => $cp->id,
            'amount' => -1,
            'reason' => 'มาเรียนไม่ได้จอง',
        ])->assertRedirect();

        $this->assertTrue($cp->fresh()->starts_at->isToday());
    }

    public function test_expired_package_is_not_recalculated(): void
    {
        $customer = $this->customer();
        $cp = $this->unstartedPackage($customer);
        $booking = $this->service->book($customer, $this->sessionsOnDifferentDays(1)->first());

        $cp->update(['status' => 'expired']);
        $expiry = $cp->fresh()->expires_at->copy();

        $this->service->cancel($booking, 'admin');

        $this->assertTrue($cp->fresh()->expires_at->isSameDay($expiry));
    }

    public function test_manual_walk_in_deduction_starts_the_countdown_today(): void
    {
        $customer = $this->customer();
        $cp = $this->unstartedPackage($customer);

        app(CreditAdjuster::class)->deduct($customer, 1, 'walk-in');

        $cp->refresh();
        $this->assertTrue($cp->starts_at->isToday());
        $this->assertTrue($cp->expires_at->isSameDay(now()->addDays(30)));
    }
}

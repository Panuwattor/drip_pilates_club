<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * แอดมินจัดการอายุแพ็กให้ลูกค้า ปุ่มเดียว 2 โหมด
 *   - mode=add: บวกวัน / ต่ออายุแพ็กที่หมดแล้ว (คืนเครดิตที่ถูกริบ)
 *   - mode=set: ตั้งอายุรวมใหม่ ลดลงก็ได้
 */
class PackageExtensionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->artisan('classes:generate', ['--days' => 30]);
        $this->admin = User::where('role', 'owner')->firstOrFail();
    }

    private function customer(): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create([
            'code' => 'XT-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'first_name' => 'ต่ออายุ',
            'last_name' => (string) $n,
            'phone' => '06' . str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            'status' => 'active',
        ]);
    }

    private function pack(Customer $customer, array $overrides = []): CustomerPackage
    {
        $package = Package::where('code', 'trio-10')->firstOrFail();

        return CustomerPackage::create(array_merge([
            'code' => 'CP-' . uniqid(),
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'type' => $package->type,
            'purchased_at' => now()->subDays(40),
            'starts_at' => now()->subDays(40)->toDateString(),
            'expires_at' => now()->subDays(10)->toDateString(),
            'valid_days' => 30,
            'credit_total' => 10,
            'credit_used' => 6,
            'credit_remaining' => 4,
            'status' => 'active',
        ], $overrides));
    }

    private function add(CustomerPackage $cp, int $days, string $reason = 'ลูกค้าป่วย')
    {
        return $this->actingAs($this->admin)->post(route('admin.customers.age', $cp), [
            'mode' => 'add',
            'days' => $days,
            'reason' => $reason,
        ]);
    }

    private function set(CustomerPackage $cp, int $days, string $reason = 'ตั้งอายุใหม่')
    {
        return $this->actingAs($this->admin)->post(route('admin.customers.age', $cp), [
            'mode' => 'set',
            'days' => $days,
            'reason' => $reason,
        ]);
    }

    // ---- โหมดบวกวัน (add) --------------------------------------------------

    public function test_adding_days_to_an_active_package_moves_the_expiry_date(): void
    {
        $customer = $this->customer();
        $cp = $this->pack($customer, ['expires_at' => now()->addDays(5)->toDateString()]);

        $this->add($cp, 14)->assertRedirect()->assertSessionHas('status');

        $cp->refresh();
        $this->assertTrue($cp->expires_at->isSameDay(now()->addDays(19)));
        $this->assertSame(14, $cp->extended_days);
        $this->assertSame('active', $cp->status);
        $this->assertDatabaseHas('credit_transactions', [
            'customer_package_id' => $cp->id,
            'amount' => 0,
            'user_id' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('notifications', ['customer_id' => $customer->id, 'type' => 'package_extended']);
    }

    public function test_renewing_an_expired_package_reactivates_it_and_restores_credits(): void
    {
        $customer = $this->customer();
        $cp = $this->pack($customer); // หมดอายุเมื่อ 10 วันก่อน เหลือ 4 เครดิต

        app(BookingService::class)->expirePackages();
        $this->assertSame('expired', $cp->fresh()->status);
        $this->assertSame(0, $customer->totalCredits());

        $this->add($cp, 30)->assertSessionHas('status');

        $cp->refresh();
        $this->assertSame('active', $cp->status);
        $this->assertTrue($cp->expires_at->isSameDay(now()->addDays(20)));
        $this->assertSame(4, $customer->totalCredits());
        $this->assertDatabaseHas('credit_transactions', [
            'customer_package_id' => $cp->id,
            'amount' => 4,
            'type' => 'admin_adjust',
        ]);

        // ต่ออายุแล้วจองคลาสได้จริง
        $session = ClassSession::where('start_at', '>', now()->addDays(2))
            ->whereHas('classType', fn ($q) => $q->where('code', 'trio-reformer'))
            ->orderBy('start_at')->firstOrFail();
        $booking = app(BookingService::class)->book($customer, $session);
        $this->assertSame($cp->id, $booking->customer_package_id);
    }

    public function test_renewing_an_expired_package_must_reach_today(): void
    {
        $customer = $this->customer();
        $cp = $this->pack($customer, ['status' => 'expired']); // หมดไป 10 วัน

        $this->add($cp, 5)->assertSessionHas('error');

        $cp->refresh();
        $this->assertSame('expired', $cp->status);
        $this->assertSame(0, $cp->extended_days);
    }

    public function test_added_days_survive_recalculation_from_first_class(): void
    {
        $customer = $this->customer();
        $cp = $this->pack($customer, [
            'starts_at' => null, 'expires_at' => null,
            'credit_used' => 0, 'credit_remaining' => 10,
        ]);

        $this->add($cp, 10)->assertSessionHas('status');
        $this->assertNull($cp->fresh()->expires_at);
        $this->assertSame(40, $cp->fresh()->totalValidDays());

        $session = ClassSession::where('start_at', '>', now()->addDays(2))
            ->whereHas('classType', fn ($q) => $q->where('code', 'trio-reformer'))
            ->orderBy('start_at')->firstOrFail();
        app(BookingService::class)->book($customer, $session);

        // อายุ 30 วัน + ขยาย 10 วัน นับจากวันของคลาสแรก
        $this->assertTrue($cp->fresh()->expires_at->isSameDay($session->start_at->copy()->addDays(40)));
    }

    // ---- โหมดตั้งอายุใหม่ (set) ---------------------------------------------

    public function test_setting_validity_shortens_an_active_package(): void
    {
        $customer = $this->customer();
        // เริ่มใช้เมื่อ 5 วันก่อน อายุ 90 วัน ยังเหลืออีกนาน
        $cp = $this->pack($customer, [
            'starts_at' => now()->subDays(5)->toDateString(),
            'expires_at' => now()->addDays(85)->toDateString(),
            'valid_days' => 90,
            'extended_days' => 7, // มีวันขยายเก่าค้างอยู่ ต้องถูกล้าง
        ]);

        $this->set($cp, 14)->assertRedirect()->assertSessionHas('status');

        $cp->refresh();
        $this->assertSame(14, $cp->valid_days);
        $this->assertSame(0, $cp->extended_days);
        $this->assertSame(14, $cp->totalValidDays());
        // 14 วันนับจากวันเริ่มใช้ (5 วันก่อน) => เหลืออีก 9 วัน
        $this->assertTrue($cp->expires_at->isSameDay(now()->subDays(5)->addDays(14)));
        $this->assertSame('active', $cp->status);
        $this->assertDatabaseHas('notifications', ['customer_id' => $customer->id, 'type' => 'package_validity_set']);
    }

    public function test_setting_validity_on_a_not_started_package_waits_for_first_class(): void
    {
        $customer = $this->customer();
        $cp = $this->pack($customer, [
            'starts_at' => null, 'expires_at' => null,
            'valid_days' => 90, 'credit_used' => 0, 'credit_remaining' => 10,
        ]);

        $this->set($cp, 14)->assertSessionHas('status');

        $cp->refresh();
        $this->assertSame(14, $cp->valid_days);
        $this->assertNull($cp->expires_at);

        $session = ClassSession::where('start_at', '>', now()->addDays(2))
            ->whereHas('classType', fn ($q) => $q->where('code', 'trio-reformer'))
            ->orderBy('start_at')->firstOrFail();
        app(BookingService::class)->book($customer, $session);

        // อายุ 14 วันนับจากวันของคลาสแรก
        $this->assertTrue($cp->fresh()->expires_at->isSameDay($session->start_at->copy()->addDays(14)));
    }

    public function test_setting_a_validity_that_lands_in_the_past_expires_and_forfeits_credits(): void
    {
        $customer = $this->customer();
        // เริ่มใช้เมื่อ 20 วันก่อน เหลือ 4 เครดิต
        $cp = $this->pack($customer, [
            'starts_at' => now()->subDays(20)->toDateString(),
            'expires_at' => now()->addDays(10)->toDateString(),
            'valid_days' => 30,
        ]);
        $this->assertSame(4, $customer->totalCredits());

        // ตั้งอายุ 14 วัน แต่เริ่มใช้ 20 วันแล้ว => หมดอายุไปแล้ว
        $this->set($cp, 14)->assertSessionHas('status');

        $cp->refresh();
        $this->assertSame('expired', $cp->status);
        $this->assertSame(0, $customer->totalCredits());
        $this->assertDatabaseHas('credit_transactions', [
            'customer_package_id' => $cp->id,
            'amount' => -4,
            'type' => 'expire',
        ]);
    }

    public function test_setting_validity_can_revive_an_expired_package_and_restore_credits(): void
    {
        $customer = $this->customer();
        $cp = $this->pack($customer); // หมดอายุเมื่อ 10 วันก่อน เหลือ 4 เครดิต

        app(BookingService::class)->expirePackages();
        $this->assertSame('expired', $cp->fresh()->status);
        $this->assertSame(0, $customer->totalCredits());

        // เริ่มใช้เมื่อ 40 วันก่อน ตั้งอายุ 60 วัน => ยังไม่หมด (ถึงอีก 20 วัน) แพ็กฟื้น + คืนเครดิต
        $this->set($cp, 60)->assertSessionHas('status');

        $cp->refresh();
        $this->assertSame('active', $cp->status);
        $this->assertTrue($cp->expires_at->isSameDay(now()->subDays(40)->addDays(60)));
        $this->assertSame(4, $customer->totalCredits());
        $this->assertDatabaseHas('credit_transactions', [
            'customer_package_id' => $cp->id,
            'amount' => 4,
            'type' => 'admin_adjust',
        ]);
    }

    public function test_cannot_set_validity_on_a_purchase_date_package(): void
    {
        $customer = $this->customer();
        // แพ็กแบบเก่า นับจากวันซื้อ (valid_days = null)
        $cp = $this->pack($customer, [
            'valid_days' => null,
            'expires_at' => now()->addDays(5)->toDateString(),
        ]);

        $this->set($cp, 14)->assertSessionHas('error');
        $this->assertNull($cp->fresh()->valid_days);
    }

    // ---- ตรวจความถูกต้องของ input และหน้าจอ ---------------------------------

    public function test_cancelled_package_cannot_be_changed_and_input_is_validated(): void
    {
        $customer = $this->customer();
        $cancelled = $this->pack($customer, ['status' => 'cancelled']);
        $this->add($cancelled, 30)->assertSessionHas('error');
        $this->set($cancelled, 14)->assertSessionHas('error');
        $this->assertSame(0, $cancelled->fresh()->extended_days);

        $active = $this->pack($customer, ['expires_at' => now()->addDays(5)->toDateString()]);
        $this->actingAs($this->admin)->post(route('admin.customers.age', $active), ['mode' => 'add', 'days' => 0, 'reason' => 'x'])
            ->assertSessionHasErrors('days');
        $this->actingAs($this->admin)->post(route('admin.customers.age', $active), ['mode' => 'set', 'days' => 7])
            ->assertSessionHasErrors('reason');
        $this->actingAs($this->admin)->post(route('admin.customers.age', $active), ['mode' => 'nope', 'days' => 7, 'reason' => 'x'])
            ->assertSessionHasErrors('mode');
        // โหมดบวกวันจำกัดที่ 365 วัน
        $this->actingAs($this->admin)->post(route('admin.customers.age', $active), ['mode' => 'add', 'days' => 400, 'reason' => 'x'])
            ->assertSessionHas('error');
    }

    public function test_customer_page_shows_a_single_manage_age_button(): void
    {
        $customer = $this->customer();
        $this->pack($customer, ['status' => 'expired']);
        $this->pack($customer, ['expires_at' => now()->addDays(5)->toDateString()]);

        $this->actingAs($this->admin)->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('id="ageModal"', false)
            ->assertSee('js-manage-age', false)
            ->assertSee(__t('ต่ออายุ', 'Renew'))
            ->assertDontSee('js-extend-package', false)
            ->assertDontSee('id="validityModal"', false);
    }
}

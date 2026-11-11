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
 * แอดมินขยายอายุ / ต่ออายุแพ็กให้ลูกค้า
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

    private function extend(CustomerPackage $cp, int $days, string $reason = 'ลูกค้าป่วย')
    {
        return $this->actingAs($this->admin)->post(route('admin.customers.extend', $cp), [
            'days' => $days,
            'reason' => $reason,
        ]);
    }

    public function test_extending_an_active_package_moves_the_expiry_date(): void
    {
        $customer = $this->customer();
        $cp = $this->pack($customer, ['expires_at' => now()->addDays(5)->toDateString()]);

        $this->extend($cp, 14)->assertRedirect()->assertSessionHas('status');

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

        $this->extend($cp, 30)->assertSessionHas('status');

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

        $this->extend($cp, 5)->assertSessionHas('error');

        $cp->refresh();
        $this->assertSame('expired', $cp->status);
        $this->assertSame(0, $cp->extended_days);
    }

    public function test_extension_survives_recalculation_from_first_class(): void
    {
        $customer = $this->customer();
        $cp = $this->pack($customer, [
            'starts_at' => null, 'expires_at' => null,
            'credit_used' => 0, 'credit_remaining' => 10,
        ]);

        $this->extend($cp, 10)->assertSessionHas('status');
        $this->assertNull($cp->fresh()->expires_at);
        $this->assertSame(40, $cp->fresh()->totalValidDays());

        $session = ClassSession::where('start_at', '>', now()->addDays(2))
            ->whereHas('classType', fn ($q) => $q->where('code', 'trio-reformer'))
            ->orderBy('start_at')->firstOrFail();
        app(BookingService::class)->book($customer, $session);

        // อายุ 30 วัน + ขยาย 10 วัน นับจากวันของคลาสแรก
        $this->assertTrue($cp->fresh()->expires_at->isSameDay($session->start_at->copy()->addDays(40)));
    }

    public function test_cancelled_package_cannot_be_extended_and_input_is_validated(): void
    {
        $customer = $this->customer();
        $cancelled = $this->pack($customer, ['status' => 'cancelled']);
        $this->extend($cancelled, 30)->assertSessionHas('error');
        $this->assertSame(0, $cancelled->fresh()->extended_days);

        $active = $this->pack($customer, ['expires_at' => now()->addDays(5)->toDateString()]);
        $this->actingAs($this->admin)->post(route('admin.customers.extend', $active), ['days' => 0, 'reason' => 'x'])
            ->assertSessionHasErrors('days');
        $this->actingAs($this->admin)->post(route('admin.customers.extend', $active), ['days' => 400, 'reason' => 'x'])
            ->assertSessionHasErrors('days');
        $this->actingAs($this->admin)->post(route('admin.customers.extend', $active), ['days' => 7])
            ->assertSessionHasErrors('reason');
    }

    public function test_customer_page_shows_extend_and_renew_buttons(): void
    {
        $customer = $this->customer();
        $this->pack($customer, ['status' => 'expired']);
        $this->pack($customer, ['expires_at' => now()->addDays(5)->toDateString()]);

        $this->actingAs($this->admin)->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('id="extendModal"', false)
            ->assertSee('js-extend-package', false)
            ->assertSee(__t('ต่ออายุ', 'Renew'));
    }
}

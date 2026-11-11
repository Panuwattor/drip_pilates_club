<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Setting;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * กติกายกเลิกคลาส
 *  - กรุ๊ปคลาส: ยกเลิกฟรีได้ถึง 22:00 (เวลาไทย) ของวันก่อนคลาส เลยจากนั้นตัดเครดิต
 *  - คลาสที่ไม่ได้ตั้งเวลาตัด (ไพรเวท): ใช้จำนวนชั่วโมงจาก settings
 *
 * แอปรันด้วย timezone UTC แต่เวลาคลาสเก็บเป็นเวลาไทย เทสต์จึงตั้งเวลาเป็น UTC แล้วเช็คว่าเทียบเป็นเวลาไทยถูก
 */
class CancellationPolicyTest extends TestCase
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

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function customerWithCredits(): array
    {
        static $n = 0;
        $n++;

        $customer = Customer::create([
            'code' => 'CX-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'first_name' => 'ยกเลิก',
            'last_name' => (string) $n,
            'phone' => '07' . str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            'status' => 'active',
        ]);

        $package = Package::where('code', 'trio-10')->firstOrFail();

        $cp = CustomerPackage::create([
            'code' => 'CP-' . uniqid(),
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'type' => $package->type,
            'purchased_at' => now(),
            'starts_at' => now()->subDay()->toDateString(),
            'expires_at' => now()->addDays(90)->toDateString(),
            'credit_total' => 10,
            'credit_used' => 0,
            'credit_remaining' => 10,
            'status' => 'active',
        ]);

        return [$customer, $cp];
    }

    /** คลาสทรีโอ (กรุ๊ปคลาส) ตั้งเวลาเริ่มเป็นเวลาไทยแบบไม่มีโซน เหมือนข้อมูลจริง */
    private function trioSessionAt(string $thaiStart): ClassSession
    {
        $session = ClassSession::whereHas('classType', fn ($q) => $q->where('code', 'trio-reformer'))
            ->orderBy('start_at')
            ->firstOrFail();

        $start = Carbon::parse($thaiStart);
        $session->update([
            'start_at' => $start,
            'end_at' => $start->copy()->addMinutes(50),
            'booking_opens_at' => null,
            'booking_closes_at' => null,
            'booked_count' => 0,
            'waitlist_count' => 0,
        ]);

        return $session->fresh();
    }

    /** ตั้งนาฬิกาเป็นเวลาไทยที่ระบุ (แอปเป็น UTC จึงแปลงก่อน) */
    private function atThaiTime(string $thai): void
    {
        $this->travelTo(Carbon::parse($thai, 'Asia/Bangkok')->utc());
    }

    public function test_seeded_group_classes_use_22_cutoff_and_private_does_not(): void
    {
        $this->assertSame('22:00', \App\Models\ClassType::where('code', 'trio-reformer')->value('cancel_cutoff_time'));
        $this->assertSame('22:00', \App\Models\ClassType::where('code', 'silom-mat-group')->value('cancel_cutoff_time'));
        $this->assertNull(\App\Models\ClassType::where('code', 'private-pilates')->value('cancel_cutoff_time'));
    }

    public function test_group_class_cancelled_before_22_the_day_before_is_refunded(): void
    {
        $session = $this->trioSessionAt('2026-12-16 08:00:00'); // พุธ 08:00
        [$customer, $cp] = $this->customerWithCredits();

        $this->atThaiTime('2026-12-14 10:00:00');
        $booking = $this->service->book($customer, $session);

        $this->atThaiTime('2026-12-15 21:59:00'); // อังคาร 21:59
        $result = $this->service->cancel($booking, 'customer');

        $this->assertSame('cancelled', $result->status);
        $this->assertTrue((bool) $result->credit_refunded);
        $this->assertSame(10, $cp->fresh()->credit_remaining);
    }

    public function test_group_class_cancelled_after_22_the_day_before_is_charged(): void
    {
        $session = $this->trioSessionAt('2026-12-16 08:00:00');
        [$customer, $cp] = $this->customerWithCredits();

        $this->atThaiTime('2026-12-14 10:00:00');
        $booking = $this->service->book($customer, $session);

        $this->atThaiTime('2026-12-15 22:01:00'); // อังคาร 22:01
        $result = $this->service->cancel($booking, 'customer');

        $this->assertSame('late_cancelled', $result->status);
        $this->assertFalse((bool) $result->credit_refunded);
        $this->assertSame(9, $cp->fresh()->credit_remaining);
    }

    public function test_evening_class_deadline_is_still_22_of_the_previous_day(): void
    {
        // คลาส 19:00 วันพุธ ยกเลิกตอนเช้าวันพุธ ก็ถือว่าช้าแล้ว (เส้นตายคือ 22:00 วันอังคาร)
        $session = $this->trioSessionAt('2026-12-16 19:00:00');
        [$customer] = $this->customerWithCredits();

        $this->atThaiTime('2026-12-14 10:00:00');
        $booking = $this->service->book($customer, $session);

        $this->atThaiTime('2026-12-16 07:00:00');
        $preview = $this->service->cancellationPreview($booking);

        $this->assertTrue($preview['will_lose_credit']);
        $this->assertSame('2026-12-15 22:00', $preview['deadline']->format('Y-m-d H:i'));
    }

    public function test_class_without_cutoff_uses_hours_setting_in_thai_time(): void
    {
        $session = $this->trioSessionAt('2026-12-16 18:00:00');
        $session->classType->update(['cancel_cutoff_time' => null]);
        Setting::put('cancel_deadline_hours', 6);
        [$customer] = $this->customerWithCredits();

        $this->atThaiTime('2026-12-14 10:00:00');
        $booking = $this->service->book($customer, $session);

        // 11:59 เวลาไทย = ยังทัน (เส้นตาย 12:00) ถ้าเทียบผิดโซนจะเพี้ยนไป 7 ชม.
        $this->atThaiTime('2026-12-16 11:59:00');
        $this->assertFalse($this->service->cancellationPreview($booking->fresh())['is_late']);

        $this->atThaiTime('2026-12-16 12:01:00');
        $this->assertTrue($this->service->cancellationPreview($booking->fresh())['is_late']);
    }

    public function test_late_cancel_needs_explicit_confirmation_from_the_popup(): void
    {
        $session = $this->trioSessionAt('2026-12-16 08:00:00');
        [$customer, $cp] = $this->customerWithCredits();

        $this->atThaiTime('2026-12-14 10:00:00');
        $booking = $this->service->book($customer, $session);

        $this->atThaiTime('2026-12-15 23:00:00');

        // ป๊อปอัปได้ข้อมูลเตือนครบ
        $this->actingAs($customer, 'customer')
            ->getJson(route('customer.cancel.preview', $booking))
            ->assertOk()
            ->assertJson(['is_late' => true, 'will_lose_credit' => true, 'credit_at_stake' => 1])
            ->assertJsonPath('deadline', '15/12/2026 22:00');

        // ไม่ได้กดยืนยัน Late Cancel -> ไม่ยกเลิก ไม่ตัดเครดิต
        $this->actingAs($customer, 'customer')
            ->postJson(route('customer.cancel', $booking))
            ->assertStatus(409)
            ->assertJson(['needs_late_confirm' => true, 'will_lose_credit' => true]);
        $this->assertSame('confirmed', $booking->fresh()->status);

        // กดยืนยันแล้ว -> ยกเลิกช้า ตัดเครดิต
        $this->actingAs($customer, 'customer')
            ->postJson(route('customer.cancel', $booking), ['confirm_late' => 1])
            ->assertOk()
            ->assertJson(['ok' => true, 'status' => 'late_cancelled', 'refunded' => false]);
        $this->assertSame(9, $cp->fresh()->credit_remaining);
    }

    public function test_in_time_cancel_works_without_late_confirmation(): void
    {
        $session = $this->trioSessionAt('2026-12-16 08:00:00');
        [$customer, $cp] = $this->customerWithCredits();

        $this->atThaiTime('2026-12-14 10:00:00');
        $booking = $this->service->book($customer, $session);

        $this->actingAs($customer, 'customer')
            ->getJson(route('customer.cancel.preview', $booking))
            ->assertOk()
            ->assertJson(['is_late' => false, 'will_lose_credit' => false])
            ->assertJsonPath('rule_text', fn ($t) => str_contains($t, '22:00'));

        $this->actingAs($customer, 'customer')
            ->postJson(route('customer.cancel', $booking))
            ->assertOk()
            ->assertJson(['ok' => true, 'refunded' => true]);
        $this->assertSame(10, $cp->fresh()->credit_remaining);
    }

    public function test_waitlisted_booking_is_never_late(): void
    {
        $session = $this->trioSessionAt('2026-12-16 08:00:00');
        $session->update(['capacity' => 1]);
        [$first] = $this->customerWithCredits();
        [$second] = $this->customerWithCredits();

        $this->atThaiTime('2026-12-14 10:00:00');
        $this->service->book($first, $session);
        $waiting = $this->service->book($second, $session);
        $this->assertSame('waitlisted', $waiting->status);

        $this->atThaiTime('2026-12-15 23:30:00');
        $this->actingAs($second, 'customer')
            ->postJson(route('customer.cancel', $waiting))
            ->assertOk();
        $this->assertSame('cancelled', $waiting->fresh()->status);
    }

    public function test_admin_can_set_and_clear_cutoff_on_class_type(): void
    {
        $admin = User::where('role', 'owner')->firstOrFail();
        $type = \App\Models\ClassType::where('code', 'private-pilates')->firstOrFail();

        $payload = $type->only([
            'code', 'name_th', 'name_en', 'level', 'equipment_type', 'duration_min', 'default_capacity', 'credit_cost',
        ]) + ['is_active' => 1];

        $this->actingAs($admin)->put(route('admin.class-types.update', $type), $payload + ['cancel_cutoff_time' => '21:30'])
            ->assertSessionHasNoErrors();
        $this->assertSame('21:30', $type->fresh()->cancel_cutoff_time);

        $this->actingAs($admin)->put(route('admin.class-types.update', $type), $payload + ['cancel_cutoff_time' => ''])
            ->assertSessionHasNoErrors();
        $this->assertNull($type->fresh()->cancel_cutoff_time);

        $this->actingAs($admin)->put(route('admin.class-types.update', $type), $payload + ['cancel_cutoff_time' => '25:99'])
            ->assertSessionHasErrors('cancel_cutoff_time');
    }

    public function test_customer_pages_include_popup_and_guide_explains_rule(): void
    {
        [$customer] = $this->customerWithCredits();

        $this->actingAs($customer, 'customer')->get(route('customer.bookings'))
            ->assertOk()->assertSee('id="cancelDialog"', false)->assertSee('Late Cancel');
        $this->actingAs($customer, 'customer')->get(route('customer.schedule'))
            ->assertOk()->assertSee('id="cancelDialog"', false);
        $this->actingAs($customer, 'customer')->get(route('customer.guide'))
            ->assertOk()->assertSee('22:00');
    }
}

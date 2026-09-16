<?php

namespace Tests\Feature;

use App\Exceptions\BookingException;
use App\Models\ClassSession;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * walk-in หน้าเคาน์เตอร์ — ลูกค้าเดินเข้ามาเรียนสดโดยไม่ได้จอง
 *
 * ต่างจากการหักเครดิตมือตรงที่อันนี้สร้าง booking จริง ทำให้ยอดคนเข้าเรียน
 * และเครดิตที่หัก (ตาม credit_cost ของคลาส) ตรงกับความจริง
 */
class CounterWalkInTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $service;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->artisan('classes:generate', ['--days' => 30]);

        $this->service = app(BookingService::class);
        $this->admin = User::where('role', 'owner')->firstOrFail();
    }

    private function customer(array $attrs = []): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create(array_merge([
            'code' => 'W-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'first_name' => 'วอล์คอิน',
            'last_name' => (string) $n,
            'phone' => '09' . str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            'status' => 'active',
        ], $attrs));
    }

    private function givePackage(Customer $customer, string $code = 'trio-10'): CustomerPackage
    {
        $package = Package::where('code', $code)->firstOrFail();

        return CustomerPackage::create([
            'code' => 'CP-' . uniqid(),
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'type' => $package->type,
            'purchased_at' => now(),
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addDays($package->valid_days)->toDateString(),
            'credit_total' => $package->credit_amount,
            'credit_used' => 0,
            'credit_remaining' => $package->credit_amount,
            'max_per_day' => $package->max_per_day,
            'max_per_week' => $package->max_per_week,
            'max_future_bookings' => $package->max_future_bookings,
            'status' => 'active',
        ]);
    }

    /** คลาสที่ "กำลังเรียนอยู่" — เริ่มไปแล้วแต่ยังไม่จบ ซึ่งเป็นกรณีจริงของ walk-in */
    private function startedSession(): ClassSession
    {
        $session = ClassSession::where('start_at', '>', now()->addDays(2))
            ->whereHas('classType', fn ($q) => $q->where('code', 'trio-reformer'))
            ->orderBy('start_at')
            ->firstOrFail();

        $session->update([
            'start_at' => now()->subMinutes(10),
            'end_at' => now()->addMinutes(50),
        ]);

        return $session->fresh();
    }

    public function test_walk_in_can_join_a_class_that_already_started(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer);
        $session = $this->startedSession();

        $booking = $this->service->book($customer, $session, 'walk_in', $this->admin->id);

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('walk_in', $booking->booked_via);
        $this->assertSame($session->id, $booking->class_session_id);
        $this->assertSame(1, $session->fresh()->booked_count);
        $this->assertSame(9, $package->fresh()->credit_remaining);
    }

    public function test_normal_booking_still_blocked_after_class_starts(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);
        $session = $this->startedSession();

        $this->expectException(BookingException::class);
        $this->service->book($customer, $session, 'customer');
    }

    public function test_walk_in_rejected_once_class_has_ended(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);
        $session = $this->startedSession();
        $session->update(['start_at' => now()->subHours(2), 'end_at' => now()->subHour()]);

        $this->expectException(BookingException::class);
        $this->service->book($customer, $session->fresh(), 'walk_in', $this->admin->id);
    }

    public function test_walk_in_overbooks_instead_of_joining_waitlist(): void
    {
        $session = $this->startedSession();
        $session->update(['booked_count' => $session->capacity]);

        $customer = $this->customer();
        $this->givePackage($customer);

        $booking = $this->service->book($customer, $session->fresh(), 'walk_in', $this->admin->id);

        // ต้องได้ booking จริง ไม่ใช่เข้าคิว เพราะคนยืนอยู่หน้าเคาน์เตอร์แล้ว
        $this->assertSame('confirmed', $booking->status);
        $this->assertNull($booking->waitlist_position);
    }

    public function test_walk_in_deducts_the_class_credit_cost(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer);

        $session = $this->startedSession();
        $session->update(['credit_cost' => 2]);

        $booking = $this->service->book($customer, $session->fresh(), 'walk_in', $this->admin->id);

        $this->assertEquals(2, $booking->credit_used);
        $this->assertSame(8, $package->fresh()->credit_remaining);
    }

    public function test_counter_walk_in_endpoint_books_and_checks_in(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);
        $session = $this->startedSession();

        $this->actingAs($this->admin)
            ->post(route('admin.counter.walkin', $customer), [
                'class_session_id' => $session->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'class_session_id' => $session->id,
            'booked_via' => 'walk_in',
            'status' => 'attended',
        ]);
        $this->assertSame(1, $session->fresh()->attended_count);
    }

    public function test_counter_walk_in_needs_confirmation_when_class_is_full(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);

        $session = $this->startedSession();
        $session->update(['booked_count' => $session->capacity]);

        $this->actingAs($this->admin)
            ->post(route('admin.counter.walkin', $customer), [
                'class_session_id' => $session->id,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('bookings', ['customer_id' => $customer->id]);

        // ยืนยันแล้วต้องผ่าน
        $this->actingAs($this->admin)
            ->post(route('admin.counter.walkin', $customer), [
                'class_session_id' => $session->id,
                'confirm_overbook' => 1,
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'booked_via' => 'walk_in',
        ]);
    }
}

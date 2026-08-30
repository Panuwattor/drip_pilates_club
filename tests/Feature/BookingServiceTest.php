<?php

namespace Tests\Feature;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Setting;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
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

    private function customer(array $attrs = []): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create(array_merge([
            'code' => 'C-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'first_name' => 'ทดสอบ',
            'last_name' => (string) $n,
            'phone' => '08' . str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            'status' => 'active',
        ], $attrs));
    }

    private function givePackage(Customer $customer, string $code = 'trio-10', array $overrides = []): CustomerPackage
    {
        $package = Package::where('code', $code)->firstOrFail();

        return CustomerPackage::create(array_merge([
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
        ], $overrides));
    }

    private function futureSession(): ClassSession
    {
        return ClassSession::where('start_at', '>', now()->addDays(2))
            ->orderBy('start_at')
            ->firstOrFail();
    }

    public function test_booking_deducts_one_credit(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer);
        $session = $this->futureSession();

        $booking = $this->service->book($customer, $session);

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame(9, $package->fresh()->credit_remaining);
        $this->assertSame(1, $session->fresh()->booked_count);
        $this->assertDatabaseHas('credit_transactions', [
            'booking_id' => $booking->id,
            'type' => 'booking',
            'amount' => -1,
        ]);
    }

    public function test_cannot_book_same_session_twice(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);
        $session = $this->futureSession();

        $this->service->book($customer, $session);

        $this->expectException(BookingException::class);
        $this->service->book($customer, $session);
    }

    public function test_booking_without_package_fails(): void
    {
        $customer = $this->customer();
        $session = $this->futureSession();

        try {
            $this->service->book($customer, $session);
            $this->fail('ควรจองไม่ได้เพราะไม่มีแพ็ก');
        } catch (BookingException $e) {
            $this->assertSame('no_active_package', $e->reason);
        }
    }

    public function test_expired_package_cannot_be_used(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer, 'trio-10', [
            'starts_at' => now()->subDays(100)->toDateString(),
            'expires_at' => now()->subDay()->toDateString(),
        ]);
        $session = $this->futureSession();

        $this->expectException(BookingException::class);
        $this->service->book($customer, $session);
    }

    public function test_deducts_from_package_expiring_soonest(): void
    {
        $customer = $this->customer();

        $later = $this->givePackage($customer, 'trio-10', [
            'expires_at' => now()->addDays(90)->toDateString(),
        ]);
        $sooner = $this->givePackage($customer, 'trio-5', [
            'expires_at' => now()->addDays(10)->toDateString(),
        ]);

        $this->service->book($customer, $this->futureSession());

        $this->assertSame(4, $sooner->fresh()->credit_remaining, 'ต้องตัดจากแพ็กที่ใกล้หมดอายุก่อน');
        $this->assertSame(10, $later->fresh()->credit_remaining, 'แพ็กที่หมดอายุทีหลังต้องไม่ถูกแตะ');
    }

    public function test_unlimited_package_does_not_deduct_credit(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer, 'unlimited-monthly');

        $booking = $this->service->book($customer, $this->futureSession());

        $this->assertSame('confirmed', $booking->status);
        $this->assertEquals(0, $booking->credit_used);
        $this->assertNull($package->fresh()->credit_remaining);
    }

    public function test_unlimited_respects_daily_quota(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer, 'unlimited-monthly'); // max_per_day = 2

        $day = ClassSession::where('start_at', '>', now()->addDay())
            ->orderBy('start_at')->first()->start_at->toDateString();

        $sameDay = ClassSession::whereDate('start_at', $day)
            ->orderBy('start_at')->take(3)->get();

        $this->assertGreaterThanOrEqual(3, $sameDay->count(), 'ต้องมีอย่างน้อย 3 คลาสในวันเดียวกันเพื่อทดสอบ');

        $this->service->book($customer, $sameDay[0]);
        $this->service->book($customer, $sameDay[1]);

        try {
            $this->service->book($customer, $sameDay[2]);
            $this->fail('คลาสที่ 3 ในวันเดียวกันควรถูกบล็อกด้วยโควตา');
        } catch (BookingException $e) {
            $this->assertSame('quota_exceeded', $e->reason);
        }
    }

    public function test_full_class_puts_customer_on_waitlist(): void
    {
        $session = $this->futureSession();
        $session->update(['capacity' => 1]);

        $first = $this->customer();
        $this->givePackage($first);
        $this->service->book($first, $session);

        $second = $this->customer();
        $this->givePackage($second);
        $waitlisted = $this->service->book($second, $session);

        $this->assertSame('waitlisted', $waitlisted->status);
        $this->assertSame(1, $waitlisted->waitlist_position);
        $this->assertEquals(0, $waitlisted->credit_used, 'อยู่คิวยังไม่ตัดเครดิต');
        $this->assertSame(1, $session->fresh()->waitlist_count);
    }

    public function test_cancelling_promotes_first_waitlisted_customer(): void
    {
        $session = $this->futureSession();
        $session->update(['capacity' => 1]);

        $first = $this->customer();
        $this->givePackage($first);
        $firstBooking = $this->service->book($first, $session);

        $second = $this->customer();
        $secondPackage = $this->givePackage($second);
        $secondBooking = $this->service->book($second, $session);
        $this->assertSame('waitlisted', $secondBooking->status);

        // คนแรกยกเลิก คนที่รออยู่ต้องถูกเลื่อนขึ้นมาอัตโนมัติ
        $this->service->cancel($firstBooking, 'admin');

        $promoted = $secondBooking->fresh();
        $this->assertSame('confirmed', $promoted->status);
        $this->assertNotNull($promoted->promoted_at);
        $this->assertNull($promoted->waitlist_position);
        $this->assertEquals(1, $promoted->credit_used, 'ตอนถูกเลื่อนขึ้นมาถึงจะตัดเครดิต');
        $this->assertSame(9, $secondPackage->fresh()->credit_remaining);

        $session->refresh();
        $this->assertSame(1, $session->booked_count);
        $this->assertSame(0, $session->waitlist_count);

        $this->assertDatabaseHas('notifications', [
            'customer_id' => $second->id,
            'type' => 'waitlist_promoted',
        ]);
    }

    public function test_cancelling_in_time_refunds_credit(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer);
        $session = $this->futureSession();

        $booking = $this->service->book($customer, $session);
        $this->assertSame(9, $package->fresh()->credit_remaining);

        $cancelled = $this->service->cancel($booking, 'customer');

        $this->assertSame('cancelled', $cancelled->status);
        $this->assertTrue((bool) $cancelled->credit_refunded);
        $this->assertSame(10, $package->fresh()->credit_remaining, 'ยกเลิกทันเวลาต้องคืนเครดิต');
        $this->assertSame(0, $session->fresh()->booked_count);
    }

    public function test_late_cancellation_does_not_refund_credit(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer);

        // คลาสเริ่มในอีก 2 ชม. ซึ่งเลยเส้นตาย 12 ชม. แล้ว
        $session = $this->futureSession();
        $session->update([
            'start_at' => now()->addHours(2),
            'end_at' => now()->addHours(3),
            'booking_closes_at' => now()->addHour(),
        ]);

        $booking = $this->service->book($customer, $session);
        $cancelled = $this->service->cancel($booking, 'customer');

        $this->assertSame('late_cancelled', $cancelled->status);
        $this->assertFalse((bool) $cancelled->credit_refunded);
        $this->assertSame(9, $package->fresh()->credit_remaining, 'ยกเลิกช้าต้องไม่คืนเครดิต');
        $this->assertDatabaseHas('credit_transactions', [
            'booking_id' => $booking->id,
            'type' => 'late_cancel',
        ]);
    }

    public function test_admin_cancellation_is_never_late(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer);

        $session = $this->futureSession();
        $session->update([
            'start_at' => now()->addHours(2),
            'end_at' => now()->addHours(3),
        ]);

        $booking = $this->service->book($customer, $session, 'admin');
        $cancelled = $this->service->cancel($booking, 'admin');

        $this->assertSame('cancelled', $cancelled->status);
        $this->assertSame(10, $package->fresh()->credit_remaining, 'แอดมินยกเลิกให้ต้องคืนเครดิตเสมอ');
    }

    public function test_cancellation_preview_warns_about_credit_loss(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);

        $session = $this->futureSession();
        $session->update(['start_at' => now()->addHours(2), 'end_at' => now()->addHours(3)]);

        $booking = $this->service->book($customer, $session, 'admin');
        $preview = $this->service->cancellationPreview($booking);

        $this->assertTrue($preview['is_late']);
        $this->assertTrue($preview['will_lose_credit']);
        $this->assertEquals(1, $preview['credit_at_stake']);
    }

    public function test_cancelling_whole_session_refunds_everyone(): void
    {
        $session = $this->futureSession();
        $session->update(['capacity' => 3]);

        $packages = [];
        foreach (range(1, 3) as $i) {
            $customer = $this->customer();
            $packages[] = $this->givePackage($customer);
            $this->service->book($customer, $session);
        }

        $affected = $this->service->cancelSession($session, 'ครูป่วย', 'Instructor sick');

        $this->assertSame(3, $affected);
        $this->assertSame('cancelled', $session->fresh()->status);

        foreach ($packages as $package) {
            $this->assertSame(10, $package->fresh()->credit_remaining, 'ทุกคนต้องได้เครดิตคืน');
        }

        $this->assertDatabaseCount('notifications', 6); // 3 booking_confirmed + 3 class_cancelled
        $this->assertDatabaseHas('credit_transactions', ['type' => 'compensate']);
    }

    public function test_no_show_keeps_credit_deducted(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer);

        $booking = $this->service->book($customer, $this->futureSession());
        $result = $this->service->markNoShow($booking);

        $this->assertSame('no_show', $result->status);
        $this->assertSame(9, $package->fresh()->credit_remaining, 'ไม่มาเรียนต้องไม่คืนเครดิต');
        $this->assertDatabaseHas('credit_transactions', [
            'booking_id' => $booking->id,
            'type' => 'no_show',
        ]);
    }

    public function test_check_in_marks_attendance(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);
        $session = $this->futureSession();

        $booking = $this->service->book($customer, $session);
        $result = $this->service->checkIn($booking);

        $this->assertSame('attended', $result->status);
        $this->assertNotNull($result->checked_in_at);
        $this->assertSame(1, $session->fresh()->attended_count);
    }

    public function test_used_up_package_reactivates_after_refund(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer, 'trio-1'); // 1 เครดิต

        $booking = $this->service->book($customer, $this->futureSession());
        $this->assertSame('used_up', $package->fresh()->status);

        $this->service->cancel($booking, 'admin');

        $package->refresh();
        $this->assertSame('active', $package->status, 'คืนเครดิตแล้วแพ็กต้องกลับมาใช้ได้');
        $this->assertSame(1, $package->credit_remaining);
    }

    public function test_booking_after_class_started_is_rejected(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);

        $session = $this->futureSession();
        $session->update(['start_at' => now()->subHour(), 'end_at' => now()]);

        try {
            $this->service->book($customer, $session);
            $this->fail('ไม่ควรจองคลาสที่เริ่มไปแล้วได้');
        } catch (BookingException $e) {
            $this->assertSame('session_already_started', $e->reason);
        }
    }

    public function test_booking_logs_record_every_transition(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);

        $booking = $this->service->book($customer, $this->futureSession());
        $this->service->cancel($booking, 'admin', 'ทดสอบ');

        $logs = $booking->fresh()->logs()->orderBy('id')->get();
        $this->assertCount(2, $logs);
        $this->assertSame('confirmed', $logs[0]->to_status);
        $this->assertSame('cancelled', $logs[1]->to_status);
    }
}

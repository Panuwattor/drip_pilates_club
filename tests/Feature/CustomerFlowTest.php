<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\ClassSession;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Trainer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->artisan('classes:generate', ['--days' => 14]);
    }

    private function customer(): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create([
            'code' => 'DP-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'first_name' => 'ทดสอบ',
            'last_name' => (string) $n,
            'phone' => '08' . str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            'password' => 'secret123',
            'status' => 'active',
        ]);
    }

    private function givePackage(Customer $c, string $code = 'trio-10'): CustomerPackage
    {
        $p = Package::where('code', $code)->firstOrFail();

        return CustomerPackage::create([
            'code' => 'CP-' . uniqid(),
            'customer_id' => $c->id,
            'package_id' => $p->id,
            'type' => $p->type,
            'purchased_at' => now(),
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addDays($p->valid_days)->toDateString(),
            'credit_total' => $p->credit_amount,
            'credit_used' => 0,
            'credit_remaining' => $p->credit_amount,
            'max_per_day' => $p->max_per_day,
            'status' => 'active',
        ]);
    }

    private function futureSession(): ClassSession
    {
        // เลือกคลาสประเภท trio-reformer ให้ตรงกับแพ็ก trio-10 ที่ givePackage() ใช้เป็นค่าเริ่มต้น
        // ไม่งั้นจะสุ่มได้คลาสประเภทที่แพ็กใช้ไม่ได้ แล้ว book() โยน package_not_valid_for_class (flaky)
        return ClassSession::where('start_at', '>', now()->addDays(2))
            ->whereHas('classType', fn ($q) => $q->where('code', 'trio-reformer'))
            ->orderBy('start_at')
            ->firstOrFail();
    }

    public function test_homepage_loads_for_guest(): void
    {
        $this->get('/')->assertOk()->assertSee('Drip Pilates', false);
    }

    public function test_homepage_loads_for_logged_in_customer(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);

        // หน้าแอปของลูกค้าอยู่ที่ route('home') = /customer ส่วน / เป็นหน้า marketing ที่ไม่โชว์ชื่อ
        $this->actingAs($customer, 'customer')
            ->get(route('home'))
            ->assertOk()
            ->assertSee($customer->first_name, false);
    }

    public function test_login_and_register_pages_load(): void
    {
        $this->get(route('customer.login'))->assertOk();
        $this->get(route('customer.register'))->assertOk();
    }

    public function test_customer_can_register(): void
    {
        $response = $this->post(route('customer.register'), [
            'first_name' => 'สมหญิง',
            'last_name' => 'ใจดี',
            'phone' => '0899999999',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('customers', ['phone' => '0899999999', 'first_name' => 'สมหญิง']);
        $this->assertAuthenticated('customer');
    }

    public function test_customer_can_login_with_phone(): void
    {
        $customer = $this->customer();

        $this->post(route('customer.login'), [
            'phone' => $customer->phone,
            'password' => 'secret123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_banned_customer_cannot_login(): void
    {
        $customer = $this->customer();
        $customer->update(['status' => 'banned']);

        $this->post(route('customer.login'), [
            'phone' => $customer->phone,
            'password' => 'secret123',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest('customer');
    }

    public function test_sessions_api_returns_html_for_branch_and_date(): void
    {
        $session = $this->futureSession();

        $response = $this->actingAs($this->customer(), 'customer')
            ->getJson(route('api.sessions', [
                'branch' => $session->branch_id,
                'date' => $session->start_at->toDateString(),
            ]));

        $response->assertOk()
            ->assertJsonStructure(['html', 'count']);

        $this->assertGreaterThan(0, $response->json('count'));
        $this->assertStringContainsString('class-card', $response->json('html'));
    }

    public function test_switching_branch_returns_different_classes(): void
    {
        $branches = Branch::orderBy('id')->take(2)->get();
        $date = $this->futureSession()->start_at->toDateString();

        $this->actingAs($this->customer(), 'customer');

        $first = $this->getJson(route('api.sessions', ['branch' => $branches[0]->id, 'date' => $date]));
        $second = $this->getJson(route('api.sessions', ['branch' => $branches[1]->id, 'date' => $date]));

        $first->assertOk();
        $second->assertOk();

        // สองสาขาต้องไม่คืน HTML ก้อนเดียวกัน
        $this->assertNotSame($first->json('html'), $second->json('html'));
    }

    public function test_guest_cannot_book(): void
    {
        $session = $this->futureSession();

        $this->postJson(route('customer.book', $session))->assertUnauthorized();
    }

    public function test_customer_can_book_a_class(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer);
        $session = $this->futureSession();

        $response = $this->actingAs($customer, 'customer')
            ->postJson(route('customer.book', $session));

        $response->assertOk()->assertJson(['ok' => true, 'status' => 'confirmed']);

        $this->assertSame(9, $package->fresh()->credit_remaining);
        $this->assertSame(1, $session->fresh()->booked_count);
    }

    public function test_booking_without_package_returns_friendly_error(): void
    {
        $customer = $this->customer();
        $session = $this->futureSession();

        $response = $this->withSession(['locale' => 'th'])
            ->actingAs($customer, 'customer')
            ->postJson(route('customer.book', $session));

        $response->assertStatus(422);
        $this->assertStringContainsString('แพ็กเกจ', $response->json('message'));
    }

    public function test_cancel_preview_warns_about_credit_loss(): void
    {
        $customer = $this->customer();
        $this->givePackage($customer);

        $session = $this->futureSession();
        $session->update(['start_at' => now()->addHours(2), 'end_at' => now()->addHours(3)]);

        $booking = app(\App\Services\BookingService::class)->book($customer, $session, 'admin');

        $response = $this->actingAs($customer, 'customer')
            ->getJson(route('customer.cancel.preview', $booking));

        $response->assertOk()->assertJson(['will_lose_credit' => true]);
    }

    public function test_customer_can_cancel_own_booking(): void
    {
        $customer = $this->customer();
        $package = $this->givePackage($customer);
        $session = $this->futureSession();

        $booking = app(\App\Services\BookingService::class)->book($customer, $session);
        $this->assertSame(9, $package->fresh()->credit_remaining);

        $this->actingAs($customer, 'customer')
            ->postJson(route('customer.cancel', $booking))
            ->assertOk()
            ->assertJson(['ok' => true, 'refunded' => true]);

        $this->assertSame(10, $package->fresh()->credit_remaining);
    }

    public function test_customer_cannot_cancel_someone_elses_booking(): void
    {
        $owner = $this->customer();
        $this->givePackage($owner);
        $booking = app(\App\Services\BookingService::class)->book($owner, $this->futureSession());

        $other = $this->customer();

        $this->actingAs($other, 'customer')
            ->postJson(route('customer.cancel', $booking))
            ->assertForbidden();
    }

    public function test_locale_switch_changes_page_language(): void
    {
        $this->actingAs($this->customer(), 'customer');
        $this->get(route('locale.set', 'en'));

        // "Overview" อยู่ในหน้าแอปของลูกค้า (route('home')) ไม่ใช่หน้า marketing ที่ /
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('Overview', false);
    }

    public function test_class_cards_show_english_when_locale_is_en(): void
    {
        $session = $this->futureSession();
        $this->get(route('locale.set', 'en'));

        $response = $this->actingAs($this->customer(), 'customer')
            ->getJson(route('api.sessions', [
                'branch' => $session->branch_id,
                'date' => $session->start_at->toDateString(),
            ]));

        $html = $response->json('html');
        $this->assertStringContainsString('Book Now', $html);
        $this->assertStringNotContainsString('จองเลย', $html);
    }
}

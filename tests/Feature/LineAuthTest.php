<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\PhoneVerificationCode;
use App\Services\LineLoginService;
use App\Services\PhoneVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class LineAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        config()->set('services.line.client_id', 'test-client');
        config()->set('services.line.client_secret', 'test-secret');
        config()->set('services.line.redirect', 'http://localhost/auth/line/callback');
    }

    /** ปลอม LINE ให้คืนโปรไฟล์ที่กำหนด จะได้ไม่ต้องยิง API จริง */
    private function fakeLine(array $profile): void
    {
        $mock = Mockery::mock(LineLoginService::class);
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('authorizeUrl')->andReturn('https://access.line.me/fake');
        $mock->shouldReceive('fetchProfile')->andReturn($profile);

        $this->app->instance(LineLoginService::class, $mock);
    }

    /** เดินผ่าน redirect ก่อนเพื่อให้ session มี state/nonce ที่ถูกต้อง */
    private function callbackWithState(): \Illuminate\Testing\TestResponse
    {
        $this->get(route('customer.line.redirect'));

        return $this->get(route('customer.line.callback', [
            'code' => 'fake-code',
            'state' => session('line_oauth_state'),
        ]));
    }

    public function test_new_line_user_gets_account_and_is_sent_to_complete_profile(): void
    {
        $this->fakeLine([
            'line_user_id' => 'U-new-001',
            'display_name' => 'คุณเอ',
            'picture_url' => 'https://example.com/a.jpg',
        ]);

        $this->callbackWithState()->assertRedirect(route('customer.profile.complete'));

        $customer = Customer::where('line_user_id', 'U-new-001')->first();

        $this->assertNotNull($customer);
        $this->assertSame('คุณเอ', $customer->first_name);
        $this->assertNull($customer->phone, 'สมัครผ่าน LINE ต้องยังไม่มีเบอร์');
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_returning_line_user_logs_straight_in(): void
    {
        $customer = Customer::create([
            'code' => 'DP-00900',
            'first_name' => 'กลับมา',
            'phone' => '0899999001',
            'line_user_id' => 'U-known-001',
            'status' => 'active',
            'profile_completed_at' => now(),
        ]);

        $this->fakeLine(['line_user_id' => 'U-known-001', 'display_name' => 'กลับมา']);

        $this->callbackWithState()->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($customer->fresh(), 'customer');
    }

    public function test_banned_line_user_cannot_log_in(): void
    {
        Customer::create([
            'code' => 'DP-00901',
            'first_name' => 'โดนแบน',
            'phone' => '0899999002',
            'line_user_id' => 'U-banned',
            'status' => 'banned',
            'profile_completed_at' => now(),
        ]);

        $this->fakeLine(['line_user_id' => 'U-banned', 'display_name' => 'โดนแบน']);

        $this->callbackWithState()->assertRedirect(route('customer.login'));
        $this->assertGuest('customer');
    }

    public function test_callback_with_bad_state_is_rejected(): void
    {
        $this->fakeLine(['line_user_id' => 'U-csrf', 'display_name' => 'x']);

        $this->get(route('customer.line.redirect'));

        $this->get(route('customer.line.callback', [
            'code' => 'fake-code',
            'state' => 'wrong-state',
        ]))->assertRedirect(route('customer.login'));

        $this->assertGuest('customer');
        $this->assertDatabaseMissing('customers', ['line_user_id' => 'U-csrf']);
    }

    public function test_free_phone_completes_profile_without_otp(): void
    {
        $this->fakeLine(['line_user_id' => 'U-free', 'display_name' => 'เบอร์ว่าง']);
        $this->callbackWithState();

        $this->post(route('customer.profile.complete.submit'), [
            'first_name' => 'สมชาย',
            'last_name' => 'ใจดี',
            'phone' => '081-234-5678',
        ])->assertRedirect(route('home'));

        $customer = Customer::where('line_user_id', 'U-free')->first();

        // เบอร์ต้องถูกล้างขีดออกให้เหลือตัวเลขล้วน
        $this->assertSame('0812345678', $customer->phone);
        $this->assertSame('สมชาย', $customer->first_name);
        $this->assertFalse($customer->needsProfileCompletion());
    }

    public function test_existing_phone_requires_otp_and_merges_into_original_account(): void
    {
        // บัญชีที่แอดมินสร้างไว้ พร้อมแพ็กเกจที่ต้องไม่หาย
        $original = Customer::create([
            'code' => 'DP-00500',
            'first_name' => 'ลูกค้าเก่า',
            'phone' => '0812345678',
            'status' => 'active',
            'profile_completed_at' => now(),
        ]);

        $package = Package::where('code', 'trio-10')->first();
        CustomerPackage::create([
            'code' => 'CP-MERGE-1',
            'customer_id' => $original->id,
            'package_id' => $package->id,
            'type' => $package->type,
            'purchased_at' => now(),
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addDays(60)->toDateString(),
            'credit_total' => 10,
            'credit_used' => 0,
            'credit_remaining' => 10,
            'status' => 'active',
        ]);

        $this->fakeLine(['line_user_id' => 'U-merge', 'display_name' => 'ลูกค้าเก่า']);
        $this->callbackWithState();

        $lineAccount = Customer::where('line_user_id', 'U-merge')->first();

        // กรอกเบอร์ที่ตรงกับบัญชีเดิม -> ต้องยังไม่ผูก ต้องขอ OTP ก่อน
        $this->post(route('customer.profile.complete.submit'), [
            'first_name' => 'ลูกค้าเก่า',
            'phone' => '0812345678',
        ])->assertRedirect(route('customer.profile.complete'));

        $this->assertNull($original->fresh()->line_user_id, 'ยังไม่ควรผูกก่อนยืนยัน OTP');
        $this->assertDatabaseHas('phone_verification_codes', ['phone' => '0812345678']);

        // ยืนยันด้วยรหัสที่ถูกต้อง
        $code = $this->latestOtpFor('0812345678');

        $this->post(route('customer.line.otp.verify'), ['code' => $code])
            ->assertRedirect(route('home'));

        $original->refresh();

        $this->assertSame('U-merge', $original->line_user_id, 'LINE ต้องย้ายมาอยู่บัญชีเดิม');
        $this->assertNotNull($original->phone_verified_at);
        $this->assertAuthenticatedAs($original, 'customer');

        // บัญชีที่สร้างจาก LINE ต้องถูกลบ และแพ็กเกจเดิมต้องอยู่ครบ
        $this->assertSoftDeleted('customers', ['id' => $lineAccount->id]);
        $this->assertSame(1, $original->packages()->count());
        $this->assertSame(10, $original->packages()->first()->credit_remaining);
    }

    public function test_wrong_otp_does_not_merge(): void
    {
        $original = Customer::create([
            'code' => 'DP-00501',
            'first_name' => 'ลูกค้าเก่า',
            'phone' => '0823456789',
            'status' => 'active',
            'profile_completed_at' => now(),
        ]);

        $this->fakeLine(['line_user_id' => 'U-wrong-otp', 'display_name' => 'x']);
        $this->callbackWithState();

        $this->post(route('customer.profile.complete.submit'), [
            'first_name' => 'x',
            'phone' => '0823456789',
        ]);

        $this->post(route('customer.line.otp.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertNull($original->fresh()->line_user_id);
    }

    public function test_phone_already_linked_to_another_line_is_refused(): void
    {
        Customer::create([
            'code' => 'DP-00502',
            'first_name' => 'มี LINE แล้ว',
            'phone' => '0834567890',
            'line_user_id' => 'U-other',
            'status' => 'active',
            'profile_completed_at' => now(),
        ]);

        $this->fakeLine(['line_user_id' => 'U-newcomer', 'display_name' => 'คนใหม่']);
        $this->callbackWithState();

        $this->post(route('customer.profile.complete.submit'), [
            'first_name' => 'คนใหม่',
            'phone' => '0834567890',
        ])->assertSessionHasErrors('phone');

        $this->assertDatabaseMissing('phone_verification_codes', ['phone' => '0834567890']);
    }

    public function test_incomplete_profile_cannot_book(): void
    {
        $this->fakeLine(['line_user_id' => 'U-nophone', 'display_name' => 'ยังไม่กรอก']);
        $this->callbackWithState();

        $this->artisan('classes:generate', ['--days' => 14]);
        $session = \App\Models\ClassSession::where('start_at', '>', now()->addDay())->first();

        $this->post(route('customer.book', $session))
            ->assertRedirect(route('customer.profile.complete'));
    }

    public function test_logged_in_customer_can_link_line_from_profile(): void
    {
        $customer = Customer::create([
            'code' => 'DP-00600',
            'first_name' => 'ผูกทีหลัง',
            'phone' => '0845678901',
            'password' => 'secret123',
            'status' => 'active',
            'profile_completed_at' => now(),
        ]);

        $this->actingAs($customer, 'customer');
        $this->fakeLine(['line_user_id' => 'U-late-link', 'display_name' => 'ผูกทีหลัง']);

        $this->callbackWithState()->assertRedirect(route('customer.profile.edit'));

        $this->assertSame('U-late-link', $customer->fresh()->line_user_id);
    }

    public function test_cannot_link_line_already_used_by_someone_else(): void
    {
        Customer::create([
            'code' => 'DP-00601',
            'first_name' => 'เจ้าของ LINE',
            'phone' => '0856789012',
            'line_user_id' => 'U-taken',
            'status' => 'active',
            'profile_completed_at' => now(),
        ]);

        $me = Customer::create([
            'code' => 'DP-00602',
            'first_name' => 'ฉัน',
            'phone' => '0867890123',
            'password' => 'secret123',
            'status' => 'active',
            'profile_completed_at' => now(),
        ]);

        $this->actingAs($me, 'customer');
        $this->fakeLine(['line_user_id' => 'U-taken', 'display_name' => 'x']);

        $this->callbackWithState();

        $this->assertNull($me->fresh()->line_user_id);
    }

    public function test_unlink_is_blocked_without_password(): void
    {
        $customer = Customer::create([
            'code' => 'DP-00700',
            'first_name' => 'ไม่มีรหัส',
            'phone' => '0878901234',
            'line_user_id' => 'U-nopass',
            'status' => 'active',
            'profile_completed_at' => now(),
        ]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.line.unlink'));

        $this->assertSame('U-nopass', $customer->fresh()->line_user_id,
            'ไม่มีรหัสผ่านแล้วปลด LINE จะเข้าระบบไม่ได้อีก ต้องกันไว้');
    }

    public function test_unlink_works_once_password_is_set(): void
    {
        $customer = Customer::create([
            'code' => 'DP-00701',
            'first_name' => 'มีรหัส',
            'phone' => '0889012345',
            'password' => 'secret123',
            'line_user_id' => 'U-haspass',
            'status' => 'active',
            'profile_completed_at' => now(),
        ]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.line.unlink'));

        $this->assertNull($customer->fresh()->line_user_id);
    }

    public function test_otp_is_rate_limited_within_cooldown(): void
    {
        $otp = app(PhoneVerificationService::class);

        $first = $otp->send('0891112222');
        $second = $otp->send('0891112222');

        $this->assertTrue($first['sent']);
        $this->assertFalse($second['sent'], 'ขอซ้ำภายใน 1 นาทีต้องไม่ส่ง');
        $this->assertGreaterThan(0, $second['wait_seconds']);
    }

    public function test_otp_rejects_after_too_many_wrong_attempts(): void
    {
        $otp = app(PhoneVerificationService::class);
        $otp->send('0892223333');

        for ($i = 0; $i < 5; $i++) {
            $otp->verify('0892223333', '000000');
        }

        // ถึงจะใส่รหัสถูกก็ต้องไม่ผ่านแล้ว เพราะเดาเกินโควตา
        $this->assertFalse($otp->verify('0892223333', $this->latestOtpFor('0892223333')));
    }

    public function test_expired_otp_is_rejected(): void
    {
        $otp = app(PhoneVerificationService::class);
        $otp->send('0893334444');

        $code = $this->latestOtpFor('0893334444');

        PhoneVerificationCode::where('phone', '0893334444')
            ->update(['expires_at' => now()->subMinute()]);

        $this->assertFalse($otp->verify('0893334444', $code));
    }

    public function test_phone_normalisation_handles_country_code(): void
    {
        $otp = app(PhoneVerificationService::class);

        $this->assertSame('0812345678', $otp->normalize('+66812345678'));
        $this->assertSame('0812345678', $otp->normalize('081-234-5678'));
        $this->assertSame('0812345678', $otp->normalize('081 234 5678'));
    }

    /** ดึงรหัส OTP ล่าสุดจาก log เพราะในฐานข้อมูลเก็บเป็น hash */
    private function latestOtpFor(string $phone): string
    {
        $logPath = storage_path('logs/laravel.log');
        $lines = array_reverse(file($logPath ?: '') ?: []);

        foreach ($lines as $line) {
            if (str_contains($line, '[OTP]') && str_contains($line, $phone)
                && preg_match('/"code":"(\d{6})"/', $line, $m)) {
                return $m[1];
            }
        }

        $this->fail("ไม่พบรหัส OTP สำหรับเบอร์ {$phone} ใน log");
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

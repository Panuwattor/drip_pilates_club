<?php

namespace Tests\Feature;

use App\Models\PhoneVerificationCode;
use App\Services\PhoneVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ทดสอบโหมด smsmkt โดยปลอม HTTP ไม่ยิง API จริง จะได้ไม่เสียเครดิต
 */
class SmsmktOtpTest extends TestCase
{
    use RefreshDatabase;

    private const SEND_URL = 'https://portal-otp.smsmkt.com/api/otp-send';
    private const VALIDATE_URL = 'https://portal-otp.smsmkt.com/api/otp-validate';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.sms.driver', 'smsmkt');
        config()->set('services.smsmkt.enabled', true);
        config()->set('services.smsmkt.api_key', 'test-key');
        config()->set('services.smsmkt.secret_key', 'test-secret');
        config()->set('services.smsmkt.project_key', 'test-project');
    }

    private function otp(): PhoneVerificationService
    {
        return app(PhoneVerificationService::class);
    }

    public function test_send_stores_provider_token_and_never_stores_the_code(): void
    {
        Http::fake([self::SEND_URL => Http::response([
            'code' => '000',
            'detail' => 'OK',
            'result' => ['token' => 'TOKEN-ABC'],
        ])]);

        $result = $this->otp()->send('0812345678');

        $this->assertTrue($result['sent']);
        $this->assertNull($result['debug_code'], 'โหมดจริงต้องไม่โชว์รหัสบนหน้าจอ');

        $record = PhoneVerificationCode::where('phone', '0812345678')->firstOrFail();

        $this->assertSame('smsmkt', $record->provider);
        $this->assertSame('TOKEN-ABC', $record->provider_token);
        $this->assertNull($record->code_hash, 'SMSMKT ถือรหัสเอง เราต้องไม่เก็บ');
    }

    public function test_send_forwards_credentials_as_headers(): void
    {
        Http::fake([self::SEND_URL => Http::response([
            'code' => '000', 'result' => ['token' => 'T'],
        ])]);

        $this->otp()->send('0812345678');

        Http::assertSent(function ($request) {
            return $request->hasHeader('api_key', 'test-key')
                && $request->hasHeader('secret_key', 'test-secret')
                && $request['project_key'] === 'test-project'
                && $request['phone'] === '0812345678';
        });
    }

    public function test_insufficient_credit_is_reported_not_swallowed(): void
    {
        Http::fake([self::SEND_URL => Http::response([
            'code' => '400',
            'detail' => "Can't send SMS, Credit balance is not enough",
        ])]);

        $result = $this->otp()->send('0812345678');

        $this->assertFalse($result['sent']);
        $this->assertStringContainsString('เครดิต', $result['error']);

        // ส่งไม่ออกต้องไม่สร้างแถวค้างไว้ ไม่งั้นจะติด cooldown ทั้งที่ยังไม่ได้ SMS
        $this->assertDatabaseCount('phone_verification_codes', 0);
    }

    public function test_invalid_api_key_is_reported(): void
    {
        Http::fake([self::SEND_URL => Http::response([
            'code' => '1004', 'detail' => 'API key or Secret key incorrect',
        ])]);

        $result = $this->otp()->send('0812345678');

        $this->assertFalse($result['sent']);
        $this->assertStringContainsString('API key', $result['error']);
    }

    public function test_verify_delegates_to_smsmkt_and_accepts_valid_code(): void
    {
        Http::fake([
            self::SEND_URL => Http::response(['code' => '000', 'result' => ['token' => 'TOKEN-OK']]),
            self::VALIDATE_URL => Http::response(['code' => '000', 'result' => ['status' => true]]),
        ]);

        $this->otp()->send('0812345678');

        $this->assertTrue($this->otp()->verify('0812345678', '123456'));

        Http::assertSent(fn ($r) => $r->url() === self::VALIDATE_URL
            && $r['token'] === 'TOKEN-OK'
            && $r['otp_code'] === '123456');

        $this->assertNotNull(
            PhoneVerificationCode::where('phone', '0812345678')->first()->verified_at
        );
    }

    /**
     * เคยพลาดตรงนี้ — สุ่ม ref_code ส่งไปเอง แล้ว validate ไม่ผ่านทุกครั้ง
     * SMSMKT ไม่ได้ใช้ ref_code กับ flow นี้ มีแค่ token ตัวเดียว
     */
    public function test_never_sends_ref_code_to_smsmkt(): void
    {
        Http::fake([
            self::SEND_URL => Http::response(['code' => '000', 'result' => ['token' => 'T']]),
            self::VALIDATE_URL => Http::response(['code' => '000', 'result' => ['status' => true]]),
        ]);

        $this->otp()->send('0812345678');
        $this->assertTrue($this->otp()->verify('0812345678', '123456'));

        Http::assertSent(fn ($r) => $r->url() === self::SEND_URL
            && ! array_key_exists('ref_code', $r->data()));

        Http::assertSent(fn ($r) => $r->url() === self::VALIDATE_URL
            && ! array_key_exists('ref_code', $r->data()));
    }

    /** ตอบกลับไม่มี ref_code ก็ต้องทำงานได้ปกติ — เราไม่ได้พึ่งค่านี้ */
    public function test_works_when_response_has_no_ref_code(): void
    {
        Http::fake([
            self::SEND_URL => Http::response(['code' => '000', 'result' => ['token' => 'TOKEN-OK']]),
            self::VALIDATE_URL => Http::response(['code' => '000', 'result' => ['status' => true]]),
        ]);

        $result = $this->otp()->send('0812345678');

        $this->assertTrue($result['sent']);
        $this->assertTrue($this->otp()->verify('0812345678', '123456'));
    }

    public function test_verify_rejects_when_smsmkt_says_status_false(): void
    {
        Http::fake([
            self::SEND_URL => Http::response(['code' => '000', 'result' => ['token' => 'T']]),
            self::VALIDATE_URL => Http::response(['code' => '000', 'result' => ['status' => false]]),
        ]);

        $this->otp()->send('0812345678');

        $this->assertFalse($this->otp()->verify('0812345678', '999999'));
    }

    public function test_verify_rejects_expired_token_from_provider(): void
    {
        Http::fake([
            self::SEND_URL => Http::response(['code' => '000', 'result' => ['token' => 'T']]),
            self::VALIDATE_URL => Http::response(['code' => '5000', 'detail' => 'token expire']),
        ]);

        $this->otp()->send('0812345678');

        $this->assertFalse($this->otp()->verify('0812345678', '123456'));
    }

    public function test_attempt_limit_applies_in_smsmkt_mode_too(): void
    {
        Http::fake([
            self::SEND_URL => Http::response(['code' => '000', 'result' => ['token' => 'T']]),
            self::VALIDATE_URL => Http::response(['code' => '000', 'result' => ['status' => false]]),
        ]);

        $this->otp()->send('0812345678');

        for ($i = 0; $i < 5; $i++) {
            $this->otp()->verify('0812345678', '000000');
        }

        // ครั้งที่ 6 ต้องถูกปฏิเสธที่ฝั่งเราเลย ไม่ควรยิงไป SMSMKT อีก
        Http::fake([
            self::VALIDATE_URL => Http::response(['code' => '000', 'result' => ['status' => true]]),
        ]);

        $this->assertFalse($this->otp()->verify('0812345678', '123456'));
    }

    public function test_network_failure_does_not_leak_exception(): void
    {
        Http::fake([self::SEND_URL => fn () => throw new \RuntimeException('connection refused')]);

        $result = $this->otp()->send('0812345678');

        $this->assertFalse($result['sent']);
        $this->assertStringContainsString('เชื่อมต่อ', $result['error']);
    }

    public function test_cooldown_blocks_immediate_resend_without_calling_provider(): void
    {
        Http::fake([self::SEND_URL => Http::response([
            'code' => '000', 'result' => ['token' => 'T'],
        ])]);

        $this->otp()->send('0812345678');
        $second = $this->otp()->send('0812345678');

        $this->assertFalse($second['sent']);
        $this->assertGreaterThan(0, $second['wait_seconds']);

        // ต้องยิงไป SMSMKT แค่ครั้งเดียว ครั้งที่สองกันไว้ตั้งแต่ฝั่งเรา = ไม่เปลืองเครดิต
        Http::assertSentCount(1);
    }

    public function test_falls_back_to_log_driver_when_disabled(): void
    {
        config()->set('services.smsmkt.enabled', false);
        Http::fake();

        $result = $this->otp()->send('0812345678');

        $this->assertTrue($result['sent']);
        $this->assertNotNull($result['debug_code'], 'โหมด log ต้องคืนรหัสมาให้ทดสอบ');

        Http::assertNothingSent();
    }
}

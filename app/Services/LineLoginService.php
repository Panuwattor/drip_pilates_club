<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * LINE Login v2.1 (OAuth2 + OpenID Connect)
 * ไม่ใช้ Socialite เพราะต้องการแค่ provider เดียว เรียก endpoint ตรงๆ ง่ายกว่า
 *
 * เอกสาร: https://developers.line.biz/en/docs/line-login/integrate-line-login/
 */
class LineLoginService
{
    private const AUTHORIZE_URL = 'https://access.line.me/oauth2/v2.1/authorize';
    private const TOKEN_URL = 'https://api.line.me/oauth2/v2.1/token';
    private const VERIFY_URL = 'https://api.line.me/oauth2/v2.1/verify';
    private const PROFILE_URL = 'https://api.line.me/v2/profile';

    public function isConfigured(): bool
    {
        return filled(config('services.line.client_id'))
            && filled(config('services.line.client_secret'));
    }

    /** URL ที่ให้ผู้ใช้เด้งไปหน้า LINE เพื่ออนุญาต */
    public function authorizeUrl(string $state, string $nonce): string
    {
        $this->assertConfigured();

        return self::AUTHORIZE_URL . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.line.client_id'),
            'redirect_uri' => config('services.line.redirect'),
            'state' => $state,
            'scope' => 'openid profile',
            'nonce' => $nonce,
            // บังคับให้เลือกบัญชีใหม่ทุกครั้ง กันเครื่องที่ใช้ร่วมกันเผลอเข้าบัญชีคนอื่น
            'prompt' => 'consent',
        ]);
    }

    /**
     * แลก authorization code เป็นข้อมูลผู้ใช้
     * คืน ['line_user_id', 'display_name', 'picture_url', 'email']
     */
    public function fetchProfile(string $code, string $expectedNonce): array
    {
        $this->assertConfigured();

        $token = $this->exchangeCode($code);
        $profile = [];

        // id_token มีข้อมูลครบและถูกเซ็นมาแล้ว ให้ LINE ช่วย verify ให้
        if (! empty($token['id_token'])) {
            $profile = $this->verifyIdToken($token['id_token'], $expectedNonce);
        }

        // เผื่อ id_token ไม่มีรูป/ชื่อ ค่อยยิง profile endpoint เสริม
        if (empty($profile['line_user_id']) || empty($profile['display_name'])) {
            $profile = array_merge($profile, $this->fetchUserProfile($token['access_token']));
        }

        if (empty($profile['line_user_id'])) {
            throw new RuntimeException('LINE ไม่ส่งรหัสผู้ใช้กลับมา');
        }

        return $profile;
    }

    private function exchangeCode(string $code): array
    {
        $response = Http::asForm()
            ->timeout(15)
            ->post(self::TOKEN_URL, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => config('services.line.redirect'),
                'client_id' => config('services.line.client_id'),
                'client_secret' => config('services.line.client_secret'),
            ]);

        if ($response->failed()) {
            throw new RuntimeException('แลก token กับ LINE ไม่สำเร็จ: ' . $response->body());
        }

        return $response->json();
    }

    /** ให้ LINE ตรวจลายเซ็น id_token แทนเรา จะได้ไม่ต้องทำ JWT เอง */
    private function verifyIdToken(string $idToken, string $expectedNonce): array
    {
        $response = Http::asForm()
            ->timeout(15)
            ->post(self::VERIFY_URL, [
                'id_token' => $idToken,
                'client_id' => config('services.line.client_id'),
                'nonce' => $expectedNonce,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('ตรวจสอบ id_token ไม่ผ่าน: ' . $response->body());
        }

        $data = $response->json();

        return array_filter([
            'line_user_id' => $data['sub'] ?? null,
            'display_name' => $data['name'] ?? null,
            'picture_url' => $data['picture'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
    }

    private function fetchUserProfile(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->timeout(15)
            ->get(self::PROFILE_URL);

        if ($response->failed()) {
            throw new RuntimeException('ดึงโปรไฟล์ LINE ไม่สำเร็จ: ' . $response->body());
        }

        $data = $response->json();

        return array_filter([
            'line_user_id' => $data['userId'] ?? null,
            'display_name' => $data['displayName'] ?? null,
            'picture_url' => $data['pictureUrl'] ?? null,
        ]);
    }

    private function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('ยังไม่ได้ตั้งค่า LINE Login กรุณากรอก LINE_CLIENT_ID และ LINE_CLIENT_SECRET ใน .env');
        }
    }
}

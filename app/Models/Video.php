<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasTranslatedFields;

    protected $translatable = ['title', 'caption'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * เดา provider จาก URL — เรียกตอนบันทึกใน controller
     * ถ้าเดาไม่ออกให้ default เป็น instagram (ค่าเดิม) แล้วแอดมินแก้เองได้
     */
    public static function detectProvider(string $url): string
    {
        $url = strtolower($url);

        return match (true) {
            str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be') => 'youtube',
            str_contains($url, 'tiktok.com') => 'tiktok',
            str_contains($url, 'facebook.com') || str_contains($url, 'fb.watch') => 'facebook',
            default => 'instagram',
        };
    }

    /**
     * ดึง YouTube video id จากรูปแบบ url ที่พบบ่อย
     * (watch?v=, youtu.be/, shorts/, embed/)
     */
    public function youtubeId(): ?string
    {
        if ($this->provider !== 'youtube') {
            return null;
        }

        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/|v/))([A-Za-z0-9_-]{11})~', $this->url, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * ทำ url ให้เป็นรูปแบบมาตรฐานสำหรับ oEmbed/embed ของแต่ละเจ้า
     * โดยตัด query string ที่ไม่จำเป็นออก
     */
    public function canonicalUrl(): string
    {
        // ตัด query/fragment ทิ้ง กัน utm_* หลุดเข้า embed
        return strtok($this->url, '?#') ?: $this->url;
    }

    /**
     * URL สำหรับฝัง iframe เล่นในหน้าเว็บเลย (ไม่เด้งออกแอป)
     * คืน null ถ้า provider นั้นฝังตรงไม่ได้ — ให้ fallback ไปเปิดลิงก์
     */
    public function embedIframeUrl(): ?string
    {
        return match ($this->provider) {
            'youtube' => $this->youtubeId()
                ? 'https://www.youtube.com/embed/' . $this->youtubeId() . '?autoplay=1&rel=0'
                : null,
            // Instagram embed ทางการ เล่นในหน้าเว็บได้ ไม่เด้งไปแอป
            'instagram' => rtrim($this->canonicalUrl(), '/') . '/embed/',
            // TikTok embed v2 รับ video id
            'tiktok' => ($id = $this->tiktokId()) ? 'https://www.tiktok.com/embed/v2/' . $id : null,
            // Facebook video plugin
            'facebook' => 'https://www.facebook.com/plugins/video.php?href='
                . rawurlencode($this->canonicalUrl()) . '&show_text=false&autoplay=true',
            default => null,
        };
    }

    /** ดึง video id ของ TikTok จาก url (.../video/1234567890) */
    public function tiktokId(): ?string
    {
        if (preg_match('~/video/(\d+)~', $this->url, $m)) {
            return $m[1];
        }

        return null;
    }

    public function embedType(): string
    {
        return $this->provider;
    }

    /**
     * รูปปกที่ใช้แสดงในการ์ด: รูปที่แอดมินอัปเองมาก่อน ถ้าไม่มีใช้รูปที่ดึงมาอัตโนมัติ
     * คืน URL พร้อมใช้ใน <img src> (thumbnail เป็น relative/asset, remote เป็น URL เต็ม)
     */
    public function posterUrl(): ?string
    {
        if ($this->thumbnail) {
            return asset($this->thumbnail);
        }

        return $this->remote_thumbnail ?: null;
    }
}

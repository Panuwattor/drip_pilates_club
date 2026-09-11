<?php

namespace App\Support;

use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ดึงรูปปก (cover) ของคลิปแบบอัตโนมัติ เพื่อเอาไปทำการ์ด poster + ปุ่ม play
 *  - YouTube : ได้จาก video id ตรงๆ ไม่ต้องยิงเน็ต
 *  - IG/TikTok/FB : โหลดหน้า public ของคลิปแล้วอ่าน og:image
 * คืน null ถ้าดึงไม่ได้ (แอดมินอัปรูปเองแทนได้)
 */
class VideoThumbnailFetcher
{
    public static function fetch(Video $video): ?string
    {
        try {
            return match ($video->provider) {
                'youtube' => static::youtube($video),
                default => static::ogImage($video->canonicalUrl()),
            };
        } catch (\Throwable $e) {
            Log::info('video thumbnail fetch failed', ['url' => $video->url, 'err' => $e->getMessage()]);

            return null;
        }
    }

    protected static function youtube(Video $video): ?string
    {
        $id = $video->youtubeId();

        return $id ? "https://i.ytimg.com/vi/{$id}/hqdefault.jpg" : null;
    }

    /**
     * โหลดหน้า public ของคลิปแล้วดึง <meta property="og:image">
     * ใส่ user-agent ปกติ กัน IG/TikTok ตอบหน้า login เปล่า
     */
    protected static function ogImage(string $url): ?string
    {
        $res = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
                . '(KHTML, like Gecko) Chrome/122.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml',
        ])->timeout(8)->get($url);

        if (! $res->ok()) {
            return null;
        }

        $html = $res->body();

        // รองรับทั้ง property="og:image" และ name="og:image" สลับลำดับ attribute
        foreach (['og:image:secure_url', 'og:image'] as $prop) {
            if (preg_match(
                '~<meta[^>]+(?:property|name)=["\']' . preg_quote($prop, '~') . '["\'][^>]+content=["\']([^"\']+)["\']~i',
                $html,
                $m
            )) {
                return html_entity_decode($m[1]);
            }
            if (preg_match(
                '~<meta[^>]+content=["\']([^"\']+)["\'][^>]+(?:property|name)=["\']' . preg_quote($prop, '~') . '["\']~i',
                $html,
                $m
            )) {
                return html_entity_decode($m[1]);
            }
        }

        return null;
    }
}

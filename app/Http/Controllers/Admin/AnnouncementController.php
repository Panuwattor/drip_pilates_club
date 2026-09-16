<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Branch;
use App\Support\MediaStorage;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::with('branch')->latest('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.announcements.form', [
            'announcement' => new Announcement,
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $this->handleImage($request);

        Announcement::create($data);

        return redirect()->route('admin.announcements.index')
            ->with('status', __t('เพิ่มประกาศเรียบร้อยแล้ว', 'Article added'));
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.form', [
            'announcement' => $announcement,
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $this->validated($request);

        if ($image = $this->handleImage($request)) {
            $data['image'] = $image;
        }

        $announcement->update($data);

        return back()->with('status', __t('บันทึกประกาศแล้ว', 'Article saved'));
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('status', __t('ลบประกาศแล้ว', 'Article deleted'));
    }

    private function handleImage(Request $request): ?string
    {
        if (! $request->hasFile('image_file')) {
            return null;
        }

        return MediaStorage::store($request->file('image_file'), 'announcements');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'title_th' => ['required', 'string', 'max:200'],
            'title_en' => ['required', 'string', 'max:200'],
            'body_th' => ['nullable', 'string'],
            'body_en' => ['nullable', 'string'],
            'image_file' => ['nullable', 'image', 'max:2048'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'type' => ['required', 'in:info,promo,warning'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        unset($data['image_file']);

        $data['body_th'] = $this->sanitizeRichText($data['body_th'] ?? null);
        $data['body_en'] = $this->sanitizeRichText($data['body_en'] ?? null);

        return $data + [
            'is_active' => $request->boolean('is_active'),
            'show_on_homepage' => $request->boolean('show_on_homepage'),
            'show_on_customer' => $request->boolean('show_on_customer'),
        ];
    }

    private function sanitizeRichText(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        $allowed = '<p><br><strong><b><em><i><u><s><a><ul><ol><li><h2><h3><blockquote>';
        $clean = strip_tags($html, $allowed);

        // ตัดแท็กที่ไม่อนุญาตทิ้งก่อน เหลือไว้เฉพาะรายชื่อข้างบน
        $clean = preg_replace('/<(?!\/?(?:p|br|strong|b|em|i|u|s|a|ul|ol|li|h2|h3|blockquote)\b)[^>]*>/i', '', $clean);

        // ตัด attribute ทั้งหมดออกยกเว้น href ของลิงก์ กัน onerror/onclick แทรกผ่าน rich text editor
        // ต้องกวาดทุกแท็กที่อนุญาต ไม่ใช่แค่ <a> ไม่งั้น <p onclick="..."> หลุดไปถึงหน้าบทความ
        return preg_replace_callback(
            '/<([a-z0-9]+)(\s[^>]*)?>/i',
            function ($m) {
                $tag = strtolower($m[1]);

                if ($tag !== 'a') {
                    return "<{$tag}>";
                }

                $attrs = $m[2] ?? '';

                if (! preg_match('/\bhref\s*=\s*(["\'])(.*?)\1/i', $attrs, $hrefMatch)) {
                    return '<a>';
                }

                $href = html_entity_decode($hrefMatch[2], ENT_QUOTES, 'UTF-8');
                $href = trim(preg_replace('/[\x00-\x20]/', '', $href));

                // รับเฉพาะลิงก์ http(s) กับลิงก์ภายในเว็บ กัน javascript:/data: ที่รันสคริปต์ได้
                // ปฏิเสธ // นำหน้า (protocol-relative) ด้วย ไม่งั้น /promo กลายเป็นลิงก์ออกนอกเว็บได้
                if (! preg_match('#^(https?://|/(?!/))#i', $href)) {
                    return '<a>';
                }

                return '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '" target="_blank" rel="noopener noreferrer">';
            },
            $clean
        );
    }
}

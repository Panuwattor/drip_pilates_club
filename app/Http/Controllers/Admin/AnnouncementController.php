<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            ->with('status', 'เพิ่มประกาศเรียบร้อยแล้ว');
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

        return back()->with('status', 'บันทึกประกาศแล้ว');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('status', 'ลบประกาศแล้ว');
    }

    private function handleImage(Request $request): ?string
    {
        if (! $request->hasFile('image_file')) {
            return null;
        }

        $path = $request->file('image_file')->store('announcements', 'public');

        return 'storage/' . $path;
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

        return $data + ['is_active' => $request->boolean('is_active')];
    }

    private function sanitizeRichText(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        $allowed = '<p><br><strong><b><em><i><u><s><a><ul><ol><li><h2><h3><blockquote>';
        $clean = strip_tags($html, $allowed);

        // ตัด attribute ทั้งหมดออกยกเว้น href ของลิงก์ กัน onerror/onclick แทรกผ่าน rich text editor
        $clean = preg_replace_callback('/<a\s+[^>]*href="([^"]*)"[^>]*>/i', function ($m) {
            $href = $m[1];
            if (! preg_match('#^(https?://|/)#i', $href)) {
                return '<a>';
            }
            return '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '" target="_blank" rel="noopener noreferrer">';
        }, $clean);

        return preg_replace('/<(?!\/?(?:p|br|strong|b|em|i|u|s|a|ul|ol|li|h2|h3|blockquote)\b)[^>]+>/i', '', $clean);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Support\MediaStorage;
use App\Support\VideoThumbnailFetcher;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        return view('admin.videos.index', [
            'videos' => Video::orderBy('sort_order')->latest('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.videos.form', ['video' => new Video]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['thumbnail'] = $this->handleThumbnail($request) ?? $data['thumbnail'] ?? null;

        $video = Video::create($data);

        // ดึงรูปปกอัตโนมัติจากตัวคลิป (ถ้าแอดมินไม่ได้อัปรูปเอง)
        if (! $video->thumbnail) {
            $video->update(['remote_thumbnail' => VideoThumbnailFetcher::fetch($video)]);
        }

        return redirect()->route('admin.videos.index')
            ->with('status', 'เพิ่มคลิปเรียบร้อยแล้ว');
    }

    public function edit(Video $video)
    {
        return view('admin.videos.form', ['video' => $video]);
    }

    public function update(Request $request, Video $video)
    {
        $data = $this->validated($request);

        $urlChanged = $video->url !== $data['url'];

        if ($thumb = $this->handleThumbnail($request)) {
            $data['thumbnail'] = $thumb;
        } else {
            unset($data['thumbnail']);
        }

        $video->update($data);

        // ถ้าเปลี่ยน url และไม่มีรูปที่อัปเอง ดึงรูปปกใหม่ให้
        if ($urlChanged && ! $video->thumbnail) {
            $video->update(['remote_thumbnail' => VideoThumbnailFetcher::fetch($video)]);
        }

        return back()->with('status', 'บันทึกคลิปแล้ว');
    }

    public function destroy(Video $video)
    {
        $video->delete();

        return redirect()->route('admin.videos.index')->with('status', 'ลบคลิปแล้ว');
    }

    private function handleThumbnail(Request $request): ?string
    {
        if (! $request->hasFile('thumbnail_file')) {
            return null;
        }

        return MediaStorage::store($request->file('thumbnail_file'), 'videos');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title_th' => ['nullable', 'string', 'max:200'],
            'title_en' => ['nullable', 'string', 'max:200'],
            'caption_th' => ['nullable', 'string', 'max:1000'],
            'caption_en' => ['nullable', 'string', 'max:1000'],
            'url' => ['required', 'url', 'max:500'],
            // provider เลือกเองได้ ถ้าเว้นว่างระบบเดาจาก url ให้
            'provider' => ['nullable', 'in:instagram,youtube,tiktok,facebook'],
            'thumbnail_file' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        unset($data['thumbnail_file']);

        // ถ้าแอดมินไม่เลือก provider ให้เดาจาก url
        $data['provider'] = $data['provider'] ?? Video::detectProvider($data['url']);

        return $data + [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }
}

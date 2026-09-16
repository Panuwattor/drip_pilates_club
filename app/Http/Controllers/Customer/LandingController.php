<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Branch;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Trainer;
use App\Models\Video;

class LandingController extends Controller
{
    /** หน้าแรกสาธารณะ — แนะนำสตูดิโอ ก่อนเข้าสู่ระบบไปหน้าแอป */
    public function index()
    {
        // แพ็กเกจ public ทั้งหมด ไว้เช็คว่ามีมากกว่าที่โชว์ไหม
        $publicPackages = Package::active()->public()->with('classTypes')->orderBy('sort_order')->get();

        // หน้าแรกโชว์แค่ตัวเด่น 1 แพ็กต่อประเภทคลาส ไม่ให้รก (ที่เหลือกดดูที่ /packages)
        // เลือกแพ็กที่คุ้มสุดของแต่ละประเภท = ราคาต่อคลาสต่ำสุด แต่ไม่ใช่แพ็กครั้งเดียว
        $featured = $publicPackages
            ->groupBy(fn ($p) => $p->classTypes->first()?->id ?? 0)
            ->map(fn ($group) => $group->where('credit_amount', '>', 1)->sortBy('price_per_class')->first() ?? $group->first())
            ->filter()
            ->sortBy('sort_order')
            ->take(3)
            ->values();

        return view('landing', [
            'branches' => Branch::active()->orderBy('sort_order')->get(),
            'packages' => $featured,
            'hasMorePackages' => $publicPackages->count() > $featured->count(),
            'trainers' => Trainer::active()->orderBy('sort_order')->take(8)->get(),
            'announcements' => Announcement::visible()
                ->where('show_on_homepage', true)
                ->whereNull('branch_id')
                ->orderByDesc('starts_at')
                ->orderBy('sort_order')
                ->take(6)
                ->get(),
            // หน้าแรกโชว์แบบสไลด์ ไม่กี่คลิปพอ ที่เหลือกดดูได้ที่หน้า /videos
            'videos' => Video::active()->orderBy('sort_order')->latest('id')->take(6)->get(),
            'hasMoreVideos' => Video::active()->count() > 6,
            'contacts' => $this->contacts(),
        ]);
    }

    /** หน้ารวมคลิปทั้งหมด — เปิดดูได้แม้ไม่ล็อกอิน */
    public function videos()
    {
        return view('videos', [
            'videos' => Video::active()->orderBy('sort_order')->latest('id')->get(),
            'contacts' => $this->contacts(),
        ]);
    }

    /** หน้ารวมแพ็กเกจทั้งหมด — จัดกลุ่มตามประเภทคลาส เปิดดูได้แม้ไม่ล็อกอิน */
    public function packages()
    {
        $packages = Package::active()->public()
            ->with('classTypes')
            ->orderBy('sort_order')
            ->get();

        // จัดกลุ่มตามประเภทคลาสหลักของแพ็ก (แพ็กที่ไม่ผูกคลาสไปอยู่กลุ่ม "อื่นๆ")
        $groups = $packages->groupBy(fn ($p) => $p->classTypes->first()?->name ?? __t('อื่นๆ', 'Others'));

        return view('packages', [
            'groups' => $groups,
            'contacts' => $this->contacts(),
        ]);
    }

    /** ช่องทางติดต่อของเจ้าของ ใช้ร่วมทุกสาขา ดึงจากตั้งค่าระบบ */
    private function contacts(): array
    {
        return array_filter([
            'line' => Setting::get('contact_line_url'),
            'facebook' => Setting::get('contact_facebook_url'),
            'tiktok' => Setting::get('contact_tiktok_url'),
            'instagram' => Setting::get('contact_instagram_url'),
        ]);
    }

    /** หน้ารายละเอียดบทความ/ประกาศ — เปิดดูได้แม้ไม่ล็อกอิน */
    public function article(Announcement $announcement)
    {
        abort_unless($announcement->isVisible(), 404);

        $related = Announcement::visible()
            ->whereKeyNot($announcement->id)
            ->when($announcement->branch_id, fn ($q) => $q->where(fn ($q2) => $q2->whereNull('branch_id')->orWhere('branch_id', $announcement->branch_id)))
            ->orderByDesc('starts_at')
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        return view('article', [
            'announcement' => $announcement,
            'related' => $related,
        ]);
    }
}

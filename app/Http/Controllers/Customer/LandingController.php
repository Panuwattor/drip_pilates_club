<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Branch;
use App\Models\Package;
use App\Models\Trainer;

class LandingController extends Controller
{
    /** หน้าแรกสาธารณะ — แนะนำสตูดิโอ ก่อนเข้าสู่ระบบไปหน้าแอป */
    public function index()
    {
        return view('landing', [
            'branches' => Branch::active()->orderBy('sort_order')->get(),
            'packages' => Package::active()->public()->orderBy('sort_order')->get(),
            'trainers' => Trainer::active()->orderBy('sort_order')->take(8)->get(),
            'announcements' => Announcement::visible()
                ->whereNull('branch_id')
                ->orderByDesc('starts_at')
                ->orderBy('sort_order')
                ->take(6)
                ->get(),
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

<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Trainer;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * ตารางสอนของครู เปิดดูได้โดยไม่ต้องล็อกอิน ผ่าน token ที่แอดมินส่งให้
 * ดูอย่างเดียว แก้อะไรไม่ได้
 */
class ScheduleController extends Controller
{
    public function show(Request $request, string $token)
    {
        $trainer = Trainer::where('public_token', $token)->firstOrFail();

        abort_unless($trainer->is_active, 404);

        $view = $request->input('view', 'week');
        $date = Carbon::parse($request->input('date', now()->toDateString()));

        [$start, $end] = match ($view) {
            'day' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'month' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            default => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
        };

        // รวมทั้งรอบที่เป็นครูหลักและรอบที่ไปสอนแทน
        $sessions = ClassSession::with(['classType', 'branch', 'room', 'trainer'])
            ->where(fn ($q) => $q
                ->where(fn ($w) => $w->where('trainer_id', $trainer->id)->whereNull('substitute_trainer_id'))
                ->orWhere('substitute_trainer_id', $trainer->id))
            ->whereBetween('start_at', [$start, $end])
            ->where('status', 'scheduled')
            ->orderBy('start_at')
            ->get();

        return view('trainer.schedule', [
            'trainer' => $trainer,
            'sessions' => $sessions->groupBy(fn ($s) => $s->start_at->toDateString()),
            'view' => $view,
            'date' => $date,
            'start' => $start,
            'end' => $end,
            'totalSessions' => $sessions->count(),
            'totalStudents' => $sessions->sum('booked_count'),
        ]);
    }

    /** รายชื่อผู้เรียนในรอบนั้น พร้อมหมายเหตุสุขภาพ */
    public function session(string $token, ClassSession $session)
    {
        $trainer = Trainer::where('public_token', $token)->firstOrFail();

        abort_unless($trainer->is_active, 404);

        // ครูดูได้เฉพาะรอบที่ตัวเองสอน
        $isMine = ($session->trainer_id === $trainer->id && ! $session->substitute_trainer_id)
            || $session->substitute_trainer_id === $trainer->id;

        abort_unless($isMine, 403);

        $session->load(['classType', 'branch', 'room']);

        $bookings = Booking::with('customer')
            ->where('class_session_id', $session->id)
            ->whereIn('status', ['confirmed', 'attended', 'waitlisted'])
            ->get()
            ->sortBy(fn ($b) => [$b->status === 'waitlisted' ? 1 : 0, $b->waitlist_position ?? 0, $b->id])
            ->values();

        return view('trainer.session', compact('trainer', 'session', 'bookings'));
    }
}

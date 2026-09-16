<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\ClassSession;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());
        $branchIds = auth()->user()->accessibleBranchIds();

        // รายได้แยกสาขา
        $revenueByBranch = Order::selectRaw('branch_id, SUM(total) total, COUNT(*) orders')
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('branch_id')
            ->get()
            ->map(fn ($r) => [
                'branch' => Branch::find($r->branch_id)?->name ?? __t('ไม่ระบุสาขา', 'No branch'),
                'total' => (float) $r->total,
                'orders' => (int) $r->orders,
            ]);

        // สถิติการจอง
        $bookingStats = Booking::selectRaw('status, COUNT(*) c')
            ->whereHas('classSession', fn ($q) => $q
                ->whereIn('branch_id', $branchIds)
                ->whereBetween('start_at', [$from . ' 00:00:00', $to . ' 23:59:59']))
            ->groupBy('status')
            ->pluck('c', 'status');

        $totalBookings = $bookingStats->sum();
        $attended = (int) ($bookingStats['attended'] ?? 0);
        $noShow = (int) ($bookingStats['no_show'] ?? 0);

        // อันดับคลาสยอดนิยม
        $popularClasses = ClassSession::selectRaw('class_type_id, SUM(booked_count) booked, COUNT(*) sessions')
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('start_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('class_type_id')
            ->orderByDesc('booked')
            ->with('classType')
            ->take(10)
            ->get();

        // อันดับครูยอดนิยม
        $popularTrainers = ClassSession::selectRaw('trainer_id, SUM(booked_count) booked, COUNT(*) sessions')
            ->whereIn('branch_id', $branchIds)
            ->whereNotNull('trainer_id')
            ->whereBetween('start_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('trainer_id')
            ->orderByDesc('booked')
            ->with('trainer')
            ->take(10)
            ->get();

        // อัตราการเต็มของคลาส
        $fillData = ClassSession::selectRaw('SUM(capacity) cap, SUM(booked_count) booked')
            ->whereIn('branch_id', $branchIds)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->first();

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'revenueByBranch' => $revenueByBranch,
            'totalRevenue' => $revenueByBranch->sum('total'),
            'bookingStats' => $bookingStats,
            'totalBookings' => $totalBookings,
            'attendanceRate' => $totalBookings > 0 ? round($attended / $totalBookings * 100, 1) : 0,
            'noShowRate' => $totalBookings > 0 ? round($noShow / $totalBookings * 100, 1) : 0,
            'popularClasses' => $popularClasses,
            'popularTrainers' => $popularTrainers,
            'fillRate' => ($fillData->cap ?? 0) > 0
                ? round($fillData->booked / $fillData->cap * 100, 1)
                : 0,
        ]);
    }
}

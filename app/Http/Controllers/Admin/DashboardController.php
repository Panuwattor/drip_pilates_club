<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\ClassSession;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $branchIds = auth()->user()->accessibleBranchIds();
        $today = now()->toDateString();

        $todaySessions = ClassSession::with(['classType', 'trainer', 'branch', 'room'])
            ->whereIn('branch_id', $branchIds)
            ->whereDate('start_at', $today)
            ->where('status', 'scheduled')
            ->orderBy('start_at')
            ->get();

        $stats = [
            'today_classes' => $todaySessions->count(),
            'today_bookings' => Booking::whereHas('classSession', fn ($q) => $q
                    ->whereIn('branch_id', $branchIds)->whereDate('start_at', $today))
                ->whereIn('status', ['confirmed', 'attended'])
                ->count(),
            'active_customers' => Customer::where('status', 'active')->count(),
            'month_revenue' => Order::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total'),
        ];

        // อัตราการเต็มของคลาสวันนี้
        $capacity = $todaySessions->sum('capacity');
        $booked = $todaySessions->sum('booked_count');
        $stats['fill_rate'] = $capacity > 0 ? round($booked / $capacity * 100) : 0;

        $pendingPayments = Payment::where('status', 'pending')->count();

        // แพ็กใกล้หมดอายุใน 14 วัน ไว้ให้แอดมินโทรตาม
        $expiringPackages = CustomerPackage::with(['customer', 'package'])
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', now())
            ->whereDate('expires_at', '<=', now()->addDays(14))
            ->orderBy('expires_at')
            ->take(8)
            ->get();

        $recentBookings = Booking::with(['customer', 'classSession.classType', 'classSession.branch'])
            ->latest('id')
            ->take(8)
            ->get();

        // ยอดจอง 14 วันย้อนหลัง ไว้วาดกราฟ
        $trend = Booking::selectRaw('DATE(booked_at) d, COUNT(*) c')
            ->where('booked_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('d')
            ->pluck('c', 'd');

        $chart = collect(range(13, 0))->map(function ($daysAgo) use ($trend) {
            $date = now()->subDays($daysAgo)->toDateString();

            return [
                'label' => now()->subDays($daysAgo)->format('j/n'),
                'value' => (int) ($trend[$date] ?? 0),
            ];
        });

        return view('admin.dashboard', compact(
            'stats', 'todaySessions', 'pendingPayments', 'expiringPackages', 'recentBookings', 'chart'
        ));
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();

        $recentBookings = Booking::with(['property', 'guest'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'guest_name' => $b->guest ? $b->guest->fullName() : '—',
                'property_name' => $b->property?->name ?? '—',
                'check_in' => $b->check_in?->format('d.m.Y'),
                'check_out' => $b->check_out?->format('d.m.Y'),
                'status' => $b->status,
                'total_price' => $b->total_price,
            ]);

        $upcomingBookings = Booking::with(['property', 'guest'])
            ->where('check_in', '>=', $now->toDateString())
            ->whereIn('status', ['confirmed', 'pending'])
            ->orderBy('check_in')
            ->take(5)
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'guest_name' => $b->guest ? $b->guest->fullName() : '—',
                'property_name' => $b->property?->name ?? '—',
                'check_in' => $b->check_in?->format('d.m.Y'),
                'check_out' => $b->check_out?->format('d.m.Y'),
                'nights' => $b->nights,
                'status' => $b->status,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalBookings' => Booking::count(),
                'monthlyBookings' => Booking::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
                'totalRevenue' => Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])->sum('total_price'),
                'monthlyRevenue' => Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('total_price'),
                'upcomingCheckins' => Booking::where('check_in', '>=', $now->toDateString())->where('check_in', '<=', $now->copy()->addDays(7)->toDateString())->whereIn('status', ['confirmed'])->count(),
                'occupancy' => 0,
            ],
            'recentBookings' => $recentBookings,
            'upcomingBookings' => $upcomingBookings,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);

        // Get monthly revenue and booking counts
        $monthlyData = Booking::whereRaw("strftime('%Y', check_in) = ?", [(string) $year])
            ->whereIn('status', ['confirmed', 'completed', 'checked_in', 'checked_out'])
            ->select(
                DB::raw("CAST(strftime('%m', check_in) AS INTEGER) as month"),
                DB::raw('COUNT(*) as booking_count'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Build full year data (all 12 months)
        $monthlyRevenue = [];
        $monthlyBookings = [];

        for ($month = 1; $month <= 12; $month++) {
            $data = $monthlyData->get($month);
            $monthlyRevenue[$month] = $data ? (int) $data->revenue : 0;
            $monthlyBookings[$month] = $data ? (int) $data->booking_count : 0;
        }

        // Calculate occupancy rate per month
        $propertiesCount = Property::where('is_published', true)->count();
        $monthlyOccupancy = [];

        for ($month = 1; $month <= 12; $month++) {
            if ($propertiesCount > 0) {
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $totalAvailableNights = $propertiesCount * $daysInMonth;

                // Count booked nights for this month
                $monthPadded = str_pad($month, 2, '0', STR_PAD_LEFT);
                $monthStart = "$year-$monthPadded-01";
                $monthEnd = date('Y-m-t', strtotime($monthStart));

                $bookedNights = Booking::whereIn('status', ['confirmed', 'completed', 'checked_in', 'checked_out'])
                    ->where('check_out', '>', $monthStart)
                    ->where('check_in', '<=', $monthEnd)
                    ->sum('nights');

                $monthlyOccupancy[$month] = $totalAvailableNights > 0
                    ? round(($bookedNights / $totalAvailableNights) * 100, 1)
                    : 0;
            } else {
                $monthlyOccupancy[$month] = 0;
            }
        }

        // Calculate totals
        $totalRevenue = array_sum($monthlyRevenue);
        $totalBookings = array_sum($monthlyBookings);
        $avgBookingValue = $totalBookings > 0 ? round($totalRevenue / $totalBookings) : 0;
        $avgOccupancy = count($monthlyOccupancy) > 0 ? round(array_sum($monthlyOccupancy) / 12, 1) : 0;

        // Top property by revenue
        $topProperty = null;
        if (Property::count() > 1) {
            $topPropertyData = Booking::whereRaw("strftime('%Y', check_in) = ?", [(string) $year])
                ->whereIn('status', ['confirmed', 'completed', 'checked_in', 'checked_out'])
                ->select('property_id', DB::raw('SUM(total_price) as total_revenue'))
                ->groupBy('property_id')
                ->orderByDesc('total_revenue')
                ->first();

            if ($topPropertyData) {
                $prop = Property::find($topPropertyData->property_id);
                $topProperty = $prop ? [
                    'name' => $prop->name,
                    'revenue' => (int) $topPropertyData->total_revenue,
                ] : null;
            }
        }

        // Available years (from first booking to current year)
        $firstBooking = Booking::orderBy('check_in')->first();
        $startYear = $firstBooking ? $firstBooking->check_in->year : now()->year;
        $availableYears = range($startYear, now()->year);

        return Inertia::render('Reports/Index', [
            'year' => $year,
            'availableYears' => $availableYears,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyBookings' => $monthlyBookings,
            'monthlyOccupancy' => $monthlyOccupancy,
            'stats' => [
                'totalRevenue' => $totalRevenue,
                'totalBookings' => $totalBookings,
                'avgBookingValue' => $avgBookingValue,
                'avgOccupancy' => $avgOccupancy,
                'topProperty' => $topProperty,
            ],
        ]);
    }
}

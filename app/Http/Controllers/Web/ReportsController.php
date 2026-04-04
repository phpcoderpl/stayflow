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
        $propertyId = $request->input('property_id', null);

        // Get all published properties
        $properties = Property::where('is_published', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Base query for bookings
        $baseQuery = Booking::whereRaw("strftime('%Y', check_in) = ?", [(string) $year])
            ->whereIn('status', ['confirmed', 'completed', 'checked_in', 'checked_out']);

        if ($propertyId) {
            $baseQuery->where('property_id', $propertyId);
        }

        // Get monthly revenue and booking counts
        $monthlyData = (clone $baseQuery)
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
        $propertiesCount = $propertyId
            ? 1
            : Property::where('is_published', true)->count();
        $monthlyOccupancy = [];

        for ($month = 1; $month <= 12; $month++) {
            if ($propertiesCount > 0) {
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $totalAvailableNights = $propertiesCount * $daysInMonth;

                // Count booked nights for this month
                $monthPadded = str_pad($month, 2, '0', STR_PAD_LEFT);
                $monthStart = "$year-$monthPadded-01";
                $monthEnd = date('Y-m-t', strtotime($monthStart));

                $bookedNightsQuery = Booking::whereIn('status', ['confirmed', 'completed', 'checked_in', 'checked_out'])
                    ->where('check_out', '>', $monthStart)
                    ->where('check_in', '<=', $monthEnd);

                if ($propertyId) {
                    $bookedNightsQuery->where('property_id', $propertyId);
                }

                $bookedNights = $bookedNightsQuery->sum('nights');

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
        if (!$propertyId && Property::count() > 1) {
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

        // Calculate per-property data (only when viewing all properties)
        $perPropertyData = [];
        if (!$propertyId) {
            foreach ($properties as $property) {
                $propertyBookings = Booking::whereRaw("strftime('%Y', check_in) = ?", [(string) $year])
                    ->whereIn('status', ['confirmed', 'completed', 'checked_in', 'checked_out'])
                    ->where('property_id', $property->id)
                    ->selectRaw('COUNT(*) as booking_count, SUM(total_price) as revenue')
                    ->first();

                // Calculate occupancy for this property
                $propertyOccupancy = 0;
                $totalDaysInYear = date('L', strtotime("$year-01-01")) ? 366 : 365;
                $totalAvailableNights = $totalDaysInYear;

                $yearStart = "$year-01-01";
                $yearEnd = "$year-12-31";

                $bookedNights = Booking::whereIn('status', ['confirmed', 'completed', 'checked_in', 'checked_out'])
                    ->where('property_id', $property->id)
                    ->where('check_out', '>', $yearStart)
                    ->where('check_in', '<=', $yearEnd)
                    ->sum('nights');

                $propertyOccupancy = $totalAvailableNights > 0
                    ? round(($bookedNights / $totalAvailableNights) * 100, 1)
                    : 0;

                $perPropertyData[$property->id] = [
                    'name' => $property->name,
                    'bookings' => $propertyBookings ? (int) $propertyBookings->booking_count : 0,
                    'revenue' => $propertyBookings ? (int) $propertyBookings->revenue : 0,
                    'occupancy' => $propertyOccupancy,
                ];
            }
        }

        // Available years (from first booking to current year)
        $firstBooking = Booking::orderBy('check_in')->first();
        $startYear = $firstBooking ? $firstBooking->check_in->year : now()->year;
        $availableYears = range($startYear, now()->year);

        return Inertia::render('Reports/Index', [
            'year' => $year,
            'availableYears' => $availableYears,
            'properties' => $properties,
            'selectedPropertyId' => $propertyId,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyBookings' => $monthlyBookings,
            'monthlyOccupancy' => $monthlyOccupancy,
            'perPropertyData' => $perPropertyData,
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

<?php

namespace App\Services;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Property;

class AvailabilityService
{
    public static function isAvailable(Property $property, string $checkIn, string $checkOut): bool
    {
        // Check bookings overlap
        $hasBooking = Booking::where('property_id', $property->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where(function ($q2) use ($checkIn, $checkOut) {
                    $q2->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                });
            })
            ->exists();

        if ($hasBooking) return false;

        // Check blocked dates overlap
        $isBlocked = BlockedDate::where('property_id', $property->id)
            ->where('date_from', '<', $checkOut)
            ->where('date_to', '>', $checkIn)
            ->exists();

        return !$isBlocked;
    }

    public static function getUnavailableDates(Property $property, string $monthStart, string $monthEnd): array
    {
        $unavailable = [];

        // Booked dates
        $bookings = Booking::where('property_id', $property->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where('check_in', '<=', $monthEnd)
            ->where('check_out', '>=', $monthStart)
            ->get(['check_in', 'check_out', 'status']);

        foreach ($bookings as $b) {
            $start = max(strtotime($monthStart), strtotime($b->check_in));
            $end = min(strtotime($monthEnd), strtotime($b->check_out));
            for ($d = $start; $d < $end; $d += 86400) {
                $unavailable[date('Y-m-d', $d)] = 'booked';
            }
        }

        // Blocked dates
        $blocked = BlockedDate::where('property_id', $property->id)
            ->where('date_from', '<=', $monthEnd)
            ->where('date_to', '>=', $monthStart)
            ->get(['date_from', 'date_to']);

        foreach ($blocked as $b) {
            $start = max(strtotime($monthStart), strtotime($b->date_from->toDateString()));
            $end = min(strtotime($monthEnd), strtotime($b->date_to->toDateString()));
            for ($d = $start; $d <= $end; $d += 86400) {
                $unavailable[date('Y-m-d', $d)] = 'blocked';
            }
        }

        return $unavailable;
    }
}

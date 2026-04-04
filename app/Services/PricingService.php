<?php

namespace App\Services;

use App\Models\Property;
use App\Models\SeasonalPrice;
use Carbon\Carbon;

class PricingService
{
    public static function calculate(Property $property, string $checkIn, string $checkOut): array
    {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);
        $nights = $start->diffInDays($end);

        $totalBase = 0;
        $nightlyBreakdown = [];

        for ($i = 0; $i < $nights; $i++) {
            $date = $start->copy()->addDays($i);
            $price = self::getPriceForDate($property, $date);
            $totalBase += $price;
            $nightlyBreakdown[] = ['date' => $date->toDateString(), 'price' => $price];
        }

        return [
            'nights' => $nights,
            'base_total' => $totalBase,
            'cleaning_fee' => $property->cleaning_fee,
            'total_price' => $totalBase + $property->cleaning_fee,
            'nightly_breakdown' => $nightlyBreakdown,
        ];
    }

    public static function getPriceForDate(Property $property, Carbon $date): int
    {
        $seasonal = SeasonalPrice::where('property_id', $property->id)
            ->where('date_from', '<=', $date)
            ->where('date_to', '>=', $date)
            ->orderByDesc('priority')
            ->first();

        return $seasonal ? $seasonal->price_per_night : $property->base_price_per_night;
    }

    public static function getMinNightsForDate(Property $property, Carbon $date): int
    {
        $seasonal = SeasonalPrice::where('property_id', $property->id)
            ->where('date_from', '<=', $date)
            ->where('date_to', '>=', $date)
            ->whereNotNull('min_nights')
            ->orderByDesc('priority')
            ->first();

        return $seasonal ? $seasonal->min_nights : $property->min_nights;
    }
}

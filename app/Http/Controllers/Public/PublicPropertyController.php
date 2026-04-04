<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Setting;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Carbon\Carbon;
use Inertia\Inertia;

class PublicPropertyController extends Controller
{
    public function index()
    {
        $properties = Property::where('is_published', true)
            ->with(['photos' => fn($q) => $q->where('is_cover', true), 'amenities'])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Public/PropertyList', [
            'properties' => $properties,
            'brandName' => Setting::get('brand_name', 'StayFlow'),
        ]);
    }

    public function show(string $slug)
    {
        $property = Property::where('slug', $slug)
            ->where('is_published', true)
            ->with(['photos' => fn($q) => $q->orderBy('sort_order'), 'amenities', 'roomTypes'])
            ->firstOrFail();

        // Get 2 months of unavailable dates
        $start = Carbon::now()->startOfMonth()->toDateString();
        $end = Carbon::now()->addMonths(2)->endOfMonth()->toDateString();
        $unavailableDates = AvailabilityService::getUnavailableDates($property, $start, $end);

        return Inertia::render('Public/PropertyDetail', [
            'property' => $property,
            'unavailableDates' => $unavailableDates,
            'brandName' => Setting::get('brand_name', 'StayFlow'),
        ]);
    }
}

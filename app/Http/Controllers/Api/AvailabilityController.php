<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request, int $propertyId)
    {
        $property = Property::where('is_published', true)->findOrFail($propertyId);

        $start = $request->get('start', now()->startOfMonth()->toDateString());
        $end = $request->get('end', now()->addMonths(2)->endOfMonth()->toDateString());

        return response()->json([
            'property_id' => $property->id,
            'unavailable_dates' => AvailabilityService::getUnavailableDates($property, $start, $end),
        ]);
    }
}

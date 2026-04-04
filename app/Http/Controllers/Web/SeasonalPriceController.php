<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\SeasonalPrice;
use Illuminate\Http\Request;

class SeasonalPriceController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
            'price_per_night' => 'required|integer|min:0',
            'min_nights' => 'nullable|integer|min:1',
            'priority' => 'nullable|integer',
        ]);

        $property->seasonalPrices()->create($validated);

        return redirect()->back()->with('success', 'Seasonal price added successfully.');
    }

    public function destroy(Property $property, SeasonalPrice $seasonalPrice)
    {
        $seasonalPrice->delete();

        return redirect()->back()->with('success', 'Seasonal price removed.');
    }
}

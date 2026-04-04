<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Setting;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PublicPropertyController extends Controller
{
    public function index()
    {
        $properties = Property::where('is_published', true)
            ->with(['photos' => fn($q) => $q->orderBy('sort_order'), 'amenities'])
            ->orderBy('sort_order')
            ->get();

        $brandName = Setting::get('brand_name', 'StayFlow');

        return Inertia::render('Public/PropertyList', [
            'properties' => $properties,
            'brandName' => $brandName,
            'listLayout' => Setting::get('property_list_layout', 'auto'),
            'meta' => [
                'title' => $brandName . ' — ' . ($properties->count() === 1 ? $properties->first()->name : 'Nasze obiekty'),
                'description' => 'Zarezerwuj pobyt bezposrednio w ' . $brandName . '. Bez prowizji, bez posrednikow.',
            ],
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => $brandName,
                'itemListElement' => $properties->map(fn($p, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'item' => [
                        '@type' => 'LodgingBusiness',
                        'name' => $p->name,
                        'address' => [
                            '@type' => 'PostalAddress',
                            'addressLocality' => $p->city,
                            'addressCountry' => 'PL',
                        ],
                    ],
                ])->values()->toArray(),
            ],
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

        $brandName = Setting::get('brand_name', 'StayFlow');
        $coverPhoto = $property->photos->first();

        return Inertia::render('Public/PropertyDetail', [
            'property' => $property,
            'unavailableDates' => $unavailableDates,
            'brandName' => $brandName,
            'meta' => [
                'title' => $property->name . ' — ' . $brandName,
                'description' => Str::limit(strip_tags($property->description_pl), 160),
                'image' => $coverPhoto?->path ? url('storage/' . $coverPhoto->path) : null,
            ],
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'LodgingBusiness',
                'name' => $property->name,
                'description' => $property->description_pl,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $property->address,
                    'addressLocality' => $property->city,
                    'addressCountry' => 'PL',
                ],
                'priceRange' => number_format($property->base_price_per_night / 100, 2) . ' PLN',
                'image' => $coverPhoto?->path ? url('storage/' . $coverPhoto->path) : null,
            ],
        ]);
    }
}

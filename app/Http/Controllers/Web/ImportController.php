<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Photo;
use App\Models\Property;
use App\Services\BookingComScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ImportController extends Controller
{
    /**
     * Show the import page
     */
    public function index()
    {
        $amenities = Amenity::orderBy('category')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Import/Index', [
            'amenities' => $amenities,
        ]);
    }

    /**
     * Scrape data from a Booking.com URL
     */
    public function scrape(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $scrapedData = BookingComScraperService::scrape($request->url);

        return response()->json([
            'success' => true,
            'data' => $scrapedData,
        ]);
    }

    /**
     * Parse pasted HTML from Booking.com
     */
    public function parseHtml(Request $request)
    {
        $request->validate([
            'html' => 'required|string|min:100',
        ]);

        $parsedData = BookingComScraperService::parseFromHtml($request->html);

        return response()->json([
            'success' => true,
            'data' => $parsedData,
        ]);
    }

    /**
     * Receive data from bookmarklet
     */
    public function bookmarklet(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photos' => 'nullable|array',
            'max_guests' => 'nullable|integer',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'area_sqm' => 'nullable|integer',
            'price' => 'nullable|integer',
            'amenities' => 'nullable|array',
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Import the property from scraped/edited data
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description_pl' => 'nullable|string',
            'description_en' => 'nullable|string',
            'max_guests' => 'nullable|integer|min:1',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqm' => 'nullable|integer|min:0',
            'base_price_per_night' => 'nullable|integer|min:0',
            'cleaning_fee' => 'nullable|integer|min:0',
            'check_in_time' => 'nullable|string|max:5',
            'check_out_time' => 'nullable|string|max:5',
            'min_nights' => 'nullable|integer|min:1',
            'photo_urls' => 'nullable|array',
            'photo_urls.*' => 'url',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,id',
        ]);

        // Generate slug
        $slug = Str::slug($validated['name']);

        // Check if slug exists and make it unique
        $originalSlug = $slug;
        $counter = 1;
        while (Property::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Extract amenity IDs and photo URLs
        $amenityIds = $validated['amenity_ids'] ?? [];
        $photoUrls = $validated['photo_urls'] ?? [];
        unset($validated['amenity_ids'], $validated['photo_urls']);

        // Set defaults for required fields
        $validated['type'] = $validated['type'] ?? 'apartment';
        $validated['address'] = $validated['address'] ?: '';
        $validated['city'] = $validated['city'] ?: '';
        $validated['postal_code'] = $validated['postal_code'] ?: '';
        $validated['country'] = $validated['country'] ?: 'PL';
        $validated['base_price_per_night'] = $validated['base_price_per_night'] ?: 35000;
        $validated['currency'] = 'PLN';
        $validated['is_published'] = false;
        $validated['reservations_enabled'] = false;
        $validated['check_in_time'] = $validated['check_in_time'] ?? '15:00';
        $validated['check_out_time'] = $validated['check_out_time'] ?? '11:00';
        $validated['min_nights'] = $validated['min_nights'] ?? 1;

        // Create the property
        $property = Property::create(array_merge($validated, ['slug' => $slug]));

        // Attach amenities
        if (!empty($amenityIds)) {
            $property->amenities()->sync($amenityIds);
        }

        // Download and save photos
        if (!empty($photoUrls)) {
            $this->downloadPhotos($property, $photoUrls);
        }

        return redirect('/admin/properties/' . $property->id . '/edit')
            ->with('success', 'Property imported successfully!');
    }

    /**
     * Download photos from URLs and save to storage
     */
    private function downloadPhotos(Property $property, array $photoUrls): void
    {
        $sortOrder = 0;
        $isFirst = true;

        foreach ($photoUrls as $url) {
            try {
                // Download the photo
                $response = Http::timeout(30)->get($url);

                if (!$response->successful()) {
                    continue;
                }

                // Generate filename
                $extension = 'jpg'; // Default to jpg
                if (preg_match('/\.(jpe?g|png|webp)$/i', $url, $matches)) {
                    $extension = strtolower($matches[1]);
                    if ($extension === 'jpeg') {
                        $extension = 'jpg';
                    }
                }

                $filename = Str::random(40) . '.' . $extension;
                $path = "properties/{$property->id}/{$filename}";

                // Save to storage
                Storage::disk('public')->put($path, $response->body());

                // Create photo record
                Photo::create([
                    'property_id' => $property->id,
                    'filename' => $filename,
                    'path' => $path,
                    'is_cover' => $isFirst,
                    'sort_order' => $sortOrder,
                ]);

                $sortOrder++;
                $isFirst = false;

                // Limit to 20 photos
                if ($sortOrder >= 30) {
                    break;
                }
            } catch (\Exception $e) {
                // Skip failed downloads
                \Log::warning("Failed to download photo from {$url}: " . $e->getMessage());
                continue;
            }
        }
    }
}

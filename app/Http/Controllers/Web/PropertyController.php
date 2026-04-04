<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Photo;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::withCount(['bookings', 'photos'])
            ->with(['amenities', 'photos' => fn ($q) => $q->where('is_cover', true)->limit(1)])
            ->orderBy('sort_order')
            ->get()
            ->each(function ($p) {
                $cover = $p->photos->first();
                $p->cover_photo_url = $cover ? '/storage/' . $cover->path : null;
                unset($p->photos);
            });

        return Inertia::render('Properties/Index', [
            'properties' => $properties,
        ]);
    }

    public function create()
    {
        $amenities = Amenity::orderBy('category')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Properties/Create', [
            'amenities' => $amenities,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description_pl' => 'nullable|string',
            'description_en' => 'nullable|string',
            'max_guests' => 'required|integer|min:1',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqm' => 'nullable|integer|min:0',
            'base_price_per_night' => 'required|integer|min:0',
            'currency' => 'nullable|string|max:3',
            'cleaning_fee' => 'nullable|integer|min:0',
            'check_in_time' => 'nullable|string|max:5',
            'check_out_time' => 'nullable|string|max:5',
            'min_nights' => 'nullable|integer|min:1',
            'reservations_enabled' => 'nullable|boolean',
            'video_url' => 'nullable|string|max:500',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,id',
        ]);

        $slug = Str::slug($validated['name']) . '-' . Str::random(4);

        $amenityIds = $validated['amenity_ids'] ?? [];
        unset($validated['amenity_ids']);

        $property = Property::create(array_merge($validated, ['slug' => $slug]));
        $property->amenities()->sync($amenityIds);

        return redirect('/admin/properties');
    }

    public function edit(Property $property)
    {
        $property->load([
            'photos' => fn ($q) => $q->orderBy('sort_order'),
            'amenities',
            'seasonalPrices',
            'roomTypes',
        ]);

        $amenities = Amenity::orderBy('category')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Properties/Edit', [
            'property' => $property,
            'amenities' => $amenities,
        ]);
    }

    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description_pl' => 'nullable|string',
            'description_en' => 'nullable|string',
            'max_guests' => 'required|integer|min:1',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqm' => 'nullable|integer|min:0',
            'base_price_per_night' => 'required|integer|min:0',
            'currency' => 'nullable|string|max:3',
            'cleaning_fee' => 'nullable|integer|min:0',
            'check_in_time' => 'nullable|string|max:5',
            'check_out_time' => 'nullable|string|max:5',
            'min_nights' => 'nullable|integer|min:1',
            'reservations_enabled' => 'nullable|boolean',
            'video_url' => 'nullable|string|max:500',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,id',
        ]);

        $amenityIds = $validated['amenity_ids'] ?? [];
        unset($validated['amenity_ids']);

        // Ensure NOT NULL fields have defaults
        $validated['postal_code'] = $validated['postal_code'] ?? '';
        $validated['cleaning_fee'] = $validated['cleaning_fee'] ?? 0;
        $validated['check_in_time'] = $validated['check_in_time'] ?? '15:00';
        $validated['check_out_time'] = $validated['check_out_time'] ?? '11:00';
        $validated['min_nights'] = $validated['min_nights'] ?? 1;

        $property->update($validated);
        $property->amenities()->sync($amenityIds);

        return redirect()->back();
    }

    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()->back();
    }

    public function uploadPhotos(Request $request, Property $property)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|max:5120',
        ]);

        foreach ($request->file('photos') as $file) {
            $path = $file->store("properties/{$property->id}", 'public');

            $property->photos()->create([
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'sort_order' => $property->photos()->count(),
            ]);
        }

        return redirect()->back();
    }

    public function deletePhoto(Property $property, Photo $photo)
    {
        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return redirect()->back();
    }

    public function setCoverPhoto(Property $property, Photo $photo)
    {
        $property->photos()->update(['is_cover' => false]);
        $photo->update(['is_cover' => true]);

        return redirect()->back();
    }

    public function reorderPhotos(Request $request, Property $property)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:photos,id',
        ]);

        foreach ($request->input('order') as $index => $photoId) {
            $property->photos()->where('id', $photoId)->update(['sort_order' => $index]);
        }

        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate(['order' => 'required|array', 'order.*' => 'integer']);
        foreach ($validated['order'] as $index => $id) {
            Property::where('id', $id)->update(['sort_order' => $index]);
        }
        return response()->json(['ok' => true]);
    }
}

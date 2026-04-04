<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AmenityController extends Controller
{
    public function index()
    {
        $amenities = Amenity::orderBy('category')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Amenities/Index', [
            'amenities' => $amenities,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_pl' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category' => 'required|string|max:50',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        $slug = Str::slug($validated['name_pl']);
        $originalSlug = $slug;
        $counter = 1;
        while (Amenity::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('amenities', 'public');
        }

        Amenity::create([
            'slug' => $slug,
            'name_pl' => $validated['name_pl'],
            'name_en' => $validated['name_en'] ?? '',
            'category' => $validated['category'],
            'icon' => $validated['icon'] ?? null,
            'image_path' => $imagePath,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'name_pl' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category' => 'required|string|max:50',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            if ($amenity->image_path) {
                Storage::disk('public')->delete($amenity->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('amenities', 'public');
        }

        unset($validated['image']);

        $amenity->update($validated);

        return redirect()->back();
    }

    public function destroyCategory(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:50',
        ]);

        $category = $validated['category'];

        if ($category === 'general') {
            return redirect()->back();
        }

        Amenity::where('category', $category)->update(['category' => 'general']);

        return redirect()->back();
    }

    public function destroy(Amenity $amenity)
    {
        if ($amenity->image_path) {
            Storage::disk('public')->delete($amenity->image_path);
        }
        $amenity->properties()->detach();
        $amenity->delete();

        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate(['order' => 'required|array', 'order.*' => 'integer']);
        foreach ($validated['order'] as $index => $id) {
            Amenity::where('id', $id)->update(['sort_order' => $index]);
        }
        return response()->json(['ok' => true]);
    }
}

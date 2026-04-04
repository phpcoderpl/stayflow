<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    public function index()
    {
        $widgets = Widget::with('property')->get();

        return response()->json($widgets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'type' => ['required', 'in:calendar,booking_form,showcase'],
            'name' => ['required', 'string', 'max:255'],
            'config' => ['nullable', 'array'],
        ]);

        $config = $validated['config'] ?? [
            'width' => '100%',
            'height' => $validated['type'] === 'calendar' ? '600' : ($validated['type'] === 'showcase' ? '400' : '500'),
        ];

        $widget = Widget::create([
            'property_id' => $validated['property_id'],
            'type' => $validated['type'],
            'name' => $validated['name'],
            'config' => $config,
            'is_active' => true,
        ]);

        // Generate embed code
        $width = $config['width'];
        $height = $config['height'];
        $url = url("/widget/{$widget->id}");

        $embedCode = "<iframe src=\"{$url}\" width=\"{$width}\" height=\"{$height}\" frameborder=\"0\" style=\"border:0; display:block;\"></iframe>";

        $widget->update(['embed_code' => $embedCode]);

        return back()->with('success', 'Widget utworzony.');
    }

    public function destroy(Widget $widget)
    {
        $widget->delete();

        return back()->with('success', 'Widget usuniety.');
    }
}

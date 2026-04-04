<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Widget;

class WidgetController extends Controller
{
    public function render(Widget $widget)
    {
        if (!$widget->is_active) {
            abort(404);
        }

        $widget->load('property');

        switch ($widget->type) {
            case 'calendar':
                return view('widgets.calendar', compact('widget'));
            case 'booking_form':
                return view('widgets.booking-form', compact('widget'));
            case 'showcase':
                return view('widgets.showcase', compact('widget'));
            default:
                abort(404);
        }
    }
}

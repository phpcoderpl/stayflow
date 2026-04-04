<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CalendarSync;
use App\Models\EmailTemplate;
use App\Models\Property;
use App\Models\Setting;
use App\Services\ICalService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $properties = Property::orderBy('name')->get(['id', 'name']);
        $calendarSyncs = CalendarSync::with('property')->get();
        $emailTemplates = EmailTemplate::orderBy('name')->get();

        return Inertia::render('Settings/Index', [
            'settings' => [
                'brand_name' => Setting::get('brand_name', 'StayFlow'),
                'brand_primary_color' => Setting::get('brand_primary_color', '#0ea5e9'),
                'deposit_percent' => Setting::get('deposit_percent', '30'),
                'google_analytics_id' => Setting::get('google_analytics_id', ''),
                'admin_email' => Setting::get('admin_email', ''),
                'pre_arrival_days' => Setting::get('pre_arrival_days', '3'),
                'post_stay_days' => Setting::get('post_stay_days', '1'),
            ],
            'properties' => $properties,
            'calendarSyncs' => $calendarSyncs,
            'emailTemplates' => $emailTemplates,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => ['nullable', 'string', 'max:255'],
            'brand_primary_color' => ['nullable', 'string', 'max:7'],
            'deposit_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'google_analytics_id' => ['nullable', 'string', 'max:50'],
            'admin_email' => ['nullable', 'email'],
            'pre_arrival_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'post_stay_days' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                Setting::set($key, (string) $value);
            }
        }

        return back()->with('success', 'Ustawienia zapisane.');
    }

    public function createCalendarSync(Request $request)
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'provider' => ['required', 'in:ical,booking_com,google'],
            'ical_url' => ['nullable', 'url'],
        ]);

        $sync = CalendarSync::create([
            'property_id' => $validated['property_id'],
            'provider' => $validated['provider'],
            'direction' => $validated['ical_url'] ? 'import' : 'export',
            'ical_url' => $validated['ical_url'],
            'ical_export_token' => Str::uuid(),
            'is_active' => true,
        ]);

        // If import URL provided, sync immediately
        if ($validated['ical_url']) {
            $property = Property::findOrFail($validated['property_id']);
            $imported = ICalService::importFromUrl($property, $validated['ical_url']);
            $sync->update(['last_synced_at' => now()]);
            return back()->with('success', "Zsynchronizowano {$imported} wydarzen.");
        }

        return back()->with('success', 'Sync kalendarzowy utworzony.');
    }

    public function deleteCalendarSync(CalendarSync $calendarSync)
    {
        $calendarSync->delete();
        return back()->with('success', 'Sync usuniety.');
    }

    public function updateEmailTemplate(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'subject_pl' => ['required', 'string'],
            'subject_en' => ['nullable', 'string'],
            'body_pl' => ['required', 'string'],
            'body_en' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $emailTemplate->update($validated);
        return back()->with('success', 'Szablon zaktualizowany.');
    }
}

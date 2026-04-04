<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarSync;
use App\Services\ICalService;

class ICalController extends Controller
{
    public function export(string $token)
    {
        $sync = CalendarSync::where('ical_export_token', $token)->firstOrFail();
        $content = ICalService::generateFeed($sync->property);

        return response($content, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="calendar.ics"',
        ]);
    }

    public function guestIcs(string $confirmationCode)
    {
        $booking = \App\Models\Booking::where('confirmation_code', $confirmationCode)->firstOrFail();
        $content = ICalService::generateGuestIcs($booking);

        return response($content, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="booking.ics"',
        ]);
    }
}

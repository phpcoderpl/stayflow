<?php

namespace App\Services;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Property;

class ICalService
{
    public static function generateFeed(Property $property): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//StayFlow//Booking Calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . $property->name,
        ];

        // Add bookings
        $bookings = Booking::where('property_id', $property->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get();

        foreach ($bookings as $booking) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'DTSTART;VALUE=DATE:' . $booking->check_in->format('Ymd');
            $lines[] = 'DTEND;VALUE=DATE:' . $booking->check_out->format('Ymd');
            $lines[] = 'SUMMARY:Rezerwacja ' . $booking->confirmation_code;
            $lines[] = 'DESCRIPTION:Gosc: ' . ($booking->guest?->fullName() ?? 'N/A');
            $lines[] = 'UID:booking-' . $booking->id . '@stayflow';
            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'END:VEVENT';
        }

        // Add blocked dates
        $blocked = BlockedDate::where('property_id', $property->id)->get();
        foreach ($blocked as $block) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'DTSTART;VALUE=DATE:' . $block->date_from->format('Ymd');
            $lines[] = 'DTEND;VALUE=DATE:' . $block->date_to->format('Ymd');
            $lines[] = 'SUMMARY:Zablokowane' . ($block->reason ? ' - ' . $block->reason : '');
            $lines[] = 'UID:blocked-' . $block->id . '@stayflow';
            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines);
    }

    public static function importFromUrl(Property $property, string $icalUrl): int
    {
        $response = \Illuminate\Support\Facades\Http::get($icalUrl);
        if (!$response->successful()) return 0;

        $content = $response->body();
        $imported = 0;

        // Simple iCal parser
        preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $content, $matches);

        foreach ($matches[1] as $event) {
            $dtstart = null;
            $dtend = null;
            $summary = 'Booking.com';
            $uid = null;

            if (preg_match('/DTSTART[^:]*:(\d{8})/', $event, $m)) $dtstart = $m[1];
            if (preg_match('/DTEND[^:]*:(\d{8})/', $event, $m)) $dtend = $m[1];
            if (preg_match('/SUMMARY[^:]*:(.+)/m', $event, $m)) $summary = trim($m[1]);
            if (preg_match('/UID[^:]*:(.+)/m', $event, $m)) $uid = trim($m[1]);

            if (!$dtstart || !$dtend || !$uid) continue;

            $dateFrom = \Carbon\Carbon::createFromFormat('Ymd', $dtstart)->toDateString();
            $dateTo = \Carbon\Carbon::createFromFormat('Ymd', $dtend)->toDateString();

            BlockedDate::updateOrCreate(
                ['property_id' => $property->id, 'external_id' => $uid],
                [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'reason' => $summary,
                    'source' => 'ical_sync',
                ]
            );
            $imported++;
        }

        return $imported;
    }

    public static function generateGuestIcs(Booking $booking): string
    {
        $booking->load(['property', 'guest']);
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//StayFlow//Booking//EN',
            'BEGIN:VEVENT',
            'DTSTART;VALUE=DATE:' . $booking->check_in->format('Ymd'),
            'DTEND;VALUE=DATE:' . $booking->check_out->format('Ymd'),
            'SUMMARY:' . $booking->property->name,
            'DESCRIPTION:Kod: ' . $booking->confirmation_code . '\\nZameldowanie: ' . $booking->property->check_in_time . '\\nWymeldowanie: ' . $booking->property->check_out_time,
            'LOCATION:' . $booking->property->address . ', ' . $booking->property->city,
            'UID:guest-booking-' . $booking->id . '@stayflow',
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ];
        return implode("\r\n", $lines);
    }
}

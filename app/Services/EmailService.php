<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public static function sendBookingEmail(string $templateSlug, Booking $booking): bool
    {
        $template = EmailTemplate::where('slug', $templateSlug)->where('is_active', true)->first();
        if (!$template) return false;

        $booking->load(['property', 'guest']);
        $guest = $booking->guest;
        $locale = $guest->user?->locale ?? 'pl';

        $subject = $locale === 'en' && $template->subject_en ? $template->subject_en : $template->subject_pl;
        $body = $locale === 'en' && $template->body_en ? $template->body_en : $template->body_pl;

        $vars = [
            '{{guest_name}}' => $guest->fullName(),
            '{{guest_email}}' => $guest->email,
            '{{property_name}}' => $booking->property->name,
            '{{check_in}}' => $booking->check_in->format('d.m.Y'),
            '{{check_out}}' => $booking->check_out->format('d.m.Y'),
            '{{check_in_time}}' => $booking->property->check_in_time,
            '{{check_out_time}}' => $booking->property->check_out_time,
            '{{confirmation_code}}' => $booking->confirmation_code,
            '{{total_price}}' => number_format($booking->total_price / 100, 2, ',', ' ') . ' PLN',
            '{{amount}}' => number_format($booking->deposit_amount / 100, 2, ',', ' ') . ' PLN',
            '{{booking_url}}' => url('/guest/' . $guest->access_token),
            '{{brand_name}}' => Setting::get('brand_name', 'StayFlow'),
        ];

        $subject = str_replace(array_keys($vars), array_values($vars), $subject);
        $body = str_replace(array_keys($vars), array_values($vars), $body);

        $log = EmailLog::create([
            'email_template_id' => $template->id,
            'booking_id' => $booking->id,
            'to_email' => $guest->email,
            'subject' => $subject,
            'body' => $body,
            'status' => 'queued',
        ]);

        try {
            Mail::raw($body, function ($message) use ($guest, $subject) {
                $message->to($guest->email, $guest->fullName())
                    ->subject($subject);
            });
            $log->update(['status' => 'sent', 'sent_at' => now()]);
            return true;
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return false;
        }
    }
}

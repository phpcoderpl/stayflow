<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Setting;
use App\Services\EmailService;
use Illuminate\Console\Command;

class SendPreArrivalReminders extends Command
{
    protected $signature = 'stayflow:send-reminders';

    protected $description = 'Send pre-arrival reminder emails to guests';

    public function handle(): int
    {
        $days = (int) Setting::get('pre_arrival_days', 3);
        $targetDate = now()->addDays($days)->toDateString();

        $bookings = Booking::where('status', 'confirmed')
            ->whereDate('check_in', $targetDate)
            ->get();

        $this->info("Sending pre-arrival reminders for {$bookings->count()} bookings (check-in on {$targetDate}).");

        foreach ($bookings as $booking) {
            $sent = EmailService::sendBookingEmail('pre_arrival', $booking);
            $status = $sent ? 'sent' : 'failed';
            $this->line("  [{$booking->confirmation_code}] {$status}");
        }

        $this->info('Pre-arrival reminders completed.');
        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Setting;
use App\Services\EmailService;
use Illuminate\Console\Command;

class SendPostStayEmails extends Command
{
    protected $signature = 'stayflow:post-stay';

    protected $description = 'Send post-stay follow-up emails to guests';

    public function handle(): int
    {
        $days = (int) Setting::get('post_stay_days', 1);
        $targetDate = now()->subDays($days)->toDateString();

        $bookings = Booking::where('status', 'checked_out')
            ->whereDate('check_out', $targetDate)
            ->get();

        $this->info("Sending post-stay emails for {$bookings->count()} bookings (checked out on {$targetDate}).");

        foreach ($bookings as $booking) {
            $sent = EmailService::sendBookingEmail('post_stay', $booking);
            $status = $sent ? 'sent' : 'failed';
            $this->line("  [{$booking->confirmation_code}] {$status}");
        }

        $this->info('Post-stay emails completed.');
        return self::SUCCESS;
    }
}

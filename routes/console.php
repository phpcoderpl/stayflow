<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('stayflow:sync-ical')->everyFifteenMinutes();
Schedule::command('stayflow:send-reminders')->dailyAt('09:00');
Schedule::command('stayflow:post-stay')->dailyAt('10:00');

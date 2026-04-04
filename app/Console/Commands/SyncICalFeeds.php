<?php

namespace App\Console\Commands;

use App\Models\CalendarSync;
use App\Services\ICalService;
use Illuminate\Console\Command;

class SyncICalFeeds extends Command
{
    protected $signature = 'stayflow:sync-ical';

    protected $description = 'Sync all active iCal feeds (import external calendars)';

    public function handle(): int
    {
        $syncs = CalendarSync::where('is_active', true)
            ->whereNotNull('ical_url')
            ->with('property')
            ->get();

        $this->info("Found {$syncs->count()} active iCal syncs to process.");

        foreach ($syncs as $sync) {
            try {
                $imported = ICalService::importFromUrl($sync->property, $sync->ical_url);
                $sync->update(['last_synced_at' => now()]);
                $this->line("  [{$sync->property->name}] Imported {$imported} events from {$sync->provider}.");
            } catch (\Exception $e) {
                $this->error("  [{$sync->property->name}] Error: {$e->getMessage()}");
            }
        }

        $this->info('iCal sync completed.');
        return self::SUCCESS;
    }
}

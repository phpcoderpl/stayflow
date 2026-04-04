<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarSync extends Model
{
    protected $fillable = [
        'property_id',
        'direction',
        'provider',
        'ical_url',
        'ical_export_token',
        'google_calendar_id',
        'google_tokens',
        'last_synced_at',
        'sync_errors',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'google_tokens' => 'array',
            'last_synced_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $fillable = [
        'property_id',
        'name_pl',
        'name_en',
        'max_guests',
        'bed_configuration',
        'base_price_per_night',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'bed_configuration' => 'array',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}

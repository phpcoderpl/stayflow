<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'type',
        'address',
        'city',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'description_pl',
        'description_en',
        'max_guests',
        'bedrooms',
        'bathrooms',
        'area_sqm',
        'base_price_per_night',
        'currency',
        'cleaning_fee',
        'check_in_time',
        'check_out_time',
        'min_nights',
        'reservations_enabled',
        'video_url',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'reservations_enabled' => 'boolean',
            'base_price_per_night' => 'integer',
            'cleaning_fee' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function seasonalPrices(): HasMany
    {
        return $this->hasMany(SeasonalPrice::class);
    }

    public function blockedDates(): HasMany
    {
        return $this->hasMany(BlockedDate::class);
    }

    public function calendarSyncs(): HasMany
    {
        return $this->hasMany(CalendarSync::class);
    }

    public function coverPhoto(): ?Photo
    {
        return $this->photos()->where('is_cover', true)->first();
    }

    public function formattedPrice(): string
    {
        return number_format($this->base_price_per_night / 100, 2, ',', ' ') . ' zl';
    }
}

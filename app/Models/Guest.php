<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Guest extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'country',
        'city',
        'postal_code',
        'address',
        'notes',
        'booking_count',
        'access_token',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Guest $guest) {
            $guest->access_token = Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function fullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}

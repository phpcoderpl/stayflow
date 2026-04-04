<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'property_id',
        'room_type_id',
        'guest_id',
        'confirmation_code',
        'check_in',
        'check_out',
        'nights',
        'guests_count',
        'status',
        'source',
        'base_total',
        'cleaning_fee',
        'discount_amount',
        'total_price',
        'currency',
        'deposit_amount',
        'deposit_paid',
        'special_requests',
        'cancelled_at',
        'cancellation_reason',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'cancelled_at' => 'datetime',
            'deposit_paid' => 'boolean',
            'base_total' => 'integer',
            'total_price' => 'integer',
            'deposit_amount' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Booking $booking) {
            $booking->confirmation_code = 'SF-' . strtoupper(Str::random(6));
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function formattedTotal(): string
    {
        return number_format($this->total_price / 100, 2, ',', ' ') . ' zl';
    }
}

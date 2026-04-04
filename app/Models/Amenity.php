<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    protected $fillable = [
        'slug',
        'name_pl',
        'name_en',
        'icon',
        'category',
        'sort_order',
    ];

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }
}

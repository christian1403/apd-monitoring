<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
    ];

    public function cameras(): HasMany
    {
        return $this->hasMany(Camera::class);
    }

    public function detections(): HasMany
    {
        return $this->hasMany(Detection::class);
    }
}
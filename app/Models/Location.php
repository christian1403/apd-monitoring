<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function cameras()
    {
        return $this->hasMany(Camera::class);
    }

    public function detections()
    {
        return $this->hasMany(Detection::class);
    }
}
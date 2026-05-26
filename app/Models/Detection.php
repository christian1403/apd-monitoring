<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detection extends Model
{
    protected $fillable = [
        'item_id',
        'camera_id',
        'location_id',
        'status',
        'image',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
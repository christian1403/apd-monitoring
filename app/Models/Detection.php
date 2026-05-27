<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detection extends Model
{
    protected $table = 'detections';
    protected $primaryKey = 'id';
    protected $fillable = [
        'item_id',
        'camera_id',
        'location_id',
        'status',
        'image',
        'detected_at',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
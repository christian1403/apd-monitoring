<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\FileService;
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

    protected $appends = ['image_url'];
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return app(FileService::class)->getUrl($this->image);
        }
        return null;
    }

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
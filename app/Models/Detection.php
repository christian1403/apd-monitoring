<?php

namespace App\Models;

use App\Services\FileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Detection extends Model
{
    protected $table = 'detections';

    protected $primaryKey = 'id';

    protected $fillable = [
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

    public function detectionItems(): HasMany
    {
        return $this->hasMany(DetectionItem::class);
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

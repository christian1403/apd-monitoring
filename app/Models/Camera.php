<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\FileService;
class Camera extends Model
{
    protected $table = 'cameras';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'ip_address',
        'status',
        'location_id',
        'image',
    ];

    protected $appends = ['image_url'];
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return app(FileService::class)->getUrl($this->image);
        }
        return null;
    }

    // cast is_active to boolean
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function detections(): HasMany
    {
        return $this->hasMany(Detection::class);
    }
}
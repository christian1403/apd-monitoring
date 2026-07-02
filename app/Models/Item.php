<?php

namespace App\Models;

use App\Services\FileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $table = 'items';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'code',
        'description',
        'image',
        'is_active',
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

    public function detectionItems(): HasMany
    {
        return $this->hasMany(DetectionItem::class);
    }
}

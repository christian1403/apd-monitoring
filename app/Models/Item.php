<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\FileService;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
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
    
    

    public function detections(): HasMany
    {
        return $this->hasMany(Detection::class);
    }
}

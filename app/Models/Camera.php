<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function detections(): HasMany
    {
        return $this->hasMany(Detection::class);
    }
}
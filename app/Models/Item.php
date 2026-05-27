<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    

    public function detections(): HasMany
    {
        return $this->hasMany(Detection::class);
    }
}

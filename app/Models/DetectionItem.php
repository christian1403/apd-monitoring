<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetectionItem extends Model
{
    protected $table = 'detection_items';

    protected $primaryKey = 'id';

    protected $fillable = [
        'item_id',
        'detection_id',
        'status',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function detection(): BelongsTo
    {
        return $this->belongsTo(Detection::class);
    }
}

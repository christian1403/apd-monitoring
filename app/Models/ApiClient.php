<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiClient extends Model
{
    protected $fillable = [
        'name',
        'api_key',
        'api_secret',
        'camera_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ApiClient $client) {
            if (empty($client->api_key)) {
                $client->api_key = Str::random(32);
            }
            if (empty($client->api_secret)) {
                $client->api_secret = Str::random(64);
            }
        });
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }
}

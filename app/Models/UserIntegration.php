<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserIntegration extends Model
{
    protected $fillable = [
        'user_id',
        'integration_id',
        'status',
        'credentials',
        'last_response',
        'expires_at',
        'connected_at',
        'last_refreshed_at',
    ];

    protected $casts = [
        'credentials'        => 'array',
        'last_response'      => 'array',
        'expires_at'         => 'datetime',
        'connected_at'       => 'datetime',
        'last_refreshed_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(UserIntegrationAuthLog::class);
    }

    public function info(): HasOne
    {
        return $this->hasOne(UserIntegrationInfo::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}

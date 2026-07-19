<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserIntegrationInfo extends Model
{
    protected $fillable = [
        'user_integration_id',
        'email',
        'raw_response',
        'fetched_at',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'fetched_at'   => 'datetime',
    ];

    public function userIntegration(): BelongsTo
    {
        return $this->belongsTo(UserIntegration::class);
    }
}

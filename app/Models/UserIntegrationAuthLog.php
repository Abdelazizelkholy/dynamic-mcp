<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserIntegrationAuthLog extends Model
{
    protected $fillable = [
        'user_integration_id',
        'integration_auth_step_id',
        'status',
        'request',
        'response',
        'error_message',
        'executed_at',
    ];

    protected $casts = [
        'request'     => 'array',
        'response'    => 'array',
        'executed_at' => 'datetime',
    ];

    public function userIntegration(): BelongsTo
    {
        return $this->belongsTo(UserIntegration::class);
    }

    public function authStep(): BelongsTo
    {
        return $this->belongsTo(IntegrationAuthStep::class, 'integration_auth_step_id');
    }
}

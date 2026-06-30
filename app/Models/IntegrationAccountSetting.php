<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationAccountSetting extends Model
{
    protected $fillable = [
        'integration_id',
        'base_url',
        'http_method',
        'email_key',
        'response_example',
    ];

    protected $casts = [
        'response_example' => 'array',
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }
}

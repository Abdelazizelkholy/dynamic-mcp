<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationAuthStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_id',
        'name',
        'step_type',
        'auth_type',
        'http_method',
        'base_endpoint_url',
        'inputs',
        'outputs',
        'response_example',
        'order',
        'is_active',
    ];

    protected $casts = [
        'inputs'           => 'array',
        'outputs'          => 'array',
        'response_example' => 'array',
        'is_active'        => 'boolean',
        'order'            => 'integer',
    ];


    public function integration(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }
}

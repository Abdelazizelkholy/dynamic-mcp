<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_id',
        'type',
        'header_key',
        'concatenate_key',
        'require_from',
        'value',
        'label',
        'description',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];


    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }


    /**
     * Build the actual header value for runtime use.
     * Called when executing an API call on behalf of a user integration.
     *
     * @param string|null $resolvedValue  The actual token/value resolved from user integration outputs
     */
    public function buildHeaderValue(?string $resolvedValue = null): string
    {
        $val = $resolvedValue ?? $this->value;

        return match ($this->type) {
            'bearer'     => "Bearer {$val}",
            'basic_auth' => 'Basic ' . base64_encode($val),
            default      => $val ?? '',
        };
    }
}

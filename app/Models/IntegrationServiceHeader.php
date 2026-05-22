<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationServiceHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_service_id',
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

    // ── Relations ──────────────────────────────────────────────────────────────

    public function service()
    {
        return $this->belongsTo(IntegrationService::class, 'integration_service_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'bearer'     => 'Bearer',
            'basic_auth' => 'Basic Auth',
            default      => 'Normal',
        };
    }

    public function getRequireFromLabelAttribute(): string
    {
        return match ($this->require_from) {
            'user'             => 'User',
            'user_integration' => 'User Integration',
            default            => 'Admin',
        };
    }

    /**
     * Build the actual header value at runtime.
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

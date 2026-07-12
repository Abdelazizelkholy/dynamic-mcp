<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationServiceParam extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_service_id',
        'input_id',
        'type',
        'value',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function service()
    {
        return $this->belongsTo(IntegrationService::class, 'integration_service_id');
    }

    public function input()
    {
        return $this->belongsTo(IntegrationServiceInput::class, 'input_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'user_integration' => 'User Integration',
            'params'            => 'From Input',
            default             => 'Static',
        };
    }

    /**
     * Returns the URL segment representation.
     * static           → "test"
     * user_integration → "[Authorization]"
     * params           → "{key}" of the linked input
     */
    public function toUrlSegment(): string
    {
        return match ($this->type) {
            'static' => $this->value,
            'params' => '{'.($this->input?->key ?? $this->value).'}',
            default  => "[{$this->value}]",
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationServiceParam extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_service_id',
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

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'user_integration' => 'User Integration',
            default            => 'Static',
        };
    }

    /**
     * Returns the URL segment representation.
     * static           → "test"
     * user_integration → "[Authorization]"
     */
    public function toUrlSegment(): string
    {
        return $this->type === 'static'
            ? $this->value
            : "[{$this->value}]";
    }
}

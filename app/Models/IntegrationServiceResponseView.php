<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationServiceResponseView extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_service_id',
        'key',
        'data_type',
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

    public function getDataTypeLabelAttribute(): string
    {
        return match ($this->data_type) {
            'file'  => 'File',
            default => 'Text',
        };
    }
}

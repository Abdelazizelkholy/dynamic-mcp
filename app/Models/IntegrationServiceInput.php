<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationServiceInput extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_service_id',
        'group_id',
        'field_type',
        'key',
        'placeholder',
        'type',
        'key_type',
        'validation',
        'require_from',
        'options',
        'dynamic_service_id',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'order'   => 'integer',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function service()
    {
        return $this->belongsTo(IntegrationService::class, 'integration_service_id');
    }

    public function group()
    {
        return $this->belongsTo(IntegrationServiceInputGroup::class, 'group_id');
    }

    // The service whose response populates this dynamic_select
    public function dynamicService()
    {
        return $this->belongsTo(IntegrationService::class, 'dynamic_service_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getFieldTypeLabelAttribute(): string
    {
        return match ($this->field_type) {
            'select'         => 'Select',
            'dynamic_select' => 'Dynamic Select',
            'boolean'        => 'Boolean',
            'group'          => 'Group',
            'file'           => 'File',
            'file_url'       => 'File URL',
            'files'          => 'Files',
            'files_url'      => 'Files URL',
            default          => 'Input',
        };
    }

    public function isGroupField(): bool
    {
        return $this->group_id !== null;
    }

    public function isStandalone(): bool
    {
        return $this->group_id === null;
    }
}

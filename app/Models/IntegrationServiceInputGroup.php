<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationServiceInputGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_service_id',
        'key_name',
        'data_type',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(IntegrationService::class, 'integration_service_id');
    }

    public function inputs()
    {
        return $this->hasMany(IntegrationServiceInput::class, 'group_id')->orderBy('order');
    }

    // Child groups inside this group
    public function childGroups()
    {
        return $this->hasMany(IntegrationServiceInputGroup::class, 'parent_group_id')->orderBy('order');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getDataTypeLabelAttribute(): string
    {
        return match ($this->data_type) {
            'array_of_objects' => 'Array of Objects',
            'array'            => 'Array',
            default            => 'Object',
        };
    }
}

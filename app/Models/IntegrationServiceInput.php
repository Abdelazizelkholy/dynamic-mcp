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
        'parent_group_id',
        'field_type',
        'key',
        'placeholder',
        'type',
        'key_type',
        'validation',
        'require_from',
        'options',
        'dynamic_service_id',
        'date_format',
        'order',
        'filling_data',
    ];

    protected $casts = [
        'options' => 'array',
        'order'   => 'integer',
        'filling_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(fn (self $input) => $input->syncParam());

        static::deleting(function (self $input) {
            IntegrationServiceParam::where('input_id', $input->id)->delete();
        });
    }

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

    public function param()
    {
        return $this->hasOne(IntegrationServiceParam::class, 'input_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Keeps integration_service_params in sync with this input's `type`.
     * type = params → an IntegrationServiceParam row linked via input_id must exist.
     * type != params → any previously auto-linked param row is removed.
     */
    public function syncParam(): void
    {
        if ($this->type !== 'params') {
            IntegrationServiceParam::where('input_id', $this->id)->where('type', 'params')->delete();

            return;
        }

        $param = IntegrationServiceParam::firstOrNew(['input_id' => $this->id]);
        $param->integration_service_id = $this->integration_service_id;
        $param->type = 'params';

        if (! $param->exists) {
            $param->order = IntegrationServiceParam::where('integration_service_id', $this->integration_service_id)->max('order') + 1;
        }

        $param->save();
    }

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

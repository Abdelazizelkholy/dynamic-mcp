<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class IntegrationService extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'integration_id',
        'service_name_en',
        'service_name_ar',
        'http_method',
        'content_type',
        'endpoint_path',
        'logo',
        'base_url_override',
        'description_en',
        'description_ar',
        'is_enabled',
        'inherit_global_headers',
        'long_term_execution',
        'response_example',
        'dependency_service_ids',
        'order',
    ];

    protected $casts = [
        'is_enabled'             => 'boolean',
        'inherit_global_headers' => 'boolean',
        'long_term_execution'    => 'boolean',
        'dependency_service_ids' => 'array',
        'order'                  => 'integer',
        'response_example'       => 'array',
    ];

    // ── Media ──────────────────────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('service_logo')->singleFile();
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('service_logo') ?: null;
    }

    // ── Relations ──────────────────────────────────────────────────────────────

    public function integration(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    /**
     * Services that this service depends on (resolved from dependency_service_ids JSON).
     */
    public function dependencyServices()
    {
        return $this->belongsToMany(
            IntegrationService::class,
            'integration_service_dependencies',
            'service_id',
            'dependency_id'
        );
    }


    // Inside App\Models\IntegrationService.php

    public function standaloneInputs(): \Illuminate\Database\Eloquent\Relations\HasMany|IntegrationService
    {
        return $this->hasMany(IntegrationServiceInput::class, 'integration_service_id')
            ->whereNull('group_id')
            ->orderBy('order');
    }

    public function inputGroups(): \Illuminate\Database\Eloquent\Relations\HasMany|IntegrationService
    {
        return $this->hasMany(IntegrationServiceInputGroup::class, 'integration_service_id')
            ->orderBy('order');
    }

}

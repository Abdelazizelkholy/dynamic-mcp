<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Integration extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'base_api_url',
        'documentation_url',
        'description_en',
        'description_ar',
        'publish',
        'enable'
    ];

    // Media collection
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('integration_media')->singleFile();
    }

    public function getMediaUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('integration_media') ?: null;
    }



    public function authSteps()
    {
        return $this->hasMany(IntegrationAuthStep::class)->orderBy('order');
    }

    public function headers()
    {
        return $this->hasMany(IntegrationHeader::class)->orderBy('order');
    }

}

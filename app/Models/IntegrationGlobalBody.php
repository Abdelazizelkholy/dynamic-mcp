<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationGlobalBody extends Model
{
    use HasFactory;

    protected $table = 'integration_global_body';

    protected $fillable = [
        'integration_id',
        'key',
        'require_from',
        'value',
        'label',
        'description',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getRequireFromLabelAttribute(): string
    {
        return match ($this->require_from) {
            'user_integration' => 'User Integration',
            default            => 'Admin',
        };
    }
}

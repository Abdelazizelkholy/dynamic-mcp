<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationServiceResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_service_id',
        'response_example',
        'output_filter_keys',
    ];

    protected $casts = [
        'response_example'   => 'array',
        'output_filter_keys' => 'array',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function service()
    {
        return $this->belongsTo(IntegrationService::class, 'integration_service_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Flatten response_example into dot-notation keys.
     * e.g. { "data": [{ "id": 1, "name": "x" }] }
     * → { "data.*.id": 1, "data.*.name": "x" }
     */
    public function flattenResponseExample(): array
    {
        if (empty($this->response_example)) {
            return [];
        }

        return $this->flattenArray($this->response_example);
    }

    private function flattenArray(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : (string) $key;

            if (is_array($value) && ! array_is_list($value)) {
                // Nested object → recurse
                $result = array_merge($result, $this->flattenArray($value, $fullKey));
            } elseif (is_array($value) && array_is_list($value)) {
                // Array → use wildcard and flatten first element
                if (! empty($value) && is_array($value[0])) {
                    $result = array_merge($result, $this->flattenArray($value[0], "{$fullKey}.*"));
                } else {
                    $result[$fullKey . '.*'] = $value[0] ?? null;
                }
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }
}

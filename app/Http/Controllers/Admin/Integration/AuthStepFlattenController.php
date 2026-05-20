<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\IntegrationAuthStep;
use Illuminate\Http\JsonResponse;

class AuthStepFlattenController extends Controller
{
    /**
     * GET /admin/integrations/{integrationId}/auth-steps/flatten-response
     *
     * Flattens response_example of ALL auth steps in this integration.
     * Returns a merged object of dot-notation keys from all steps.
     * Used to populate "value" dropdown when require_from = previous_step_response.
     */
    public function __invoke(int $integrationId): JsonResponse
    {
        $steps = IntegrationAuthStep::where('integration_id', $integrationId)
            ->whereNotNull('response_example')
            ->orderBy('order')
            ->get(['id', 'name', 'response_example']);

        if ($steps->isEmpty()) {
            return ApiResponse::success(new \stdClass(), 'No response examples defined for this integration.');
        }

        $merged = [];

        foreach ($steps as $step) {
            $flattened = $this->flatten($step->response_example);
            $merged    = array_merge($merged, $flattened);
        }

        return ApiResponse::success(
            (object) $merged,
            'Response keys retrieved successfully.'
        );
    }

    /**
     * Recursively flatten nested array into dot-notation key => example_value.
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : (string) $key;

            if (is_array($value) && ! array_is_list($value)) {
                $result = array_merge($result, $this->flatten($value, $fullKey));
            } elseif (is_array($value) && array_is_list($value)) {
                if (! empty($value) && is_array($value[0])) {
                    $result = array_merge($result, $this->flatten($value[0], "{$fullKey}.0"));
                } else {
                    $result[$fullKey] = $value[0] ?? null;
                }
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }
}

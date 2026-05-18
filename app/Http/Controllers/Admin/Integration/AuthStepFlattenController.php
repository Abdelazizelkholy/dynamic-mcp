<?php


namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\IntegrationAuthStep;
use Illuminate\Http\JsonResponse;

class AuthStepFlattenController extends Controller
{
    /**
     * GET /admin/integrations/{integrationId}/auth-steps/{stepId}/flatten-response
     *
     * Flattens the response_example JSON of an auth step into dot-notation keys.
     * Used to populate the "value" dropdown when require_from = previous_step_response.
     *
     * Example response_example:
     * {
     *     "data": {
     *         "access_token": "eyJ...",
     *         "expires_in": 3600,
     *         "user": { "id": 1, "name": "John" }
     *     }
     * }
     *
     * Returns:
     * [
     *     { "key": "data.access_token",  "example": "eyJ..." },
     *     { "key": "data.expires_in",    "example": 3600 },
     *     { "key": "data.user.id",       "example": 1 },
     *     { "key": "data.user.name",     "example": "John" }
     * ]
     */
    public function __invoke(int $integrationId, int $stepId): JsonResponse
    {
        $step = IntegrationAuthStep::where('id', $stepId)
            ->where('integration_id', $integrationId)
            ->first();

        if (!$step) {
            return ApiResponse::error('Auth step not found.', 404);
        }

        if (empty($step->response_example)) {
            return ApiResponse::success([], 'No response example defined for this step.');
        }

        $flattened = $this->flatten($step->response_example);

        return ApiResponse::success(
            (object) collect($flattened)->keyBy('key')->map(fn($item) => $item['example'])->toArray(),
            'Response keys retrieved successfully.'
        );
    }

    /**
     * Recursively flatten a nested array into dot-notation key => value pairs.
     *
     * @param array $data
     * @param string $prefix
     * @return array
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : (string)$key;

            if (is_array($value) && !array_is_list($value)) {
                // Nested object → recurse
                $result = array_merge($result, $this->flatten($value, $fullKey));
            } elseif (is_array($value) && array_is_list($value)) {
                // Array → flatten first element as example if exists
                if (!empty($value) && is_array($value[0])) {
                    $result = array_merge($result, $this->flatten($value[0], "{$fullKey}.0"));
                } else {
                    $result[] = [
                        'key' => $fullKey,
                        'example' => $value[0] ?? null,
                    ];
                }
            } else {
                // Scalar value
                $result[] = [
                    'key' => $fullKey,
                    'example' => $value,
                ];
            }
        }

        return $result;
    }
}

<?php
namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountSetting\UpdateAccountSettingRequest;
use App\Http\Resources\Admin\AccountSetting\AccountSettingResource;
use App\Models\Integration;
use Illuminate\Http\JsonResponse;

class IntegrationAccountSettingController extends Controller
{
    // GET /admin/integrations/{integrationId}/account-settings
    public function show(int $integrationId): JsonResponse
    {
        $integration = Integration::findOrFail($integrationId);
        $setting = $integration->accountSetting;

        if (!$setting) {
            return ApiResponse::error('Account settings not initialized yet for this integration.', 404);
        }

        return ApiResponse::success(new AccountSettingResource($setting));
    }

    // PUT /admin/integrations/{integrationId}/account-settings
    public function update(UpdateAccountSettingRequest $request, int $integrationId): JsonResponse
    {
        $integration = Integration::findOrFail($integrationId);

        $setting = $integration->accountSetting()->updateOrCreate(
            ['integration_id' => $integrationId],
            $request->validated()
        );

        return ApiResponse::success(
            new AccountSettingResource($setting),
            'Account settings updated successfully.'
        );
    }
}

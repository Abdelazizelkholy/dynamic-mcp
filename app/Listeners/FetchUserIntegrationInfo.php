<?php

namespace App\Listeners;

use App\Events\UserIntegrationConnected;
use App\Models\UserIntegrationInfo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * After a UserIntegration successfully completes its OAuth callback, calls the
 * integration's configured account_setting endpoint (e.g. Salla's
 * GET /oauth2/user/info) with the freshly-issued access token, and stores the
 * response — plus the email extracted via account_setting.email_key — on
 * UserIntegrationInfo.
 */
class FetchUserIntegrationInfo
{
    public function handle(UserIntegrationConnected $event): void
    {
        $userIntegration = $event->userIntegration;
        $accountSetting = $userIntegration->integration->accountSetting;

        if (! $accountSetting) {
            return;
        }

        $accessToken = data_get($userIntegration->credentials, 'access_token');

        if (! $accessToken) {
            return;
        }

        try {
            $response = Http::withToken($accessToken)
                ->send($accountSetting->http_method, $accountSetting->base_url);
        } catch (Throwable $e) {
            Log::warning('FetchUserIntegrationInfo: request failed', [
                'user_integration_id' => $userIntegration->id,
                'error'                => $e->getMessage(),
            ]);

            return;
        }

        if (! $response->successful()) {
            Log::warning('FetchUserIntegrationInfo: non-successful response', [
                'user_integration_id' => $userIntegration->id,
                'status'               => $response->status(),
            ]);

            return;
        }

        $body = $response->json() ?? [];

        UserIntegrationInfo::updateOrCreate(
            ['user_integration_id' => $userIntegration->id],
            [
                'email'        => data_get($body, $accountSetting->email_key),
                'raw_response' => $body,
                'fetched_at'   => now(),
            ]
        );
    }
}

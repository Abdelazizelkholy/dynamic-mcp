<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Integration;
use App\Models\IntegrationAuthStep;
use App\Models\IntegrationHeader;

class QuickbooksIntegrationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Core Integration Setup
        $integration = Integration::create([
            'name'              => 'QuickBooks',
            'base_api_url'      => 'https://quickbooks.api.intuit.com/v3',
            'documentation_url' => 'https://developer.intuit.com/app/developer/qbo/docs/get-started',
            'description_en'    => 'Sync invoices and accounting data with QuickBooks Online.',
            'description_ar'    => 'مزامنة الفواتير والبيانات المحاسبية مع كويك بوكس أونلاين.',
            'publish'           => true,
            'category'          => 'accounting',
        ]);

        // 2. Global Integration Headers
        IntegrationHeader::create([
            'integration_id' => $integration->id,
            'type'           => 'bearer',
            'header_key'     => 'Authorization',
            'require_from'   => 'user_integration',
            'value'          => 'access_token',
            'label'          => 'OAuth Access Token',
            'is_active'      => true,
            'order'          => 1,
        ]);

        // 3. Auth Steps — real 3-step Intuit OAuth2 flow (developer.intuit.com/oauth2-policy)
        //    1) redirect -> https://appcenter.intuit.com/connect/oauth2               (browser redirect, GET, query params)
        //    2) call     -> https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer (exchange code, POST, grant_type=authorization_code)
        //    3) call     -> https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer (refresh, POST, grant_type=refresh_token)
        //    NOTE: access_token expires after 1h (expires_in), refresh_token after ~100 days
        //    (x_refresh_token_expires_in) and rolls forward every time it's used to refresh.
        //    Intuit requires HTTP Basic auth (base64 client_id:client_secret) on steps 2 & 3 —
        //    stored here as a single pre-computed header value via the `config:` convention
        //    (see AuthStepRunner::resolveAdminValue()) so the raw secret never lives in this table.

        IntegrationAuthStep::create([
            'integration_id'    => $integration->id,
            'name'              => 'Redirect to Intuit Authorization',
            'step_type'         => 'login_callback',
            'auth_type'         => 'redirect',
            'http_method'       => 'GET',
            'base_endpoint_url' => 'https://appcenter.intuit.com/connect/oauth2',
            'inputs'            => [
                [
                    'key'          => 'client_id',
                    'label'        => 'Client ID',
                    'type'         => 'params',
                    'require_from' => 'user',
                ],
                [
                    'key'          => 'redirect_uri',
                    'label'        => 'Redirect URI',
                    'type'         => 'params',
                    'require_from' => 'user',
                ],
                [
                    'key'          => 'response_type',
                    'label'        => 'Response Type',
                    'type'         => 'params',
                    'require_from' => 'admin',
                    'value'        => 'code',
                ],
                [
                    'key'          => 'scope',
                    'label'        => 'Scope',
                    'type'         => 'params',
                    'require_from' => 'admin',
                    'value'        => 'com.intuit.quickbooks.accounting',
                ],
                [
                    'key'          => 'state',
                    'label'        => 'State (CSRF)',
                    'type'         => 'params',
                    'require_from' => 'front',
                ],
            ],
            'outputs'           => [],
            'response_example'  => null,
            'order'             => 1,
            'is_active'         => true,
        ]);

        IntegrationAuthStep::create([
            'integration_id'    => $integration->id,
            'name'              => 'Exchange Code for Access Token',
            'step_type'         => 'login_callback',
            'auth_type'         => 'call',
            'http_method'       => 'POST',
            'base_endpoint_url' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            'inputs'            => [
                [
                    'key'          => 'Authorization',
                    'label'        => 'Basic Auth Header',
                    'type'         => 'headers',
                    'require_from' => 'admin',
                    'value'        => 'config:services.quickbooks.basic_auth_header',
                ],
                [
                    'key'          => 'grant_type',
                    'label'        => 'Grant Type',
                    'type'         => 'body',
                    'require_from' => 'admin',
                    'value'        => 'authorization_code',
                ],
                [
                    'key'          => 'code',
                    'label'        => 'Authorization Code',
                    'type'         => 'body',
                    'require_from' => 'user',
                ],
                [
                    'key'          => 'redirect_uri',
                    'label'        => 'Redirect URI',
                    'type'         => 'body',
                    'require_from' => 'user',
                ],
            ],
            'outputs'           => ['access_token', 'refresh_token', 'expires_in', 'x_refresh_token_expires_in', 'token_type'],
            'response_example'  => [
                'token_type'                 => 'bearer',
                'access_token'               => 'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2Ijo...',
                'expires_in'                 => 3600,
                'refresh_token'              => 'AB11739069489OJyOSJ2iVKMHUpJVQEjxdKrVFXQeEjxrqiZmR',
                'x_refresh_token_expires_in' => 8726400,
            ],
            'order'             => 2,
            'is_active'         => true,
        ]);

        IntegrationAuthStep::create([
            'integration_id'    => $integration->id,
            'name'              => 'Refresh Access Token',
            'step_type'         => 'refresh_token',
            'auth_type'         => 'call',
            'http_method'       => 'POST',
            'base_endpoint_url' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            'inputs'            => [
                [
                    'key'          => 'Authorization',
                    'label'        => 'Basic Auth Header',
                    'type'         => 'headers',
                    'require_from' => 'admin',
                    'value'        => 'config:services.quickbooks.basic_auth_header',
                ],
                [
                    'key'          => 'grant_type',
                    'label'        => 'Grant Type',
                    'type'         => 'body',
                    'require_from' => 'admin',
                    'value'        => 'refresh_token',
                ],
                [
                    'key'          => 'refresh_token',
                    'label'        => 'Refresh Token',
                    'type'         => 'body',
                    // Pulled automatically from this UserIntegration's stored
                    // credentials — no input required from the caller at all.
                    'require_from' => 'user_integration',
                    'value'        => 'refresh_token',
                ],
            ],
            'outputs'           => ['access_token', 'refresh_token', 'expires_in', 'x_refresh_token_expires_in', 'token_type'],
            'response_example'  => [
                'token_type'                 => 'bearer',
                'access_token'               => 'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2Ijo...NEW',
                'expires_in'                 => 3600,
                'refresh_token'              => 'AB11739069489NEWROLLEDFORWARDTOKEN',
                'x_refresh_token_expires_in' => 8726400,
            ],
            'order'             => 3,
            'is_active'         => true,
        ]);
    }
}

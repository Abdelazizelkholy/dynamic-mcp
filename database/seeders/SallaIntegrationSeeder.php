<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Integration;
use App\Models\IntegrationAuthStep;
use App\Models\IntegrationHeader;
use App\Models\IntegrationService;
use App\Models\IntegrationServiceHeader;
use App\Models\IntegrationServiceInputGroup;
use App\Models\IntegrationServiceInput;
use App\Models\IntegrationServiceResponse;

class SallaIntegrationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Core Integration Setup
        $integration = Integration::create([
            'name'              => 'Salla',
            'base_api_url'      => 'https://api.salla.dev/admin/v2',
            'documentation_url' => 'https://docs.salla.dev',
            'description_en'    => 'Connect your store with Salla to manage orders and inventory seamlessly.',
            'description_ar'    => 'ربط متجرك مع سلة لإدارة الطلبات والمخزون بسلاسة.',
            'publish'           => true,
            'category'          => 'ecommerce',
        ]);

        // 2. Global Integration Headers (Required for every subsequent user call)
        IntegrationHeader::create([
            'integration_id' => $integration->id,
            'type'           => 'bearer',
            'header_key'     => 'Authorization',
            'require_from'   => 'user_integration', // This token comes from the OAuth process outputs
            'value'          => 'access_token',
            'label'          => 'OAuth Access Token',
            'is_active'      => true,
            'order'          => 1,
        ]);

        // 3. Auth Steps (OAuth2 Flow)
        IntegrationAuthStep::create([
            'integration_id'    => $integration->id,
            'name'              => 'Generate Access Token',
            'step_type'         => 'login_callback',
            'auth_type'         => 'bearer',
            'http_method'       => 'POST',
            'base_endpoint_url' => 'https://accounts.salla.sa/oauth2/token',
            'inputs'            => [
                'code'          => 'string',
                'client_id'     => 'string',
                'client_secret' => 'string',
                'redirect_uri'  => 'string',
                'grant_type'    => 'authorization_code'
            ],
            'outputs'           => [
                'access_token'  => 'access_token',
                'refresh_token' => 'refresh_token',
                'expires_in'    => 'expires_in'
            ],
            'response_example'  => [
                'access_token'  => 'eg_tok_12345abcde',
                'expires_in'    => 2592000,
                'refresh_token' => 'eg_ref_67890fghij',
                'token_type'    => 'Bearer',
                'scope'         => 'offline_access'
            ],
            'order'             => 1,
            'is_active'         => true,
        ]);

        // 4. Create Order Service Setup
        $service = IntegrationService::create([
            'integration_id'         => $integration->id,
            'service_name_en'        => 'Create Order',
            'service_name_ar'        => 'إنشاء طلب',
            'http_method'            => 'POST',
            'content_type'           => 'application/json',
            'endpoint_path'          => '/orders',
            'description_en'         => 'Creates a new manual order inside the merchant Salla store.',
            'description_ar'         => 'إنشاء طلب جديد يدوياً في متجر سلة الخاص بالتاجر.',
            'is_enabled'             => true,
            'inherit_global_headers' => true,
            'long_term_execution'    => false,
            'order'                  => 1,
        ]);

        // Service Specific Content-Type Header
        IntegrationServiceHeader::create([
            'integration_service_id' => $service->id,
            'type'                   => 'normal',
            'header_key'             => 'Content-Type',
            'require_from'           => 'admin',
            'value'                  => 'application/json',
            'label'                  => 'Content Type',
            'is_active'              => true,
            'order'                  => 1,
        ]);

        // 5. Input Structural Mapping for Create Order JSON
        // Salla structure requires customers object and an items array of objects.

        // Group 1: Customer (Object)
        $customerGroup = IntegrationServiceInputGroup::create([
            'integration_service_id' => $service->id,
            'key_name'               => 'customer',
            'data_type'              => 'object',
            'order'                  => 1,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $service->id,
            'group_id'               => $customerGroup->id,
            'field_type'             => 'input',
            'key'                    => 'first_name',
            'placeholder'            => 'John',
            'type'                   => 'text',
            'validation'             => 'required|string',
            'require_from'           => 'user',
            'order'                  => 1,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $service->id,
            'group_id'               => $customerGroup->id,
            'field_type'             => 'input',
            'key'                    => 'mobile',
            'placeholder'            => '512345678',
            'type'                   => 'text',
            'validation'             => 'required|string',
            'require_from'           => 'user',
            'order'                  => 2,
        ]);

        // Group 2: Items (Array of Objects)
        $itemsGroup = IntegrationServiceInputGroup::create([
            'integration_service_id' => $service->id,
            'key_name'               => 'items',
            'data_type'              => 'array_of_objects',
            'order'                  => 2,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $service->id,
            'group_id'               => $itemsGroup->id,
            'field_type'             => 'input',
            'key'                    => 'id',
            'placeholder'            => 'Product ID',
            'type'                   => 'text',
            'validation'             => 'required',
            'require_from'           => 'user',
            'order'                  => 1,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $service->id,
            'group_id'               => $itemsGroup->id,
            'field_type'             => 'input',
            'key'                    => 'quantity',
            'placeholder'            => '1',
            'type'                   => 'number',
            'validation'             => 'required|integer|min:1',
            'require_from'           => 'user',
            'order'                  => 2,
        ]);

        // 6. Response Example Structure mapping
        IntegrationServiceResponse::create([
            'integration_service_id' => $service->id,
            'response_example'       => [
                'status' => 200,
                'success' => true,
                'data' => [
                    'id' => 987654321,
                    'reference_id' => 'ORD-10023',
                    'total' => [
                        'amount' => 150.00,
                        'currency' => 'SAR'
                    ],
                    'status' => [
                        'id' => 1,
                        'name' => 'Under Review'
                    ]
                ]
            ],
            'output_filter_keys' => [
                'data.id',
                'data.reference_id',
                'data.total.amount'
            ]
        ]);
    }
}

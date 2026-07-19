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

        // 3. Auth Steps — real 3-step Salla OAuth2 flow (docs.salla.dev/421118m0)
        //    1) redirect  -> https://accounts.salla.sa/oauth2/auth   (browser redirect, GET, query params)
        //    2) call      -> https://accounts.salla.sa/oauth2/token  (exchange code, POST, body: grant_type=authorization_code)
        //    3) call      -> https://accounts.salla.sa/oauth2/token  (refresh, POST, body: grant_type=refresh_code)
        //    NOTE: response field is `expires` (seconds), not `expires_in`. Refresh tokens are single-use.

        IntegrationAuthStep::create([
            'integration_id'    => $integration->id,
            'name'              => 'Redirect to Salla Authorization',
            'step_type'         => 'login_callback',
            'auth_type'         => 'redirect',
            'http_method'       => 'GET',
            'base_endpoint_url' => 'https://accounts.salla.sa/oauth2/auth',
            'inputs'            => [
                [
                    'key'          => 'client_id',
                    'label'        => 'Client ID',
                    'type'         => 'params',
                    'require_from' => 'user',
                ],
                [
                    'key'          => 'client_secret',
                    'label'        => 'Client Secret',
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
                    'key'          => 'redirect_uri',
                    'label'        => 'Redirect URI',
                    'type'         => 'params',
                    'require_from' => 'user',
                ],
                [
                    'key'          => 'scope',
                    'label'        => 'Scope',
                    'type'         => 'params',
                    'require_from' => 'admin',
                    'value'        => 'offline_access',
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
            'base_endpoint_url' => 'https://accounts.salla.sa/oauth2/token',
            'inputs'            => [
                [
                    'key'          => 'code',
                    'label'        => 'Authorization Code',
                    'type'         => 'body',
                    'require_from' => 'user',
                ],
                [
                    'key'          => 'state',
                    'label'        => 'State (CSRF)',
                    'type'         => 'body',
                    'require_from' => 'user',
                ],
                [
                    'key'          => 'grant_type',
                    'label'        => 'Grant Type',
                    'type'         => 'body',
                    'require_from' => 'admin',
                    'value'        => 'authorization_code',
                ],
            ],
            'outputs'           => ['access_token', 'refresh_token', 'expires', 'scope', 'token_type'],
            'response_example'  => [
                'access_token'  => 'ory_at_12345abcde',
                'expires'       => 1209599,
                'refresh_token' => 'ory_rt_67890fghij',
                'scope'         => 'settings.read customers.read_write offline_access',
                'token_type'    => 'bearer',
            ],
            'order'             => 2,
            'is_active'         => true,
        ]);

        // 3b. Account Settings — "User Information Details" (docs.salla.dev)
        //     GET https://accounts.salla.sa/oauth2/user/info, Authorization: Bearer <access_token>
        //     Used to identify which merchant/user a connected UserIntegration belongs to.
        $integration->accountSetting()->updateOrCreate(
            ['integration_id' => $integration->id],
            [
                'base_url'         => 'https://accounts.salla.sa/oauth2/user/info',
                'http_method'      => 'GET',
                'email_key'        => 'data.email',
                'response_example' => [
                    'status'  => 200,
                    'success' => true,
                    'data'    => [
                        'id'         => 1689717978,
                        'name'       => 'Test User',
                        'email'      => 'test@gmail.com',
                        'mobile'     => '+96652318526',
                        'role'       => 'user',
                        'created_at' => '2021-03-27 21:51:56',
                        'merchant'   => [
                            'id'                => 847769313,
                            'username'          => 'User_name123',
                            'name'              => 'User Name',
                            'avatar'            => 'https://i.ibb.co/jyqRQfQ/avatar.jpg',
                            'store_location'    => '21.589481804199123,39.19797739999999',
                            'plan'              => 'pro',
                            'status'            => 'active',
                            'domain'            => 'https://www.domain.com',
                            'tax_number'        => '424243241321234',
                            'commercial_number' => '3552180509',
                            'created_at'        => '2021-12-31 12:59:59',
                        ],
                    ],
                ],
            ]
        );

        // 4. [SERVICE 1] Create Order Service
        $createOrderService = IntegrationService::create([
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

        $customerGroup = IntegrationServiceInputGroup::create([
            'integration_service_id' => $createOrderService->id,
            'key_name'               => 'customer',
            'data_type'              => 'object',
            'order'                  => 1,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $createOrderService->id,
            'group_id'               => $customerGroup->id,
            'field_type'             => 'input',
            'key'                    => 'first_name',
            'placeholder'            => 'John',
            'type'                   => 'input',
            'validation'             => 'required',
            'require_from'           => 'user',
            'order'                  => 1,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $createOrderService->id,
            'group_id'               => $customerGroup->id,
            'field_type'             => 'input',
            'key'                    => 'mobile',
            'placeholder' => '512345678',
            'type'        => 'input',
            'validation'  => 'required',
            'require_from'=> 'user',
            'order'       => 2,
        ]);

        $itemsGroup = IntegrationServiceInputGroup::create([
            'integration_service_id' => $createOrderService->id,
            'key_name'               => 'items',
            'data_type'              => 'array_of_objects',
            'order'                  => 2,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $createOrderService->id,
            'group_id'               => $itemsGroup->id,
            'field_type'             => 'input',
            'key'                    => 'id',
            'placeholder'            => 'Product ID',
            'type'                   => 'input',
            'validation'             => 'required',
            'require_from'           => 'user',
            'order'                  => 1,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $createOrderService->id,
            'group_id'               => $itemsGroup->id,
            'field_type'             => 'input',
            'key'                    => 'quantity',
            'placeholder'            => '1',
            'type'                   => 'input',
            'validation'             => 'required',
            'require_from'           => 'user',
            'order'                  => 2,
        ]);

        IntegrationServiceResponse::create([
            'integration_service_id' => $createOrderService->id,
            'response_example'       => [
                'status'  => 200,
                'success' => true,
                'data'    => ['id' => 987654321, 'reference_id' => 'ORD-10023']
            ],
            'output_filter_keys'     => ['data.id', 'data.reference_id']
        ]);


        // 5. [SERVICE 2] List Orders Service
        $listOrdersService = IntegrationService::create([
            'integration_id'         => $integration->id,
            'service_name_en'        => 'List Orders',
            'service_name_ar'        => 'عرض قائمة الطلبات',
            'http_method'            => 'GET',
            'content_type'           => 'application/json',
            'endpoint_path'          => '/orders',
            'description_en'         => 'Get all orders with pagination and filtering features.',
            'description_ar'         => 'جلب جميع الطلبات مع خصائص الترقيم والتصفية.',
            'is_enabled'             => true,
            'inherit_global_headers' => true,
            'long_term_execution'    => false,
            'order'                  => 2,
        ]);

        // Standalone optional query inputs for pagination/filter
        IntegrationServiceInput::create([
            'integration_service_id' => $listOrdersService->id,
            'field_type'             => 'input',
            'key'                    => 'page',
            'placeholder'            => '1',
            'type'                   => 'input',
            'validation'             => 'nullable',
            'require_from'           => 'user',
            'order'                  => 1,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $listOrdersService->id,
            'field_type'             => 'input',
            'key'                    => 'status',
            'placeholder'            => 'under_review',
            'type'                   => 'input',
            'validation'             => 'nullable',
            'require_from'           => 'user',
            'order'                  => 2,
        ]);

        IntegrationServiceResponse::create([
            'integration_service_id' => $listOrdersService->id,
            'response_example'       => [
                'status'  => 200,
                'success' => true,
                'data'    => [
                    ['id' => 987654321, 'reference_id' => 'ORD-10023', 'status' => ['name' => 'Completed']]
                ],
                'cursor' => ['next' => 'eyJjdXJzb3IiOiJ...']
            ],
            'output_filter_keys'     => ['data.*.id', 'data.*.reference_id']
        ]);


        // 6. [SERVICE 3] Update Order Service
        $updateOrderService = IntegrationService::create([
            'integration_id'         => $integration->id,
            'service_name_en'        => 'Update Order',
            'service_name_ar'        => 'تحديث بيانات الطلب',
            'http_method'            => 'PUT',
            'content_type'           => 'application/json',
            'endpoint_path'          => '/orders/{id}', // Standard dynamic endpoint path
            'description_en'         => 'Update basic details of a specific order.',
            'description_ar'         => 'تحديث البيانات الأساسية لطلب معين.',
            'is_enabled'             => true,
            'inherit_global_headers' => true,
            'long_term_execution'    => false,
            'order'                  => 3,
        ]);

        // Path Parameter Input
        IntegrationServiceInput::create([
            'integration_service_id' => $updateOrderService->id,
            'field_type'             => 'input',
            'key'                    => 'id',
            'placeholder'            => 'Order ID Path Param',
            'type'                   => 'input',
            'validation'             => 'required',
            'require_from'           => 'user',
            'order'                  => 1,
        ]);

        // Body input
        IntegrationServiceInput::create([
            'integration_service_id' => $updateOrderService->id,
            'field_type'             => 'input',
            'key'                    => 'notes',
            'placeholder'            => 'Add custom order notes',
            'type'                   => 'input',
            'validation'             => 'nullable',
            'require_from'           => 'user',
            'order'                  => 2,
        ]);

        IntegrationServiceResponse::create([
            'integration_service_id' => $updateOrderService->id,
            'response_example'       => [
                'status'  => 200,
                'success' => true,
                'data'    => ['id' => 987654321, 'notes' => 'Updated notes']
            ],
            'output_filter_keys'     => ['data.id']
        ]);


        // 7. [SERVICE 4] Update Order Status Service
        $updateStatusService = IntegrationService::create([
            'integration_id'         => $integration->id,
            'service_name_en'        => 'Update Order Status',
            'service_name_ar'        => 'تحديث حالة الطلب',
            'http_method'            => 'POST',
            'content_type'           => 'application/json',
            'endpoint_path'          => '/orders/{id}/status',
            'description_en'         => 'Change order fulfillment or verification status.',
            'description_ar'         => 'تغيير حالة تنفيذ أو التحقق من الطلب.',
            'is_enabled'             => true,
            'inherit_global_headers' => true,
            'long_term_execution'    => false,
            'order'                  => 4,
        ]);

        // Path Parameter
        IntegrationServiceInput::create([
            'integration_service_id' => $updateStatusService->id,
            'field_type'             => 'input',
            'key'                    => 'id',
            'placeholder'            => 'Order ID',
            'type'                   => 'input',
            'validation'             => 'required',
            'require_from'           => 'user',
            'order'                  => 1,
        ]);

        // Body key status
        IntegrationServiceInput::create([
            'integration_service_id' => $updateStatusService->id,
            'field_type'             => 'input',
            'key'                    => 'slug',
            'placeholder'            => 'delivering / completed',
            'type'                   => 'input',
            'validation'             => 'required',
            'require_from'           => 'user',
            'order'                  => 2,
        ]);

        IntegrationServiceResponse::create([
            'integration_service_id' => $updateStatusService->id,
            'response_example'       => [
                'status'  => 200,
                'success' => true,
                'message' => 'Order status has been updated successfully.'
            ],
            'output_filter_keys'     => ['success', 'message']
        ]);


        // 8. [SERVICE 5] Create Product Service
        $createProductService = IntegrationService::create([
            'integration_id'         => $integration->id,
            'service_name_en'        => 'Create Product',
            'service_name_ar'        => 'إنشاء منتج',
            'http_method'            => 'POST',
            'content_type'           => 'application/json',
            'endpoint_path'          => '/products',
            'description_en'         => 'Add a new product to the Salla store catalog.',
            'description_ar'         => 'إضافة منتج جديد لكتالوج متجر سلة.',
            'is_enabled'             => true,
            'inherit_global_headers' => true,
            'long_term_execution'    => false,
            'order'                  => 5,
        ]);

        // Standard Product fields
        IntegrationServiceInput::create([
            'integration_service_id' => $createProductService->id,
            'field_type'             => 'input',
            'key'                    => 'name',
            'placeholder'            => 'Product Name',
            'type'                   => 'input',
            'validation'             => 'required',
            'require_from'           => 'user',
            'order'                  => 1,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $createProductService->id,
            'field_type'             => 'input',
            'key'                    => 'price',
            'placeholder'            => '100',
            'type'                   => 'input',
            'validation'             => 'required',
            'require_from'           => 'user',
            'order'                  => 2,
        ]);

        IntegrationServiceInput::create([
            'integration_service_id' => $createProductService->id,
            'field_type'             => 'input',
            'key'                    => 'quantity',
            'placeholder'            => '10',
            'type'                   => 'input',
            'validation'             => 'required',
            'require_from'           => 'user',
            'order'                  => 3,
        ]);

        IntegrationServiceResponse::create([
            'integration_service_id' => $createProductService->id,
            'response_example'       => [
                'status'  => 200,
                'success' => true,
                'data'    => ['id' => 123456, 'name' => 'Product Name', 'sku' => 'SKU-123']
            ],
            'output_filter_keys'     => ['data.id', 'data.sku']
        ]);
    }
}

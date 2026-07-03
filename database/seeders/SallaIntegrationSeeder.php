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

        // 3. Auth Steps (Using fixed 'login_callback' step_type)
        IntegrationAuthStep::create([
            'integration_id'    => $integration->id,
            'name'              => 'Generate Access Token',
            'step_type'         => 'login_callback',
            'auth_type'         => 'redirect',
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

<?php

namespace App\Http\Controllers\Admin;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EnumController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success([

            // ── Integration ─────────────────────────────────────────────────
            'integration_categories' => [
                ['key' => 'ecommerce',  'label' => 'E-Commerce'],
                ['key' => 'payment',    'label' => 'Payment'],
                ['key' => 'shipping',   'label' => 'Shipping'],
                ['key' => 'crm',        'label' => 'CRM'],
                ['key' => 'marketing',  'label' => 'Marketing'],
                ['key' => 'accounting', 'label' => 'Accounting'],
                ['key' => 'social',     'label' => 'Social'],
                ['key' => 'other',      'label' => 'Other'],
            ],

            // ── Auth Steps ──────────────────────────────────────────────────
            'auth_step_types' => [
                ['key' => 'login_callback',  'label' => 'Login & Callback'],
                ['key' => 'set_credentials', 'label' => 'Set Credentials'],
                ['key' => 'refresh_token',   'label' => 'Refresh Access Token'],
            ],

            'auth_types' => [
                ['key' => 'oauth2',  'label' => 'OAuth 2.0'],
                ['key' => 'api_key', 'label' => 'API Key'],
                ['key' => 'basic',   'label' => 'Basic Auth'],
                ['key' => 'bearer',  'label' => 'Bearer Token'],
                ['key' => 'custom',  'label' => 'Custom'],
            ],

            'http_methods' => [
                ['key' => 'GET',    'label' => 'GET'],
                ['key' => 'POST',   'label' => 'POST'],
                ['key' => 'PUT',    'label' => 'PUT'],
                ['key' => 'PATCH',  'label' => 'PATCH'],
                ['key' => 'DELETE', 'label' => 'DELETE'],
            ],

            'input_types' => [
                ['key' => 'body',    'label' => 'Body'],
                ['key' => 'params',  'label' => 'Params'],
                ['key' => 'headers', 'label' => 'Headers'],
            ],


            'auth_step_input_require_from' => [
                ['key' => 'admin',                  'label' => 'Admin'],
                ['key' => 'user',                   'label' => 'User'],
                ['key' => 'front',                  'label' => 'Front'],
                ['key' => 'response',               'label' => 'Response'],
                ['key' => 'user_integration',       'label' => 'User Integration'],
                ['key' => 'previous_step_response', 'label' => 'Previous Step Response'],
            ],


            // ── Headers ─────────────────────────────────────────────────────
            'header_types' => [
                ['key' => 'normal',     'label' => 'Normal'],
                ['key' => 'bearer',     'label' => 'Bearer'],
                ['key' => 'basic_auth', 'label' => 'Basic Auth'],
            ],

            'header_require_from' => [
                ['key' => 'admin',            'label' => 'Admin'],
                ['key' => 'user_integration', 'label' => 'User Integration'],
            ],

            // ── Services ────────────────────────────────────────────────────
            'service_content_types' => [
                ['key' => 'application/json',                  'label' => 'JSON'],
                ['key' => 'multipart/form-data',               'label' => 'Form Data'],
                ['key' => 'application/x-www-form-urlencoded', 'label' => 'URL Encoded'],
                ['key' => 'text/plain',                        'label' => 'Plain Text'],
            ],

        ], 'Enums retrieved successfully.');
    }
}

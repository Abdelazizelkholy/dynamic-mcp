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
                ['key' => 'call',  'label' => 'Call'],
                ['key' => 'redirect ', 'label' => 'Redirect ']
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

            'global_body_require_from' => [
                ['key' => 'admin',            'label' => 'Admin'],
                ['key' => 'user_integration', 'label' => 'User Integration'],
            ],

            'service_param_types' => [
                ['key' => 'static',           'label' => 'Static'],
                ['key' => 'user_integration', 'label' => 'User Integration'],
                ['key' => 'params',           'label' => 'From Input'],
            ],


            'input_field_types' => [
                ['key' => 'input',          'label' => 'Text'],
                ['key' => 'select',         'label' => 'Select'],
                ['key' => 'dynamic_select', 'label' => 'Dynamic Select'],
                ['key' => 'boolean',        'label' => 'Boolean'],
            //    ['key' => 'group',          'label' => 'Group'],
                ['key' => 'file',           'label' => 'File'],
                ['key' => 'file_url',       'label' => 'File URL'],
                ['key' => 'files',          'label' => 'Multiple Files'],
                ['key' => 'files_url',      'label' => 'Files URL'],
                ['key' => 'date',           'label' => 'Date'],       // ← new
                ['key' => 'datetime',       'label' => 'Date Time'],  // ← new
            ],

            'input_types' => [
                ['key' => 'params', 'label' => 'Params'],
                ['key' => 'input',  'label' => 'Input'],
            ],

            'input_key_types' => [
                ['key' => 'body',    'label' => 'Body'],
                ['key' => 'headers', 'label' => 'Headers'],
            ],

            'input_validations' => [
                ['key' => 'required', 'label' => 'Required'],
                ['key' => 'nullable', 'label' => 'Nullable'],
            ],

            'input_require_from' => [
                ['key' => 'admin',              'label' => 'Admin'],
                ['key' => 'user',               'label' => 'User'],
                ['key' => 'front',              'label' => 'Front'],
                ['key' => 'response',           'label' => 'Response'],
                ['key' => 'user_integration',   'label' => 'User Integration'],
                ['key' => 'dependency_service', 'label' => 'Dependency Service'],  // ← new
            ],

            'input_group_data_types' => [
                ['key' => 'object',           'label' => 'Object'],
                ['key' => 'array',            'label' => 'Array'],
                ['key' => 'array_of_objects', 'label' => 'Array of Object'],
            ],

            'response_view_data_types' => [
                ['key' => 'text', 'label' => 'Text'],
                ['key' => 'file', 'label' => 'File'],
            ],


        ], 'Enums retrieved successfully.');
    }
}

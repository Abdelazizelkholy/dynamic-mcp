<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /*
    |--------------------------------------------------------------------------
    | integration_service_inputs
    |--------------------------------------------------------------------------
    | Individual input fields — can belong to a group OR be standalone.
    |
    | group_id = null  → single/standalone field
    | group_id = X     → belongs to a group
    |
    | field_type:
    |   input           → plain text input
    |   select          → static dropdown  (options JSON)
    |   dynamic_select  → loads options from another service (service_id FK)
    |   boolean         → true/false toggle
    |   group           → nested group reference
    |   file            → single file upload
    |   file_url        → file via URL string
    |   files           → multiple files upload
    |   files_url       → multiple files via URL
    |
    | key_type:
    |   body   → sent in request body
    |   query  → sent as query param  ?key=value
    |   header → sent as request header
    |   path   → sent as URL path segment  /resource/{key}
    |
    | validation:
    |   required | nullable | sometimes
    |
    | require_from:
    |   admin | user | front | user_integration | previous_step_response
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('integration_service_inputs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('integration_service_id')
                ->constrained('integration_services')
                ->cascadeOnDelete();

            // null = standalone field, set = belongs to a group
            $table->foreignId('group_id')
                ->nullable()
                ->constrained('integration_service_input_groups')
                ->cascadeOnDelete();

            $table->enum('field_type', [
                'input',
                'select',
                'dynamic_select',
                'boolean',
                'group',
                'file',
                'file_url',
                'files',
                'files_url',
            ])->default('input');

            $table->string('key');
            $table->string('placeholder')->nullable();

            $table->enum('type', [
                'text', 'number', 'email', 'password', 'url', 'date', 'textarea',
            ])->nullable();   // only relevant for field_type = input

            $table->enum('key_type', ['body', 'query', 'header', 'path'])
                ->default('body');

            $table->enum('validation', ['required', 'nullable', 'sometimes'])
                ->default('required');

            $table->enum('require_from', [
                'admin',
                'user',
                'front',
                'user_integration',
                'previous_step_response',
            ])->default('user');

            // For field_type = select → static options
            // e.g. ["value1", "value2"] or [{"label":"X","value":"x"}]
            $table->json('options')->nullable();

            // For field_type = dynamic_select → references another service
            $table->foreignId('dynamic_service_id')
                ->nullable()
                ->constrained('integration_services')
                ->nullOnDelete();

            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_service_inputs');
    }
};

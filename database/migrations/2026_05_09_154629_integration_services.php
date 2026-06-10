<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /*
    |--------------------------------------------------------------------------
    | integration_services
    |--------------------------------------------------------------------------
    | Represents an API endpoint/operation exposed by an integration.
    |
    | Fields from UI:
    | - service_name          → Service Name
    | - http_method           → GET | POST | PUT | PATCH | DELETE
    | - content_type          → application/json | multipart/form-data | ...
    | - endpoint_path         → relative path  e.g. /orders
    | - logo                  → optional icon name / media
    | - base_url_override     → overrides integration base_api_url if set
    | - description_en        → Description (EN)
    | - description_ar        → الوصف (AR)
    | - is_enabled            → Service Enabled toggle
    | - inherit_global_headers→ Auto-apply shared headers toggle
    | - long_term_execution   → Async / Polling required toggle
    | - dependency_service_ids→ JSON array of service IDs this depends on
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('integration_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained()->cascadeOnDelete();

            $table->string('service_name_en')->nullable();
            $table->string('service_name_ar')->nullable();
            $table->enum('http_method', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])->default('GET');
            $table->enum('content_type', [
                'application/json',
                'multipart/form-data',
                'application/x-www-form-urlencoded',
                'text/plain',
            ])->default('application/json');

            $table->string('endpoint_path');                    // e.g. /orders
            $table->string('logo')->nullable();                 // optional icon name
            $table->string('base_url_override')->nullable();    // overrides integration base_api_url

            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();

            // Toggles
            $table->boolean('is_enabled')->default(true);
            $table->boolean('inherit_global_headers')->default(true);
            $table->boolean('long_term_execution')->default(false);

            // Multi-select: IDs of other services this service depends on
            // stored as JSON array e.g. [1, 3, 5]
            $table->json('dependency_service_ids')->nullable();

            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_services');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /*
    |--------------------------------------------------------------------------
    | integration_service_headers
    |--------------------------------------------------------------------------
    | Per-service headers — applied only to this specific endpoint.
    |
    | type         → normal | bearer | basic_auth
    | header_key   → e.g. Authorization, X-Api-Key, Content-Type
    | require_from → where the value comes from:
    |                admin            → static value set by admin
    |                user             → end user provides at runtime
    |                user_integration → resolved from user integration outputs
    | value        → static value OR key name to resolve
    | label        → display label
    | description  → optional description
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('integration_service_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_service_id')
                ->constrained('integration_services')
                ->cascadeOnDelete();

            $table->enum('type', ['normal', 'bearer', 'basic_auth'])->default('normal');
            $table->string('header_key');
            $table->string('concatenate_key')->nullable();    
            $table->enum('require_from', ['admin', 'user', 'user_integration'])->default('admin');
            $table->string('value')->nullable();

            $table->string('label')->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_service_headers');
    }
};

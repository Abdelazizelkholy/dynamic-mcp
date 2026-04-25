<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | AuthStep Types
        |--------------------------------------------------------------------------
        | login_callback   → OAuth login + redirect callback (e.g. Salla)
        | set_credentials  → Admin sets API key/secret manually
        | refresh_token    → Refresh expired access token automatically
        |--------------------------------------------------------------------------
        |
        | AuthStep HTTP Methods: GET | POST | PUT | PATCH | DELETE
        |
        | inputs  → what the admin/user must supply  (JSON array of field configs)
        | outputs → what the response returns         (JSON array of field names)
        |
        */

        Schema::create('integration_auth_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->enum('step_type', ['login_callback', 'set_credentials', 'refresh_token']);
            $table->enum('auth_type', ['oauth2', 'api_key', 'basic', 'bearer', 'custom'])->default('oauth2');
            $table->enum('http_method', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])->default('POST');

            $table->string('base_endpoint_url');

            // JSON array: [{ "key": "client_id", "label": "Client ID", "type": "text", "required": true }]
            $table->json('inputs')->nullable();

            // JSON array: ["access_token", "refresh_token", "expires_in"]
            $table->json('outputs')->nullable();

            // Example response shown in UI
            $table->json('response_example')->nullable();

            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_auth_steps');
    }
};

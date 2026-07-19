<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('integration_id')->constrained()->cascadeOnDelete();

            $table->enum('status', ['pending', 'connected', 'expired', 'revoked', 'error'])
                ->default('pending');

            // Resolved auth-step outputs: access_token, refresh_token, expires, scope, token_type,
            // or manually-entered credentials for a set_credentials-only integration.
            $table->json('credentials')->nullable();

            // Last raw response from the provider — useful for debugging a failed/error state.
            $table->json('last_response')->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_refreshed_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'integration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_integrations');
    }
};

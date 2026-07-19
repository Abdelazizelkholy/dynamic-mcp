<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_integration_auth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_integration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('integration_auth_step_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('status', ['success', 'failed']);

            // Resolved request (secrets masked) and raw response for this single step execution.
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamp('executed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_integration_auth_logs');
    }
};

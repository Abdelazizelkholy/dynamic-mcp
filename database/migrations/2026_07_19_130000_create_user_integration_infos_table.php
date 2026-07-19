<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_integration_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_integration_id')->unique()->constrained()->cascadeOnDelete();

            // Extracted via the integration's account_setting.email_key (dot-notation).
            $table->string('email')->nullable();

            // Full response body from the integration's account_setting endpoint (e.g. Salla's /oauth2/user/info).
            $table->json('raw_response')->nullable();

            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_integration_infos');
    }
};

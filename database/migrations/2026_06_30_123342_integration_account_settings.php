<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_account_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained()->onDelete('cascade');

            $table->string('base_url');
            $table->string('http_method')->default('GET');
            $table->string('email_key');

            $table->json('response_example');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_account_settings');
    }
};

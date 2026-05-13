<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('integration_service_params', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_service_id')
                ->constrained('integration_services')
                ->cascadeOnDelete();

            // static→ admin types a fixed value  e.g. "test"
            // user_integration→ dropdown selects a key from user integration outputs e.g. "Authorization"
            $table->enum('type', ['static', 'user_integration'])->default('static');

            $table->string('value');        // the actual value or the selected key name

            $table->unsignedInteger('order')->default(0);   // position in the URL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_service_params');
    }
};

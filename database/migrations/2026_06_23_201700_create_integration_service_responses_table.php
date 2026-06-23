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
        Schema::create('integration_service_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_service_id')
                ->unique()                           // one record per service
                ->constrained('integration_services')
                ->cascadeOnDelete();

            $table->json('response_example')->nullable();

            // [{ "key": "data.*.id", "is_used": true }, ...]
            $table->json('output_filter_keys')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_service_responses');
    }
};

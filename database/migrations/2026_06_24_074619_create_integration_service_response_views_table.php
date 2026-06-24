<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /*
    |--------------------------------------------------------------------------
    | integration_service_response_views
    |--------------------------------------------------------------------------
    | Defines how the service response is displayed to the user.
    |
    | key       → selected from the flattened response_example keys
    |             e.g. "data.*.id", "data.*.name"
    | data_type → text | file
    | order     → display order
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('integration_service_response_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_service_id')
                ->constrained('integration_services')
                ->cascadeOnDelete();

            $table->string('key');                                  // e.g. data.*.id
            $table->enum('data_type', ['text', 'file'])->default('text');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_service_response_views');
    }
};

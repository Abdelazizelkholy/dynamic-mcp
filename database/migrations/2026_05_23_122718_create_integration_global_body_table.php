<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /*
    |--------------------------------------------------------------------------
    | integration_global_bodies
    |--------------------------------------------------------------------------
    | Global body params applied to all services in an integration.
    |
    | require_from:
    |   admin            → admin sets static value
    |   user_integration → value resolved from user integration outputs
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('integration_global_body', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')
                ->constrained('integrations')
                ->cascadeOnDelete();

            $table->string('key');
            $table->enum('require_from', ['admin', 'user_integration'])->default('admin');
            $table->string('value')->nullable();
            $table->string('label')->nullable();
            $table->text('description')->nullable();

            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_global_body');
    }
};

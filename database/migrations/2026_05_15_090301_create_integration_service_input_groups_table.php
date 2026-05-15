<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /*
    |--------------------------------------------------------------------------
    | integration_service_input_groups
    |--------------------------------------------------------------------------
    | A group is a container for multiple related inputs.
    | data_type defines how the group is structured in the request body.
    |
    | data_type:
    |   object          → { key: { field1: val, field2: val } }
    |   array_of_objects→ { key: [ { field1: val }, ... ] }
    |   array           → { key: [ val1, val2, ... ] }
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('integration_service_input_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_service_id')
                ->constrained('integration_services')
                ->cascadeOnDelete();

            $table->string('key_name');
            $table->enum('data_type', ['object', 'array_of_objects', 'array'])
                ->default('object');

            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_service_input_groups');
    }
};

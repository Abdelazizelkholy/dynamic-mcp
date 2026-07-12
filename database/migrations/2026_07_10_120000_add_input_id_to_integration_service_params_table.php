<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | New param type: 'params'
    |--------------------------------------------------------------------------
    | static           → admin types a fixed value                     e.g. "test"
    | user_integration → dropdown selects a key from user integration  e.g. "Authorization"
    | params           → value is resolved at runtime from one of this service's
    |                     own Input fields (input_id → integration_service_inputs.id)
    |                     e.g. the "id" input feeding /orders/{id}/status
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::table('integration_service_params', function (Blueprint $table) {
            $table->foreignId('input_id')
                ->nullable()
                ->after('integration_service_id')
                ->constrained('integration_service_inputs')
                ->nullOnDelete();
        });

        DB::statement("ALTER TABLE integration_service_params
        MODIFY COLUMN type ENUM('static','user_integration','params') NOT NULL DEFAULT 'static'");

        DB::statement("ALTER TABLE integration_service_params
        MODIFY COLUMN value VARCHAR(500) NULL");
    }

    public function down(): void
    {
        Schema::table('integration_service_params', function (Blueprint $table) {
            $table->dropConstrainedForeignId('input_id');
        });
    }
};

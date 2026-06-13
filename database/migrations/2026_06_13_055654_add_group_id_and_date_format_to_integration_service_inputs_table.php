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
        Schema::table('integration_service_inputs', function (Blueprint $table) {
            $table->foreignId('parent_group_id')
                ->nullable()
                ->after('group_id')
                ->constrained('integration_service_input_groups')
                ->nullOnDelete();

            $table->string('date_format')->nullable()->after('options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integration_service_inputs', function (Blueprint $table) {
            $table->dropForeign(['parent_group_id']);
            $table->dropColumn(['parent_group_id', 'date_format']);
        });
    }
};

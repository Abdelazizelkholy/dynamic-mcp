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
            // Always mirrors field_type on write (see IntegrationServiceInputRepository).
            $table->string('label')->nullable()->after('key');

            // Static value used when require_from = admin (mirrors IntegrationHeader.value
            // / IntegrationGlobalBody.value).
            $table->string('value')->nullable()->after('placeholder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integration_service_inputs', function (Blueprint $table) {
            $table->dropColumn(['label', 'value']);
        });
    }
};

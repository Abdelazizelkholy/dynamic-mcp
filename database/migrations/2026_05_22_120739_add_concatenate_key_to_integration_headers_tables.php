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
        // integration_headers
        Schema::table('integration_headers', function (Blueprint $table) {
            $table->string('concatenate_key')->nullable()->after('header_key');
        });

        // integration_service_headers
        Schema::table('integration_service_headers', function (Blueprint $table) {
            $table->string('concatenate_key')->nullable()->after('header_key');
        });
    }

    public function down(): void
    {
        Schema::table('integration_headers', function (Blueprint $table) {
            $table->dropColumn('concatenate_key');
        });

        Schema::table('integration_service_headers', function (Blueprint $table) {
            $table->dropColumn('concatenate_key');
        });
    }


};

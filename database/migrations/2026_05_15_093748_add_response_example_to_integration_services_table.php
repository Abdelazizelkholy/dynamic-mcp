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
        Schema::table('integration_services', function (Blueprint $table) {
            $table->json('response_example')->nullable()->after('long_term_execution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integration_services', function (Blueprint $table) {
            $table->dropColumn('response_example');
        });
    }
};

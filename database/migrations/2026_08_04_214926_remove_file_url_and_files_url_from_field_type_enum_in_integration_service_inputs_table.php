<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE integration_service_inputs
        MODIFY COLUMN field_type ENUM(
            'input',
            'select',
            'dynamic_select',
            'boolean',
            'group',
            'file',
            'files',
            'date',
            'datetime'
        ) NOT NULL DEFAULT 'input'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE integration_service_inputs
        MODIFY COLUMN field_type ENUM(
            'input',
            'select',
            'dynamic_select',
            'boolean',
            'group',
            'file',
            'file_url',
            'files',
            'files_url',
            'date',
            'datetime'
        ) NOT NULL DEFAULT 'input'");
    }
};

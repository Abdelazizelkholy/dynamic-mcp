<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Header Types
        |--------------------------------------------------------------------------
        | normal      → plain key/value header
        | bearer      → Authorization: Bearer {value}
        | basic_auth  → Authorization: Basic base64(user:pass)
        |
        | Require From
        |--------------------------------------------------------------------------
        | admin            → value set by admin (static)
        | user_integration → value comes from user's integration auth outputs
        |                    (e.g. access_token returned from login_callback step)
        */

        Schema::create('integration_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['normal', 'bearer', 'basic_auth'])->default('normal');
            $table->string('header_key');                          // e.g. Authorization, X-Api-Key

            // Inputs section
            $table->enum('require_from', ['admin', 'user_integration'])->default('admin');
            $table->string('value')->nullable();                   // static value (if require_from = admin)
            // or output key name (if require_from = user_integration)
            // e.g. "access_token"

            $table->string('label')->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_headers');
    }
};

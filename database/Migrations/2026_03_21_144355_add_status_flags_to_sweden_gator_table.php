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
        if (! Schema::hasTable('sweden_gator')) {
            return;
        }

        Schema::table('sweden_gator', function (Blueprint $table) {
            $table->after('updated_at', function (Blueprint $table) {
                $table->boolean('is_active')->default(true);
                $table->boolean('is_queue')->default(false);
                $table->boolean('is_done')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sweden_gator')) {
            return;
        }

        Schema::table('sweden_gator', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'is_queue', 'is_done']);
        });
    }
};

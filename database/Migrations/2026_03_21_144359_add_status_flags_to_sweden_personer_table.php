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
        if (! Schema::hasTable('sweden_personer')) {
            return;
        }

        Schema::table('sweden_personer', function (Blueprint $table) {
            $table->after('is_active', function (Blueprint $table) {
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
        if (! Schema::hasTable('sweden_personer')) {
            return;
        }

        Schema::table('sweden_personer', function (Blueprint $table) {
            $table->dropColumn(['is_queue', 'is_done']);
        });
    }
};

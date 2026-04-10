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
        if (! Schema::hasTable('sweden_kommuner')) {
            return;
        }

        Schema::table('sweden_kommuner', function (Blueprint $table) {
            $table->unsignedBigInteger('personer_count')->default(0)->after('personer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sweden_kommuner')) {
            return;
        }

        Schema::table('sweden_kommuner', function (Blueprint $table) {
            $table->dropColumn('personer_count');
        });
    }
};

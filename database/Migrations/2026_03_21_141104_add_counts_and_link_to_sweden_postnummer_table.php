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
        if (! Schema::hasTable('sweden_postnummer')) {
            return;
        }

        Schema::table('sweden_postnummer', function (Blueprint $table) {
            $table->after('foretag', function (Blueprint $table) {
                $table->unsignedInteger('gator')->nullable()->default(null);
                $table->unsignedInteger('adresser')->nullable()->default(null);
                $table->string('ratsit_link')->nullable()->default(null);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sweden_postnummer')) {
            return;
        }

        Schema::table('sweden_postnummer', function (Blueprint $table) {
            $table->dropColumn(['gator', 'adresser', 'ratsit_link']);
        });
    }
};

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
        Schema::table('sweden_postnummer', function (Blueprint $table) {
            $table->unsignedInteger('personer_hitta_queue')->default(0)->after('personer');
            $table->unsignedInteger('personer_merinfo_queue')->default(0)->after('personer_hitta_queue');
            $table->unsignedInteger('personer_ratsit_queue')->default(0)->after('personer_merinfo_queue');
            $table->unsignedInteger('personer_hitta_saved')->nullable()->after('personer_ratsit_queue');
            $table->unsignedInteger('personer_merinfo_saved')->nullable()->after('personer_hitta_saved');
            $table->unsignedInteger('personer_ratsit_saved')->nullable()->after('personer_merinfo_saved');
            $table->string('country')->nullable()->after('lan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sweden_postnummer', function (Blueprint $table) {
            $table->dropColumn([
                'personer_hitta_queue',
                'personer_merinfo_queue',
                'personer_ratsit_queue',
                'personer_hitta_saved',
                'personer_merinfo_saved',
                'personer_ratsit_saved',
                'country',
            ]);
        });
    }
};

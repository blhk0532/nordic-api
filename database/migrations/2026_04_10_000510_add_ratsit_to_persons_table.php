<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('ratsit_phone')->nullable()->after('merinfo_phone');
            $table->boolean('ratsit_is_house')->nullable()->after('merinfo_is_house');
            $table->unsignedInteger('sweden_personer_id')->nullable()->after('merinfo_id');
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn(['ratsit_phone', 'ratsit_is_house', 'sweden_personer_id']);
        });
    }
};

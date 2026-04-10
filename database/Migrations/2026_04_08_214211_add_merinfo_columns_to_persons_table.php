<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->ulid('merinfo_id')->nullable()->unique()->after('merinfo');
            $table->string('merinfo_phone')->nullable()->after('merinfo_id');
            $table->string('personal_number')->nullable()->after('merinfo_phone');
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn(['merinfo_id', 'merinfo_phone', 'personal_number']);
        });
    }
};

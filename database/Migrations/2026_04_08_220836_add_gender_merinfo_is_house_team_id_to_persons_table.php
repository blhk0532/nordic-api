<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('personal_number');
            $table->boolean('merinfo_is_house')->nullable()->after('gender');
            $table->ulid('team_id')->nullable()->index()->after('merinfo_is_house');
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn(['gender', 'merinfo_is_house', 'team_id']);
        });
    }
};

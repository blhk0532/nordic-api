<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('id')->constrained('teams')->cascadeOnDelete();
            $table->unique(['team_id', 'group', 'name']);
            $table->dropUnique(['group', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['team_id']);
            $table->dropColumn('team_id');
            $table->unique(['group', 'name']);
        });
    }
};

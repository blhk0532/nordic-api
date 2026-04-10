<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('component_groups', function (Blueprint $table): void {
            $table->unsignedBigInteger('team_id')->nullable()->after('visible');
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
        });

        Schema::table('incidents', function (Blueprint $table): void {
            $table->unsignedBigInteger('team_id')->nullable()->after('visible');
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
        });

        Schema::table('metrics', function (Blueprint $table): void {
            $table->unsignedBigInteger('team_id')->nullable()->after('visible');
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('component_groups', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('incidents', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('metrics', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};

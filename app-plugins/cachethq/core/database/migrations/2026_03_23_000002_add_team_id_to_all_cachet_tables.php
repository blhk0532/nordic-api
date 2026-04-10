<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table): void {
            $table->unsignedBigInteger('team_id')->nullable()->after('meta');
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
        });

        Schema::table('cachet_schedules', function (Blueprint $table): void {
            $table->unsignedBigInteger('team_id')->nullable()->after('completed_at');
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
        });

        Schema::table('incident_templates', function (Blueprint $table): void {
            $table->unsignedBigInteger('team_id')->nullable()->after('engine');
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
        });

        Schema::table('subscribers', function (Blueprint $table): void {
            $table->unsignedBigInteger('team_id')->nullable()->after('verified_at');
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
        });

        Schema::table('webhook_subscriptions', function (Blueprint $table): void {
            $table->unsignedBigInteger('team_id')->nullable()->after('send_all_events');
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('cachet_schedules', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('incident_templates', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('subscribers', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('webhook_subscriptions', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};

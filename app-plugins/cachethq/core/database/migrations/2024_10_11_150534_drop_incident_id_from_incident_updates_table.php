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
        if (Schema::hasTable('incident_updates')) {
            Schema::table('incident_updates', function (Blueprint $table) {
                if (Schema::hasColumn('incident_updates', 'incident_id')) {
                    $table->dropIndex('incident_updates_incident_id_index');
                    $table->dropColumn('incident_id');
                }
            });
        }
    }
};

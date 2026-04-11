<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PostgreSQL compatible migration for adding performance indexes.
     * For TEXT columns, use text_pattern_ops for efficient pattern matching.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            Schema::table('ratsit_data', function (Blueprint $table) {
                $table->index('postnummer', 'ratsit_data_postnummer_idx')->ifNotExists();
                $table->index('postort', 'ratsit_data_postort_idx')->ifNotExists();
                $table->index('kommun', 'ratsit_data_kommun_idx')->ifNotExists();
                $table->index('lan', 'ratsit_data_lan_idx')->ifNotExists();
            });

            return;
        }

        // Check if indexes exist before creating
        $indexes = DB::select("
            SELECT indexname FROM pg_indexes
            WHERE tablename = 'ratsit_data' AND indexname IN (
                'ratsit_data_postnummer_idx',
                'ratsit_data_postort_idx',
                'ratsit_data_kommun_idx',
                'ratsit_data_lan_idx'
            )
        ");

        $existingIndexes = collect($indexes)->pluck('indexname')->all();

        // For TEXT columns in PostgreSQL, we use text_pattern_ops for pattern matching
        // This allows efficient LIKE queries and sorting
        if (! in_array('ratsit_data_postnummer_idx', $existingIndexes)) {
            DB::statement('CREATE INDEX ratsit_data_postnummer_idx ON ratsit_data (postnummer text_pattern_ops)');
        }

        if (! in_array('ratsit_data_postort_idx', $existingIndexes)) {
            DB::statement('CREATE INDEX ratsit_data_postort_idx ON ratsit_data (postort text_pattern_ops)');
        }

        if (! in_array('ratsit_data_kommun_idx', $existingIndexes)) {
            DB::statement('CREATE INDEX ratsit_data_kommun_idx ON ratsit_data (kommun text_pattern_ops)');
        }

        if (! in_array('ratsit_data_lan_idx', $existingIndexes)) {
            DB::statement('CREATE INDEX ratsit_data_lan_idx ON ratsit_data (lan text_pattern_ops)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            Schema::table('ratsit_data', function (Blueprint $table) {
                $table->dropIndex('ratsit_data_postnummer_idx');
                $table->dropIndex('ratsit_data_postort_idx');
                $table->dropIndex('ratsit_data_kommun_idx');
                $table->dropIndex('ratsit_data_lan_idx');
            });

            return;
        }

        // Drop indexes if they exist
        $indexes = DB::select("
            SELECT indexname FROM pg_indexes
            WHERE tablename = 'ratsit_data' AND indexname IN (
                'ratsit_data_postnummer_idx',
                'ratsit_data_postort_idx',
                'ratsit_data_kommun_idx',
                'ratsit_data_lan_idx'
            )
        ");

        $existingIndexes = collect($indexes)->pluck('indexname')->all();

        if (in_array('ratsit_data_postnummer_idx', $existingIndexes)) {
            DB::statement('DROP INDEX IF EXISTS ratsit_data_postnummer_idx');
        }

        if (in_array('ratsit_data_postort_idx', $existingIndexes)) {
            DB::statement('DROP INDEX IF EXISTS ratsit_data_postort_idx');
        }

        if (in_array('ratsit_data_kommun_idx', $existingIndexes)) {
            DB::statement('DROP INDEX IF EXISTS ratsit_data_kommun_idx');
        }

        if (in_array('ratsit_data_lan_idx', $existingIndexes)) {
            DB::statement('DROP INDEX IF EXISTS ratsit_data_lan_idx');
        }
    }
};

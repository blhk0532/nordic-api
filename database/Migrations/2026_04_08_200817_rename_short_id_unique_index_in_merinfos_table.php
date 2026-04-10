<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER INDEX merinfos_short_id_unique RENAME TO merinfos_short_uuid_unique');
    }

    public function down(): void
    {
        DB::statement('ALTER INDEX merinfos_short_uuid_unique RENAME TO merinfos_short_id_unique');
    }
};

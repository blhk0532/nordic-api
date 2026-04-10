<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = [
        'sweden_adresser',
        'sweden_gator',
        'sweden_personer',
    ];

    private int $chunkSize = 250;

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            $this->addCoordinateColumns($tableName);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $columnsToDrop = [];

            if (Schema::hasColumn($tableName, 'latitude')) {
                $columnsToDrop[] = 'latitude';
            }

            if (Schema::hasColumn($tableName, 'longitude')) {
                $columnsToDrop[] = 'longitude';
            }

            if ($columnsToDrop === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    private function addCoordinateColumns(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (! Schema::hasColumn($tableName, 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sweden_postorter', function (Blueprint $table) {
            $table->unsignedBigInteger('sweden_kommuner_id')->nullable();
        });

        Schema::table('sweden_postnummer', function (Blueprint $table) {
            $table->unsignedBigInteger('sweden_postorter_id')->nullable();
            $table->unsignedBigInteger('sweden_kommuner_id')->nullable();
        });

        Schema::table('sweden_gator', function (Blueprint $table) {
            $table->unsignedBigInteger('sweden_postnummer_id')->nullable();
            $table->unsignedBigInteger('sweden_postorter_id')->nullable();
            $table->unsignedBigInteger('sweden_kommuner_id')->nullable();
        });

        Schema::table('sweden_adresser', function (Blueprint $table) {
            $table->unsignedBigInteger('sweden_gator_id')->nullable();
            $table->unsignedBigInteger('sweden_postnummer_id')->nullable();
            $table->unsignedBigInteger('sweden_postorter_id')->nullable();
            $table->unsignedBigInteger('sweden_kommuner_id')->nullable();
        });

        Schema::table('sweden_personer', function (Blueprint $table) {
            $table->unsignedBigInteger('sweden_adresser_id')->nullable();
            $table->unsignedBigInteger('sweden_gator_id')->nullable();
            $table->unsignedBigInteger('sweden_postnummer_id')->nullable();
            $table->unsignedBigInteger('sweden_postorter_id')->nullable();
            $table->unsignedBigInteger('sweden_kommuner_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sweden_personer', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE sweden_personer DROP CONSTRAINT IF EXISTS sweden_personer_sweden_adresser_id_foreign');
                DB::statement('ALTER TABLE sweden_personer DROP CONSTRAINT IF EXISTS sweden_personer_sweden_gator_id_foreign');
                DB::statement('ALTER TABLE sweden_personer DROP CONSTRAINT IF EXISTS sweden_personer_sweden_postnummer_id_foreign');
                DB::statement('ALTER TABLE sweden_personer DROP CONSTRAINT IF EXISTS sweden_personer_sweden_postorter_id_foreign');
                DB::statement('ALTER TABLE sweden_personer DROP CONSTRAINT IF EXISTS sweden_personer_sweden_kommuner_id_foreign');
            }
            $table->dropColumn([
                'sweden_adresser_id',
                'sweden_gator_id',
                'sweden_postnummer_id',
                'sweden_postorter_id',
                'sweden_kommuner_id',
            ]);
        });

        Schema::table('sweden_adresser', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE sweden_adresser DROP CONSTRAINT IF EXISTS sweden_adresser_sweden_gator_id_foreign');
                DB::statement('ALTER TABLE sweden_adresser DROP CONSTRAINT IF EXISTS sweden_adresser_sweden_postnummer_id_foreign');
                DB::statement('ALTER TABLE sweden_adresser DROP CONSTRAINT IF EXISTS sweden_adresser_sweden_postorter_id_foreign');
                DB::statement('ALTER TABLE sweden_adresser DROP CONSTRAINT IF EXISTS sweden_adresser_sweden_kommuner_id_foreign');
            }
            $table->dropColumn([
                'sweden_gator_id',
                'sweden_postnummer_id',
                'sweden_postorter_id',
                'sweden_kommuner_id',
            ]);
        });

        Schema::table('sweden_gator', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE sweden_gator DROP CONSTRAINT IF EXISTS sweden_gator_sweden_postnummer_id_foreign');
                DB::statement('ALTER TABLE sweden_gator DROP CONSTRAINT IF EXISTS sweden_gator_sweden_postorter_id_foreign');
                DB::statement('ALTER TABLE sweden_gator DROP CONSTRAINT IF EXISTS sweden_gator_sweden_kommuner_id_foreign');
            }
            $table->dropColumn([
                'sweden_postnummer_id',
                'sweden_postorter_id',
                'sweden_kommuner_id',
            ]);
        });

        Schema::table('sweden_postnummer', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE sweden_postnummer DROP CONSTRAINT IF EXISTS sweden_postnummer_sweden_postorter_id_foreign');
                DB::statement('ALTER TABLE sweden_postnummer DROP CONSTRAINT IF EXISTS sweden_postnummer_sweden_kommuner_id_foreign');
            }
            $table->dropColumn(['sweden_postorter_id', 'sweden_kommuner_id']);
        });

        Schema::table('sweden_postorter', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE sweden_postorter DROP CONSTRAINT IF EXISTS sweden_postorter_sweden_kommuner_id_foreign');
            }
            $table->dropColumn(['sweden_kommuner_id']);
        });
    }
};
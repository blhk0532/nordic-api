<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $constraintExists = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'booking_bookings' AND CONSTRAINT_NAME = 'booking_bookings_booking_client_id_foreign'");
        } else {
            $constraintExists = [];
        }

        if (empty($constraintExists)) {
            Schema::table('booking_bookings', function (Blueprint $table) {
                $table->foreign('booking_client_id')->references('id')->on('booking_clients')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('booking_bookings', function (Blueprint $table) {
            $table->dropForeign(['booking_client_id']);
        });
    }
};

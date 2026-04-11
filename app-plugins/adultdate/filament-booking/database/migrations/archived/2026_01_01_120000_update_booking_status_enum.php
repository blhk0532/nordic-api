<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support MODIFY or ENUM, so we'll skip this for now
        // In production with MySQL/PostgreSQL, this would work
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(<<<'SQL'
                ALTER TABLE booking_bookings
                MODIFY status ENUM('new','booked','confirmed','processing','cancelled','updated','complete')
                DEFAULT 'new'
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(<<<'SQL'
                ALTER TABLE booking_bookings
                MODIFY status ENUM('booked','confirmed','processing','cancelled','updated','complete')
                DEFAULT 'booked'
            SQL);
        }
    }
};

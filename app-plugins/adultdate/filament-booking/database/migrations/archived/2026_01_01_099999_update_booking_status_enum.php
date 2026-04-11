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
     *
     * @return void
     */
    public function up()
    {
        // First, change any existing 'new' status to 'booked' (default)
        DB::table('booking_bookings')->where('status', 'new')->update(['status' => 'booked']);

        // Then alter the enum to match the BookingStatus enum
        Schema::table('booking_bookings', function (Blueprint $table): void {
            $table->enum('status', ['booked', 'confirmed', 'processing', 'cancelled', 'updated', 'complete'])->default('booked')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to old enum values
        Schema::table('booking_bookings', function (Blueprint $table): void {
            $table->enum('status', ['new', 'processing', 'shipped', 'delivered', 'cancelled'])->default('new')->change();
        });

        // Change 'booked' back to 'new'
        DB::table('booking_bookings')->where('status', 'booked')->update(['status' => 'new']);
    }
};

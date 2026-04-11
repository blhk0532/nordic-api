<?php

declare(strict_types=1);

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
        Schema::table('booking_bookings', function (Blueprint $table) {
            $table->string('google_event_id')->nullable()->after('number');
            $table->unsignedBigInteger('booking_calendar_id')->nullable()->after('booking_location_id');

            $table->foreign('booking_calendar_id')->references('id')->on('booking_calendars')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_bookings', function (Blueprint $table) {
            $table->dropForeign(['booking_calendar_id']);
            $table->dropColumn(['google_event_id', 'booking_calendar_id']);
        });
    }
};

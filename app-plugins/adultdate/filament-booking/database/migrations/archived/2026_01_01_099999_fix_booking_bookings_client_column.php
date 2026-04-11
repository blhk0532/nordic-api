<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('booking_bookings', function (Blueprint $table): void {
            // Drop the incorrect customer_id column if it exists
            if (Schema::hasColumn('booking_bookings', 'booking_customer_id')) {
                $table->dropForeign(['booking_customer_id']);
                $table->dropColumn('booking_customer_id');
            }

            // Add the correct client_id column if it doesn't exist
            if (! Schema::hasColumn('booking_bookings', 'booking_client_id')) {
                $table->foreignId('booking_client_id')->nullable()->constrained('booking_clients')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_bookings', function (Blueprint $table): void {
            // Drop the client_id column if it exists
            if (Schema::hasColumn('booking_bookings', 'booking_client_id')) {
                $table->dropForeign(['booking_client_id']);
                $table->dropColumn('booking_client_id');
            }

            // Add back the customer_id column if it doesn't exist
            if (! Schema::hasColumn('booking_bookings', 'booking_customer_id')) {
                $table->foreignId('booking_customer_id')->nullable()->constrained()->nullOnDelete();
            }
        });
    }
};

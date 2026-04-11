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
            $table->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
            $table->foreignId('service_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('booking_user_id')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_bookings', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
            $table->dropForeign(['service_user_id']);
            $table->dropColumn('service_user_id');
            $table->dropForeign(['booking_user_id']);
            $table->dropColumn('booking_user_id');
        });
    }
};

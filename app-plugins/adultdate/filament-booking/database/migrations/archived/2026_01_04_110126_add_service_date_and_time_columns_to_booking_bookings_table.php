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
            $table->date('service_date')->nullable()->after('booking_client_id');
            $table->time('start_time')->nullable()->after('service_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->timestamp('starts_at')->nullable()->after('end_time');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->text('service_note')->nullable()->after('notes');
            $table->boolean('is_active')->default(true)->after('service_note');
            $table->timestamp('notified_at')->nullable()->after('is_active');
            $table->timestamp('confirmed_at')->nullable()->after('notified_at');
            $table->timestamp('completed_at')->nullable()->after('confirmed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'service_date',
                'start_time',
                'end_time',
                'starts_at',
                'ends_at',
                'service_note',
                'is_active',
                'notified_at',
                'confirmed_at',
                'completed_at',
            ]);
        });
    }
};

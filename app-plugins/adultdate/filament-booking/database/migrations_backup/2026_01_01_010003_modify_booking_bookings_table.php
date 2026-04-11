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
        Schema::table('booking_bookings', function (Blueprint $table): void {
            // Remove old columns
            $table->dropColumn(['shipping_price', 'shipping_method']);

            // Add new columns
            $table->foreignId('booking_location_id')->nullable()->after('booking_client_id')->constrained('booking_locations')->nullOnDelete();
            $table->date('service_date')->nullable()->after('booking_location_id');
            $table->time('start_time')->nullable()->after('service_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->text('service_note')->nullable()->after('notes');
            $table->boolean('is_active')->default(true)->after('service_note');
            $table->timestamp('notified_at')->nullable()->after('is_active');
            $table->timestamp('confirmed_at')->nullable()->after('notified_at');
            $table->timestamp('completed_at')->nullable()->after('confirmed_at');

            // Set default currency to SEK
            $table->string('currency')->default('SEK')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_bookings', function (Blueprint $table): void {
            // Remove new columns
            $table->dropForeign(['booking_location_id']);
            $table->dropColumn([
                'booking_location_id',
                'service_date',
                'start_time',
                'end_time',
                'service_note',
                'is_active',
                'notified_at',
                'confirmed_at',
                'completed_at',
            ]);

            // Add old columns back
            $table->decimal('shipping_price')->nullable();
            $table->string('shipping_method')->nullable();

            // Remove default from currency
            $table->string('currency')->default(null)->change();
        });
    }
};

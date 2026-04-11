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
        Schema::create('booking_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_location_id')->constrained('booking_locations')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_available')->default(true);
            $table->integer('max_bookings')->default(10);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Ensure one schedule per location per date
            $table->unique(['booking_location_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_schedules');
    }
};

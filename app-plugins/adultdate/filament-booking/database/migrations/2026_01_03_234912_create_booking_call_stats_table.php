<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_call_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lead_id')->nullable();
            $table->foreignId('booking_id')->nullable()->constrained('booking_bookings')->onDelete('set null');
            $table->enum('outcome', ['answered', 'voicemail', 'no_answer', 'busy', 'failed', 'other'])->default('no_answer');
            $table->integer('duration')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('booked_meeting')->default(false);
            $table->timestamp('call_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_call_stats');
    }
};

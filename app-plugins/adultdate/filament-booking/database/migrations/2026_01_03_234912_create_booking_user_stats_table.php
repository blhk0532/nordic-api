<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('stats_date');
            $table->integer('total_calls')->default(0);
            $table->integer('answered_calls')->default(0);
            $table->integer('voicemail_calls')->default(0);
            $table->integer('no_answer_calls')->default(0);
            $table->integer('busy_calls')->default(0);
            $table->integer('failed_calls')->default(0);
            $table->integer('other_calls')->default(0);
            $table->integer('booked_meetings_count')->default(0);
            $table->integer('total_duration')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'stats_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_user_stats');
    }
};

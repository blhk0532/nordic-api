<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_daily_locations', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->foreignId('service_user_id')->constrained('users')->onDelete('cascade');
            $table->string('location')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['date', 'service_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_daily_locations');
    }
};

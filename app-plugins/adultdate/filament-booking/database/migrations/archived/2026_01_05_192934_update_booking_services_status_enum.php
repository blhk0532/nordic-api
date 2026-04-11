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
        Schema::table('booking_services', function (Blueprint $table) {
            // Modify the enum to include 'confirmed'
            $table->enum('status', ['booked', 'confirmed', 'processing', 'cancelled', 'updated', 'complete'])->default('processing')->change();
        });
    }

    public function down(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            // Revert to the original enum values
            $table->enum('status', ['booked', 'changed', 'processing', 'cancelled', 'updated', 'complete'])->default('processing')->change();
        });
    }
};

<?php

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
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();            $table->foreignId('booking_brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->string('service_code')->unique()->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->integer('time_duration')->default(60); // Duration in minutes
            $table->enum('status', ['booked', 'changed', 'processing', 'cancelled', 'updated', 'complete'])->default('processing');
            $table->boolean('featured')->default(false);
            $table->boolean('is_visible')->default(false);
            $table->date('published_at')->nullable();
            $table->string('seo_title', 60)->nullable();
            $table->string('seo_description', 160)->nullable();            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};

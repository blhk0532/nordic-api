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
        if (! Schema::hasTable('booking_bookings')) {
            Schema::create('booking_bookings', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('sort')->default(0);

                // Client (replaces booking_customer_id)
                $table->foreignId('booking_client_id')->nullable()->constrained('booking_clients')->nullOnDelete();

                // Booking identifiers and pricing
                $table->string('number', 32)->unique();
                $table->decimal('total_price', 12, 2)->nullable();

                // Status (final set of values)
                $table->enum('status', ['booked', 'confirmed', 'processing', 'cancelled', 'updated', 'complete'])->default('booked');

                $table->string('currency');
                $table->decimal('shipping_price')->nullable();
                $table->string('shipping_method')->nullable();

                // Notes and service-specific fields
                $table->text('notes')->nullable();
                $table->text('service_note')->nullable();

                // Polymorphic schedulable (optional)
                $table->string('schedulable_type')->nullable();
                $table->unsignedBigInteger('schedulable_id')->nullable();

                // Relations introduced by modifier migrations
                $table->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
                $table->foreignId('service_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('booking_user_id')->nullable()->constrained('users')->nullOnDelete();

                // Service scheduling fields
                $table->date('service_date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();

                // Activity & timestamps
                $table->boolean('is_active')->default(true);
                $table->timestamp('notified_at')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('completed_at')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_bookings');
    }
};

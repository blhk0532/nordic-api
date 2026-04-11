<?php

use Adultdate\FilamentBooking\Models\Booking\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('booking_reviews');

        Schema::create('booking_comments', function (Blueprint $table): void {
            $table->id();

            $table->foreignIdFor(Customer::class)->nullable()->constrained('booking_customers')->cascadeOnDelete();
            $table->morphs('commentable');
            $table->text('title')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_visible')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_comments');
    }
};

<?php

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
        Schema::create('booking_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_order_items');
    }
};

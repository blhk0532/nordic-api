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
        if (! Schema::hasTable('booking_order_items')) {
            Schema::create('booking_order_items', function (Blueprint $table): void {
                $table->id();
                $table->integer('sort')->default(0);
                $table->foreignId('booking_order_id')->nullable()->constrained('booking_orders')->cascadeOnDelete();
                $table->foreignId('booking_product_id')->nullable()->constrained('booking_products')->cascadeOnDelete();
                $table->integer('qty');
                $table->decimal('unit_price', 10, 2);
                $table->timestamps();
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
        Schema::dropIfExists('booking_order_items');
    }
};

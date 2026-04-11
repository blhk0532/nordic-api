<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $orderConstraintExists = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'booking_order_items' AND CONSTRAINT_NAME = 'booking_order_items_booking_order_id_foreign'");
            $productConstraintExists = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'booking_order_items' AND CONSTRAINT_NAME = 'booking_order_items_booking_product_id_foreign'");
        } else {
            $orderConstraintExists = [];
            $productConstraintExists = [];
        }

        Schema::table('booking_order_items', function (Blueprint $table) use ($orderConstraintExists, $productConstraintExists) {
            if (empty($orderConstraintExists)) {
                $table->foreign('booking_order_id')->references('id')->on('booking_orders')->onDelete('cascade');
            }
            if (empty($productConstraintExists)) {
                $table->foreign('booking_product_id')->references('id')->on('booking_products')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_order_items', function (Blueprint $table) {
            $table->dropForeign(['booking_order_id']);
            $table->dropForeign(['booking_product_id']);
        });
    }
};

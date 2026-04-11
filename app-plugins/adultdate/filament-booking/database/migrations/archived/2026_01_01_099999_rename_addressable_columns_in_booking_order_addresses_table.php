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
        Schema::table('booking_order_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('booking_order_addresses', 'addressable_id')) {
                $table->renameColumn('addressable_id', 'booking_addressable_id');
            }
            if (Schema::hasColumn('booking_order_addresses', 'addressable_type')) {
                $table->renameColumn('addressable_type', 'booking_addressable_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_order_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('booking_order_addresses', 'booking_addressable_id')) {
                $table->renameColumn('booking_addressable_id', 'addressable_id');
            }
            if (Schema::hasColumn('booking_order_addresses', 'booking_addressable_type')) {
                $table->renameColumn('booking_addressable_type', 'addressable_type');
            }
        });
    }
};

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
        $hasTable = Schema::hasTable('booking_order_addresses');
        if (! $hasTable) {
            Schema::create('booking_order_addresses', function (Blueprint $table): void {
                $table->id();
                $table->morphs('booking_addressable', 'bk_addr_type_id');
                $table->string('country')->nullable();
                $table->string('street')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('zip')->nullable();
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
        Schema::dropIfExists('booking_order_addresses');
    }
};

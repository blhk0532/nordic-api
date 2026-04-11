<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('booking_addressables');
        Schema::create('booking_addressables', function (Blueprint $table): void {
            $table->foreignId('address_id');
            $table->morphs('booking_addressable', indexName: 'bk_addrable_type_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_addressables');
    }
};

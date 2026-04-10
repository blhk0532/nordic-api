<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sweden_adresser', function (Blueprint $table) {
            $table->id();
            $table->string('adress')->nullable()->index();
            $table->string('postnummer')->nullable()->index();
            $table->string('postort')->nullable()->index();
            $table->string('kommun')->nullable()->index();
            $table->string('lan')->nullable()->index();
            $table->integer('personer')->nullable();
            $table->integer('företag')->nullable();
            $table->integer('adresser')->nullable();
            $table->string('ratsit_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_queue')->default(false);
            $table->boolean('is_done')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sweden_adresser');
    }
};

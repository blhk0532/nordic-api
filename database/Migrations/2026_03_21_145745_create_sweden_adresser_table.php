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
        Schema::create('sweden_adresser', function (Blueprint $table) {
            $table->id();
            $table->string('adress')->nullable();
            $table->string('postnummer')->nullable();
            $table->string('postort')->nullable();
            $table->string('kommun')->nullable();
            $table->string('lan')->nullable();
            $table->unsignedInteger('personer')->nullable()->default(null);
            $table->unsignedInteger('företag')->nullable()->default(null);
            $table->unsignedInteger('adresser')->nullable()->default(null);
            $table->string('ratsit_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_queue')->default(false);
            $table->boolean('is_done')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sweden_adresser');
    }
};

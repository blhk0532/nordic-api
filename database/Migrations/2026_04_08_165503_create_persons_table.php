<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // New table
        Schema::create('persons', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('adress')->index();
            $table->string('zip')->index();
            $table->string('city')->index();
            $table->string('kommun')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('dob')->nullable();
            $table->string('phone')->nullable();
            $table->json('ratsit')->nullable();
            $table->json('hitta')->nullable();
            $table->json('merinfo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};

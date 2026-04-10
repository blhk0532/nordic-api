<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->nullable();
            $table->string('street')->index();
            $table->string('zip')->index();
            $table->string('city')->index();
            $table->string('kommun')->nullable()->index();
            $table->string('phone')->nullable();
            $table->ulid('merinfo_id')->nullable()->unique();
            $table->string('merinfo_phone')->nullable();
            $table->string('personal_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'street', 'zip', 'city'], 'persons_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};

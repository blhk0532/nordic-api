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
            $table->string('street');
            $table->string('zip');
            $table->string('city');
            $table->string('kommun')->nullable()->index();
            $table->string('phone')->nullable();
            $table->ulid('merinfo_id')->nullable()->unique();
            $table->string('merinfo_phone')->nullable();
            $table->string('personal_number')->nullable();
            $table->string('gender')->nullable();
            $table->boolean('merinfo_is_house')->nullable();
            $table->ulid('team_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['name', 'street', 'zip', 'city']);
            $table->index('street');
            $table->index('city');
            $table->index('zip');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};

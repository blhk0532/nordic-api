<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merinfos', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('type', 255)->default('person');
            $table->string('short_uuid', 255)->unique();
            $table->string('name', 255);
            $table->string('givenNameOrFirstName', 255);
            $table->string('personalNumber', 255)->nullable();
            $table->json('pnr')->default('[]');
            $table->json('address')->default('[]');
            $table->string('gender', 255)->nullable();
            $table->boolean('is_celebrity')->default(false);
            $table->boolean('has_company_engagement')->default(false);
            $table->integer('number_plus_count')->default(0);
            $table->json('phone_number')->default('[]');
            $table->string('url', 255)->nullable();
            $table->string('same_address_url', 255)->nullable();
            $table->boolean('is_house')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('givenNameOrFirstName');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merinfos');
    }
};

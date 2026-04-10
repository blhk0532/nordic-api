<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // New table
        Schema::create('merinfos', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('person');
            $table->string('short_id')->unique();
            $table->string('name')->index();
            $table->string('givenNameOrFirstName')->index();
            $table->string('personalNumber')->nullable();
            $table->json('pnr')->default('[]');
            $table->json('address')->default('[]');
            $table->string('gender')->nullable();
            $table->boolean('is_celebrity')->default(0);
            $table->boolean('has_company_engagement')->default(0);
            $table->integer('number_plus_count')->default(0);
            $table->json('phone_number')->default('[]');
            $table->string('url')->nullable();
            $table->string('same_address_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merinfos');
    }
};

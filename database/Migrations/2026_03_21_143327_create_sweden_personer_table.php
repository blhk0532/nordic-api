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
        Schema::create('sweden_personer', function (Blueprint $table) {
            $table->id();
            $table->string('adress')->nullable();
            $table->string('postnummer')->nullable();
            $table->string('postort')->nullable();
            $table->string('fornamn')->nullable();
            $table->string('efternamn')->nullable();
            $table->string('personnamn')->nullable();
            $table->string('personnummer')->nullable();
            $table->string('kon')->nullable();
            $table->string('telefon')->nullable();
            $table->json('telefonnummer')->nullable();
            $table->string('civilstand')->nullable();
            $table->string('adressandring')->nullable();
            $table->string('bostadstyp')->nullable();
            $table->string('agandeform')->nullable();
            $table->string('boarea')->nullable();
            $table->string('byggar')->nullable();
            $table->unsignedInteger('personer')->nullable()->default(null);
            $table->string('ratsit_link')->nullable();
            $table->json('ratsit_data')->nullable();
            $table->string('hitta_link')->nullable();
            $table->json('hitta_data')->nullable();
            $table->string('merinfo_link')->nullable();
            $table->json('merinfo_data')->nullable();
            $table->string('eniro_link')->nullable();
            $table->json('eniro_data')->nullable();
            $table->string('upplysning_link')->nullable();
            $table->json('upplysning_data')->nullable();
            $table->string('mrkoll_link')->nullable();
            $table->json('mrkoll_data')->nullable();
            $table->boolean('is_hus')->default(false);
            $table->boolean('is_owner')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['adress', 'fornamn', 'efternamn'], 'sweden_personer_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sweden_personer');
    }
};

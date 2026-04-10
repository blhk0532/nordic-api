<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merinfo_data', function (Blueprint $table) {
            $table->id();

            $table->string('personnamn', 255)->nullable();
            $table->unsignedTinyInteger('alder')->nullable(); // age is numeric
            $table->string('kon', 20)->nullable();

            $table->string('gatuadress', 255)->nullable();
            $table->string('postnummer', 20)->nullable();
            $table->string('postort', 100)->nullable();

            $table->json('telefon')->nullable();
            $table->json('telefonnummer')->nullable();
            $table->json('telefoner')->nullable();

            $table->text('karta')->nullable();
            $table->text('link')->nullable();
            $table->string('bostadstyp', 100)->nullable();
            $table->string('bostadspris', 50)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_telefon')->default(false);
            $table->boolean('is_ratsit')->default(false);
            $table->boolean('is_hus')->default(false);

            $table->integer('merinfo_personer_total')->nullable();
            $table->integer('merinfo_foretag_total')->nullable();
            $table->integer('merinfo_personer_saved')->nullable();
            $table->integer('merinfo_foretag_saved')->nullable();
            $table->integer('merinfo_personer_phone_total')->nullable();
            $table->integer('merinfo_foretag_phone_total')->nullable();
            $table->integer('merinfo_personer_phone_saved')->nullable();
            $table->integer('merinfo_foretag_phone_saved')->nullable();
            $table->integer('merinfo_personer_house_saved')->nullable();
            $table->integer('merinfo_foretag_house_saved')->nullable();
            $table->integer('merinfo_personer_count')->nullable();
            $table->integer('merinfo_personer_queue')->nullable();

            $table->timestamps();

            $table->index('personnamn');
            $table->index('gatuadress');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('merinfo_data');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sweden_postorter', function (Blueprint $table) {
            $table->id();
            $table->string('postort')->nullable();
            $table->string('kommun')->nullable();
            $table->string('lan')->nullable();
            $table->unsignedInteger('personer')->nullable();
            $table->unsignedInteger('foretag')->nullable();
            $table->unsignedInteger('postnummer')->nullable();
            $table->unsignedInteger('gator')->nullable();
            $table->unsignedInteger('adresser')->nullable();
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
        Schema::dropIfExists('sweden_postorter');
    }
};

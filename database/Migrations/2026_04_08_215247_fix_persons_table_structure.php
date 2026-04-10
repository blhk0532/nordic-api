<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn(['merinfo', 'ratsit', 'hitta']);
            $table->renameColumn('adress', 'street');
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->renameColumn('street', 'adress');
            $table->json('ratsit')->nullable();
            $table->json('hitta')->nullable();
            $table->json('merinfo')->nullable();
        });
    }
};

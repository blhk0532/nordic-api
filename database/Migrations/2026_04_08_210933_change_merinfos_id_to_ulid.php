<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merinfos', function (Blueprint $table) {
            $table->dropPrimary();
            $table->ulid('id')->change();
            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::table('merinfos', function (Blueprint $table) {
            $table->dropPrimary();
            $table->bigInteger('id')->change()->autoIncrement();
            $table->primary('id');
        });
    }
};

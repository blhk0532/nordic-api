<?php

declare(strict_types=1);

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
        Schema::table('sweden_personer', function (Blueprint $table) {
            $table->integer('alder')->nullable()->after('personnamn');
            $table->string('kommun')->nullable()->after('alder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sweden_personer', function (Blueprint $table) {
            $table->dropColumn(['alder', 'kommun']);
        });
    }
};

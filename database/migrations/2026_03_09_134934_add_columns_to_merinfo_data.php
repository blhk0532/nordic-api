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
        Schema::table('merinfo_data', function (Blueprint $table) {
            $table->string('givenNameOrFirstName')->nullable()->after('personnamn');
            //    $table->string('personalNumber')->nullable()->after('givenNameOrFirstName');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merinfo_data', function (Blueprint $table) {
            //
        });
    }
};

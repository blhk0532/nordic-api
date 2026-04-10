<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merinfos', function (Blueprint $table) {
            $table->boolean('is_house')->nullable()->after('has_company_engagement');
        });
    }

    public function down(): void
    {
        Schema::table('merinfos', function (Blueprint $table) {
            $table->dropColumn('is_house');
        });
    }
};

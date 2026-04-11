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
        Schema::table('calendar_settings', function (Blueprint $table) {
            $table->string('confirmation_sms_number')->nullable();
            $table->string('confirmation_email_address')->nullable();
            $table->string('telavox_jwt')->nullable();
            $table->string('calendar_timezone')->default('Europe/Stockholm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_settings', function (Blueprint $table) {
            $table->dropColumn(['confirmation_sms_number', 'confirmation_email_address', 'telavox_jwt', 'calendar_timezone']);
        });
    }
};

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
            if (! Schema::hasColumn('calendar_settings', 'confirmation_sms')) {
                $table->text('confirmation_sms')->nullable();
            }

            if (! Schema::hasColumn('calendar_settings', 'confirmation_email')) {
                $table->text('confirmation_email')->nullable();
            }

            if (! Schema::hasColumn('calendar_settings', 'calendar_weekends')) {
                $table->boolean('calendar_weekends')->default(false);
            }

            if (! Schema::hasColumn('calendar_settings', 'calendar_theme')) {
                $table->string('calendar_theme')->default('standard');
            }

            if (! Schema::hasColumn('calendar_settings', 'confirmation_sms_number')) {
                $table->string('confirmation_sms_number')->nullable();
            }

            if (! Schema::hasColumn('calendar_settings', 'confirmation_email_address')) {
                $table->string('confirmation_email_address')->nullable();
            }

            if (! Schema::hasColumn('calendar_settings', 'telavox_jwt')) {
                $table->string('telavox_jwt')->nullable();
            }

            if (! Schema::hasColumn('calendar_settings', 'calendar_timezone')) {
                $table->string('calendar_timezone')->default('Europe/Stockholm');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_settings', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('calendar_settings', 'confirmation_sms')) {
                $columns[] = 'confirmation_sms';
            }
            if (Schema::hasColumn('calendar_settings', 'confirmation_email')) {
                $columns[] = 'confirmation_email';
            }
            if (Schema::hasColumn('calendar_settings', 'calendar_weekends')) {
                $columns[] = 'calendar_weekends';
            }
            if (Schema::hasColumn('calendar_settings', 'calendar_theme')) {
                $columns[] = 'calendar_theme';
            }
            if (Schema::hasColumn('calendar_settings', 'confirmation_sms_number')) {
                $columns[] = 'confirmation_sms_number';
            }
            if (Schema::hasColumn('calendar_settings', 'confirmation_email_address')) {
                $columns[] = 'confirmation_email_address';
            }
            if (Schema::hasColumn('calendar_settings', 'telavox_jwt')) {
                $columns[] = 'telavox_jwt';
            }
            if (Schema::hasColumn('calendar_settings', 'calendar_timezone')) {
                $columns[] = 'calendar_timezone';
            }

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

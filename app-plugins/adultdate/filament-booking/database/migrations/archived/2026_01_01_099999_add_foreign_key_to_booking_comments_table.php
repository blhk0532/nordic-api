<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $constraintExists = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'booking_comments' AND CONSTRAINT_NAME = 'booking_comments_customer_id_foreign'");

            if (empty($constraintExists)) {
                Schema::table('booking_comments', function (Blueprint $table) {
                    $table->foreign('customer_id')->references('id')->on('booking_customers')->onDelete('cascade');
                });
            }
        } elseif ($driver === 'sqlite') {
            // For SQLite, assume the foreign key doesn't exist and add it
            Schema::table('booking_comments', function (Blueprint $table) {
                $table->foreign('customer_id')->references('id')->on('booking_customers')->onDelete('cascade');
            });
        } else {
            // For other databases, you might need to add specific checks
            Schema::table('booking_comments', function (Blueprint $table) {
                $table->foreign('customer_id')->references('id')->on('booking_customers')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('booking_comments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });
    }
};

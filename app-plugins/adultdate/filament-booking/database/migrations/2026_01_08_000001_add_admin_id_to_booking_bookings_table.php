<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_bookings', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete()->after('booking_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('booking_bookings', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });
    }
};

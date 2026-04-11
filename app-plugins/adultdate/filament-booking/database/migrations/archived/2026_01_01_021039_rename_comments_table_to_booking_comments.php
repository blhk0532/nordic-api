<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('comments')) {
            Schema::rename('comments', 'booking_comments');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_comments')) {
            Schema::rename('booking_comments', 'comments');
        }
    }
};

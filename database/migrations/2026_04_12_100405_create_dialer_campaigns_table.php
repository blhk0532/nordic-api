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
        Schema::create('dialer_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('status')->default('draft')->index();
            $table->string('source_channel')->default('SIP/1001');
            $table->string('context')->default('default');
            $table->string('caller_id')->nullable();
            $table->unsignedSmallInteger('max_concurrent_calls')->default(1);
            $table->unsignedSmallInteger('max_attempts')->default(1);
            $table->unsignedSmallInteger('retry_delay_seconds')->default(30);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialer_campaigns');
    }
};

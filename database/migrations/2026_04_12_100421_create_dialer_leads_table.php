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
        Schema::create('dialer_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dialer_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number');
            $table->string('name')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedSmallInteger('priority')->default(0)->index();
            $table->unsignedSmallInteger('attempts_count')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->string('last_disposition')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['dialer_campaign_id', 'status']);
            $table->unique(['dialer_campaign_id', 'phone_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialer_leads');
    }
};

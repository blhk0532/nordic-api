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
        Schema::create('dialer_call_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dialer_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dialer_lead_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('queued')->index();
            $table->string('ami_action_id')->nullable()->index();
            $table->string('ami_unique_id')->nullable()->index();
            $table->string('ami_linked_id')->nullable()->index();
            $table->string('channel')->nullable()->index();
            $table->string('destination')->nullable();
            $table->string('disposition')->nullable();
            $table->string('hangup_cause')->nullable();
            $table->json('raw_event')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['dialer_campaign_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialer_call_attempts');
    }
};

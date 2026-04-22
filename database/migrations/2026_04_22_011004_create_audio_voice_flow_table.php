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
        Schema::create('audio_voice_flow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('filename');
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft, approved, active, archived
            $table->integer('priority')->default(0);
            $table->json('tags')->nullable();
            $table->unsignedInteger('duration')->nullable(); // seconds
            $table->unsignedInteger('play_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for query performance
            $table->index('status');
            $table->index('priority');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_voice_flow');
    }
};

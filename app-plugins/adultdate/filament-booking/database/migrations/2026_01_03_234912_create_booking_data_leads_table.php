<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_data_leads', function (Blueprint $table) {
            $table->id();
            $table->string('luid')->unique();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->date('dob')->nullable();
            $table->integer('age')->nullable();
            $table->enum('sex', ['male', 'female', 'other'])->nullable();
            $table->enum('status', ['new', 'contacted', 'interested', 'not_interested', 'converted', 'do_not_call'])->default('new');
            $table->boolean('is_active')->default(true);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('attempt_count')->default(0);
            $table->timestamp('last_contacted_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_data_leads');
    }
};

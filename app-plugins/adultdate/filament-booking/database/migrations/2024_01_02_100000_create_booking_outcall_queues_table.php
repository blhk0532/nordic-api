<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_outcall_queues', function (Blueprint $table): void {
            $table->id();
            $table->string('luid')->nullable();
            $table->string('name')->nullable();
            $table->text('address')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('maps')->nullable();
            $table->integer('age')->nullable();
            $table->string('sex')->nullable();
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->text('notes')->nullable();
            $table->string('result')->nullable();
            $table->integer('attempts')->default(0);

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('booking_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_outcall_queues');
    }
};

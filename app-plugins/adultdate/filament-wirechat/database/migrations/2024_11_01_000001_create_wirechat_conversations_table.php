<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*** Run the migrations */
    public function up(): void
    {
        $tablePrefix = config('filament-wirechat.table_prefix', 'wirechat_');
        $usesUuid = config('filament-wirechat.uses_uuid_for_conversations', false);

        Schema::create($tablePrefix.'conversations', function (Blueprint $table) use ($usesUuid) {
            if ($usesUuid) {
                $table->uuid('id')->primary();
            } else {
                $table->id();
            }
            $table->string('type')->comment('Private is 1-1 , group or channel');
            $table->timestamp('disappearing_started_at')->nullable();
            $table->integer('disappearing_duration')->nullable();
            $table->index('type');
            $table->timestamps();
        });
    }

    /*** Reverse the migrations */
    public function down(): void
    {
        $tablePrefix = config('filament-wirechat.table_prefix', 'wirechat_');
        Schema::dropIfExists($tablePrefix.'conversations');
    }
};

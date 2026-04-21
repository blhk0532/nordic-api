<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filament_toggle_table_group_action_table', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};

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
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('app_id');
            $table->bigInteger('room_id')->unique();
            $table->string('source');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar_url')->nullable();
            $table->boolean('is_new_session')->default(true);
            $table->boolean('is_resolved')->default(false);
            $table->string('latest_service')->nullable();
            $table->string('extras')->nullable();
            $table->string('candidate_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};

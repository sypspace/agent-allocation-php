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
        Schema::create('room_queues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('room_id');
            $table->string('agent_id')->nullable();
            $table->enum('status', ['queued', 'served', 'resolved'])->default('queued');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_queues');
    }
};

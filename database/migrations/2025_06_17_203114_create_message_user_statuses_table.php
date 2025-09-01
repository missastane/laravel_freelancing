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
        Schema::create('message_user_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('status')->default(1)->comment('1 => sent, 2 => delivered, 3 => seen');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_user_statuses');
    }
};

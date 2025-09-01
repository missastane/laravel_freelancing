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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('message')->nullable()->comment('message maybe has only file(s)');
            $table->tinyInteger('message_type')->default(1)->comment('1 => text, 2 => multimedia, 3 => mixed');
            $table->timestamp('sent_date');
            $table->foreignId('parent_id')->nullable()->constrained('messages')->cascadeOnUpdate()->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

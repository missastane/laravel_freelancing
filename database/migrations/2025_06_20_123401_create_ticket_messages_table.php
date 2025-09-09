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
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('message');
            $table->foreignId('parent_id')->nullable()->constrained('ticket_messages')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('author_type')->default(1)->comment('1 => employer, 2 => freelancer, 3 => admin');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};

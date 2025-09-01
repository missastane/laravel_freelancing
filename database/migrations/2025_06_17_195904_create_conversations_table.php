<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('conversation_context')->comment('for example: App\\Models\\Proposal witch means before order or in progress of order App\\Models\\Order');
            $table->bigInteger('conversation_context_id')->comment('value of proposal_id or order_id');
            $table->tinyInteger('status')->default(1)->comment('1=> open, 2 => close');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};

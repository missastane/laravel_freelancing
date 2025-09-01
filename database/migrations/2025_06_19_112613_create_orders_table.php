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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employer_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('status')->default(1)->comment('1 => pending, 2 => in progress, 3 => completed, 4 => canceled');
            $table->decimal('total_price',20,3);
            $table->timestamp('due_date');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

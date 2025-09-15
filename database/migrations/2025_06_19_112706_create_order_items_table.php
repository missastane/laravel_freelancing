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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('proposal_milestone_id')->constrained('proposal_milestones')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('status')->default(1)->comment('1 => pending, 2 => in progress, 3 => completed 4 => approved, 5 => rejected');
            $table->tinyInteger('locked_by')->nullable()->comment('	1 => employer, 2 => freelancer, 3 => admin');
            $table->tinyInteger('locked_reason')->nullable()->comment('1 => not answer, 2 => insult, 3 => poor quality, 4 => other');
            $table->text('locked_note')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->decimal('price',20,3);
            $table->decimal('freelancer_amount',20,3);
            $table->decimal('platform_fee',20,3);
            $table->timestamp('due_date')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

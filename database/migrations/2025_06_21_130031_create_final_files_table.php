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
        Schema::create('final_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('status')->default(1)->comment('1 => pending, 2 => approved, 3 => revision, 4 => rejected');
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('employer_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('revision_at')->nullable();
            $table->text('revision_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejected_note')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_files');
    }
};

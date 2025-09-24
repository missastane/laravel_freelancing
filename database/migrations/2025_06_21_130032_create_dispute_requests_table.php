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
        Schema::create('dispute_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('final_file_id')->nullable()->constrained('final_files')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('user_type')->default(1)->comment('1 => employer, 2 => freelancer');
            $table->foreignId('raised_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('reason');
            $table->tinyInteger('status')->default(1)->comment('1 => pending, 2 => resloved, 3 => rejected');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispute_requests');
    }
};

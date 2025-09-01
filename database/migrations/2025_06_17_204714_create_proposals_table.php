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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('description');
            $table->decimal('total_amount',20,3);
            $table->integer('total_duration_time')->comment('per day');
            $table->tinyInteger('status')->default(1)->comment('1 => pending, 2 => approved, 3 => rejected, 4 => withdrawn');
            $table->timestamp('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};

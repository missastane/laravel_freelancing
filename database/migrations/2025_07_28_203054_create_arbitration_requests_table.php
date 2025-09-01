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
        Schema::create('arbitration_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_request_id')->constrained('dispute_requests')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('status')->default(1)->comment('1 => pending, 2 => For the benefit of the employer(cancel), 3 => For the benefit of the freelancer(approve delivery), 4 => Money distribution, 5 => without change');
            $table->decimal('freelancer_percent')->nullable();
            $table->decimal('employer_percent')->nullable();
            $table->text('description');
            $table->foreignId('resolved_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('resolved_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arbitration_requests');
    }
};

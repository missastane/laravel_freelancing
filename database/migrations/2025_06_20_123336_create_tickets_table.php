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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('priority_id')->constrained('ticket_priorities')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('ticket_departments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('dispute_request_id')->nullable()->constrained('dispute_requests')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('ticket_type')->default(1)->comment('1 => support, 2 => report, 3 => financial, 4 => complain');
            $table->string('subject');
            $table->tinyInteger('status')->default(1)->comment('1 => open, 2 => answered, 3 => closed');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

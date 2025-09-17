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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('account_number_sheba');
            $table->string('card_number');
            $table->string('bank_name');
            $table->decimal('amount',20,3);
            $table->timestamp('paid_at')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=> pending, 2 => accepted, 3 => rejected');
            $table->text('rejected_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};

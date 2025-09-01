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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('amount',20,3);
            $table->tinyInteger('transaction_type')->default(1)->comment('1 => increase: charge, 2 => decrease: transfer money from wallet to freelancer account, 3 => hold: order amount will be reserved until it is completed, 4 => release: release hold money by employer to freelancer wallet, 5 => refund: when the money is returened to the employer wallet, 6 => commission: when site fees are automatically withdrown');
            $table->text('description')->nullable();
            $table->string('related_type');
            $table->bigInteger('related_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};

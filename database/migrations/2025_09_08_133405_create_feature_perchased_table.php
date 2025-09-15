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
        Schema::create('feature_perchased', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_type_id')->constrained('feature_types')->cascadeOnDelete();
            $table->unsignedBigInteger('target_id');
            $table->enum('target_type', ['project', 'proposal']);
            $table->timestamp('purchased_at')->nullable();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->timestamp('expired_at')->nullable();
            $table->unique(['feature_type_id', 'target_id', 'target_type'], 'uniq_feature_target');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_perchased');
    }
};

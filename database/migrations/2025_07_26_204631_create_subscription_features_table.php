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
        Schema::create('subscription_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('feature_key');
            $table->string('feature_persian_key');
            $table->string('feature_value');
            $table->string('feature_value_type');
            $table->tinyInteger('is_limited')->default(2)->comment('1 => yes, 2 => no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_features');
    }
};

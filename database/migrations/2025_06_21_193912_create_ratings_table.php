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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('ratable_type');
            $table->bigInteger('ratable_id');
            $table->decimal('value', 2, 1);
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};

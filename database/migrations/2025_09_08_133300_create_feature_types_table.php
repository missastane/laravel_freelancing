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
        Schema::create('feature_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // internal name e.g. urgent, highlight
            $table->string('display_name', 150); // UI name
            $table->text('description')->nullable();
            $table->enum('target_type', ['project', 'proposal']);
            $table->decimal('price', 10, 2);
            $table->integer('duration_days')->nullable();
            $table->tinyInteger('is_active')->default(1)->comment('1 => active, 2 => diactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_types');
    }
};

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
        Schema::create('project_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('image');
            $table->foreignId('parent_id')->nullable()->constrained('project_categories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('status')->default(1)->comment('1 => active, 2 => disactive');
            $table->tinyInteger('show_in_menu')->default(1)->comment('1 => yes, 2 => no');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_categories');
    }
};

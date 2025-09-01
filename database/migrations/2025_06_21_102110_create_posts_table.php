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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('post_categories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('summary');
            $table->text('content');
            $table->text('image')->nullable();
            $table->string('study_time');
            $table->tinyInteger('status')->default(1)->comment('1 => active, 2 => disactive');
            $table->json('related_posts')->nullable();
            $table->bigInteger('view')->default(0);
            $table->timestamp('published_at')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

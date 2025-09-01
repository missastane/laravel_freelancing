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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('comment');
            $table->string('commentable_type');
            $table->bigInteger('commentable_id');
            $table->tinyInteger('seen')->default(2)->comment('1 => seen, 2 => unseen');
            $table->tinyInteger('approved')->default(2)->comment('1 => approved, 2 => not approved');
            $table->tinyInteger('status')->default(2)->comment('1 => active, 2 => inactive');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};

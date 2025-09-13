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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('filable_type');
            $table->bigInteger('filable_id');
            $table->string('file_name');
            $table->text('file_path');
            $table->string('mime_type');
            $table->string('file_type');
            $table->bigInteger('file_size');
            $table->tinyInteger('is_final_delivery')->default(2)->comment('1 => yes, 2 => no');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};

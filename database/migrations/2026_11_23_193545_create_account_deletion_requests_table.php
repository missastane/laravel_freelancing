<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_deletion_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->tinyInteger('status')
                ->default(1)
                ->comment('1 => pending, 2 => cancelled, 3 => completed, 4 => failed');
            $table->text('reason')->nullable();

            $table->timestamp('scheduled_for');
            $table->timestamp('processed_at')->nullable();

            $table->text('failure_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'scheduled_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_deletion_requests');
    }
};

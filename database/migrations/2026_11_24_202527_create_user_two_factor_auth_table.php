<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_two_factor_auth', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // Encrypted TOTP secret.
            $table->text('secret');

            // Null means 2FA setup has not been confirmed yet.
            $table->timestamp('confirmed_at')->nullable();

            // Expiration of an unfinished 2FA setup.
            $table->timestamp('setup_expires_at')->nullable();

            $table->unsignedBigInteger('last_used_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_two_factor_auth');
    }
};

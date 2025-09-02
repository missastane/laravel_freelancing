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
        Schema::create('subscription_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_subscription_id')->nullable()->constrained('user_subscriptions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('target_create_count')->default(0);
            $table->integer('send_notification_count')->default(0);
            $table->integer('send_email_count')->default(0);
            $table->integer('send_sms_count')->default(0);
            $table->integer('view_details_count')->default(0);
            $table->timestamp('period_start');
            $table->timestamp('period_end')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_default_usages');
    }
};

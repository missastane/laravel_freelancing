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
        Schema::create('subscription_default_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('target_type');
            $table->integer('max_target_per_month');
            $table->integer('max_notification_per_month');
            $table->integer('max_email_per_month');
            $table->integer('max_sms_per_month');
            $table->integer('max_view_deatils_per_month');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_default_features');
    }
};

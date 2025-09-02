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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('amount', 20, 3);
            $table->bigInteger('duration_days');
            $table->integer('commission_rate');
            $table->string('target_type');
            $table->integer('max_target_per_month');
            $table->integer('max_notification_per_month');
            $table->integer('max_email_per_month');
            $table->integer('max_sms_per_month');
            $table->integer('max_view_deatils_per_month');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

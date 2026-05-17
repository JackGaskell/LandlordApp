<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->unique()->after('remember_token');
            $table->string('stripe_subscription_id')->nullable()->unique()->after('stripe_customer_id');
            $table->string('subscription_status', 32)->nullable()->after('stripe_subscription_id');
        });

        Schema::table('payment_histories', function (Blueprint $table) {
            $table->string('recorded_via', 16)->default('manual')->after('verification_status');
            $table->string('stripe_checkout_session_id')->nullable()->unique()->after('recorded_via');
            $table->string('stripe_payment_intent_id')->nullable()->unique()->after('stripe_checkout_session_id');
        });

        Schema::create('stripe_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_event_id')->unique();
            $table->string('type');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'processed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_events');

        Schema::table('payment_histories', function (Blueprint $table) {
            $table->dropColumn([
                'recorded_via',
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_customer_id',
                'stripe_subscription_id',
                'subscription_status',
            ]);
        });
    }
};

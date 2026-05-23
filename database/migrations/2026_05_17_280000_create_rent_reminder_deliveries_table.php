<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_reminder_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_history_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('landlord_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reminder_type', 32);
            $table->unsignedTinyInteger('days_offset');
            $table->string('channel', 32)->default('email');
            $table->date('dispatch_date');
            $table->string('status', 32)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->unique(
                ['payment_history_id', 'reminder_type', 'days_offset', 'dispatch_date', 'channel'],
                'rent_reminder_deliveries_unique',
            );

            $table->index(['landlord_user_id', 'dispatch_date', 'status']);
            $table->index(['payment_history_id', 'reminder_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_reminder_deliveries');
    }
};

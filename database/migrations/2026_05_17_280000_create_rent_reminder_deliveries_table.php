<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rent_reminder_deliveries')) {
            $this->addReminderDeliveryIndexes();

            return;
        }

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

            $this->addReminderDeliveryIndexes($table);
        });
    }

    protected function addReminderDeliveryIndexes(?Blueprint $table = null): void
    {
        $addIndexes = function (Blueprint $blueprint): void {
            // MySQL identifier limit is 64 chars; Laravel's auto-generated names exceed that.
            if (! Schema::hasIndex('rent_reminder_deliveries', 'rrd_landlord_dispatch_status_idx')) {
                $blueprint->index(
                    ['landlord_user_id', 'dispatch_date', 'status'],
                    'rrd_landlord_dispatch_status_idx',
                );
            }

            if (! Schema::hasIndex('rent_reminder_deliveries', 'rrd_payment_reminder_idx')) {
                $blueprint->index(
                    ['payment_history_id', 'reminder_type'],
                    'rrd_payment_reminder_idx',
                );
            }
        };

        if ($table !== null) {
            $addIndexes($table);

            return;
        }

        Schema::table('rent_reminder_deliveries', $addIndexes);
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_reminder_deliveries');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlord_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('reminder_days_before');
            $table->json('overdue_reminder_days');
            $table->boolean('email_reminders_enabled')->default(true);
            $table->timestamps();

            // One settings row per landlord; fast lookup when sending reminders.
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_settings');
    }
};

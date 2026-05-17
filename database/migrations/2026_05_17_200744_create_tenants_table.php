<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone_number')->nullable();
            $table->decimal('rent_amount', 10, 2);
            $table->unsignedTinyInteger('rent_due_day');
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            // List tenants for a landlord (most common query).
            $table->index('user_id');

            // Filter active/inactive tenants on the index page.
            $table->index(['user_id', 'status']);

            // Upcoming rent-by-day widgets grouped per landlord.
            $table->index(['user_id', 'rent_due_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

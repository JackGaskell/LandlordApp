<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('status', 32)->default('due_soon');
            $table->string('verification_status', 32)->default('unverified');
            $table->timestamps();

            // Tenant payment timeline (newest first).
            $table->index(['tenant_id', 'due_date']);

            // Per-tenant status filters (e.g. open balances).
            $table->index(['tenant_id', 'status']);

            // Dashboard: overdue / due-soon scans for one landlord via tenant join.
            // Leading with status narrows rows before sorting by due_date.
            $table->index(['status', 'due_date']);

            // Collection totals and health metrics by due month.
            $table->index(['due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_histories', function (Blueprint $table) {
            // Covers landlord subquery (tenant_id IN …) + status filter + due_date sort.
            $table->index(['tenant_id', 'status', 'due_date'], 'payment_histories_tenant_status_due_idx');

            // Recent activity feed ordered by updated_at for a landlord's payments.
            $table->index(['updated_at', 'tenant_id'], 'payment_histories_updated_tenant_idx');
        });

        Schema::table('tenants', function (Blueprint $table) {
            // Monthly rent roll-up: SUM(rent_amount) WHERE user_id AND status.
            $table->index(['user_id', 'status', 'rent_amount'], 'tenants_landlord_status_rent_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payment_histories', function (Blueprint $table) {
            $table->dropIndex('payment_histories_tenant_status_due_idx');
            $table->dropIndex('payment_histories_updated_tenant_idx');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex('tenants_landlord_status_rent_idx');
        });
    }
};

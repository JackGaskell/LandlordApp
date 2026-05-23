<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_histories', function (Blueprint $table) {
            $table->string('payment_method', 32)->nullable()->after('recorded_via');
            $table->text('notes')->nullable()->after('payment_method');
            $table->unsignedSmallInteger('days_late')->nullable()->after('paid_at');
            $table->string('payment_outcome', 32)->nullable()->after('status');

            $table->index(['tenant_id', 'payment_outcome']);
            $table->index(['tenant_id', 'due_date', 'payment_outcome']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('reliability_score', 5, 2)->default(100)->after('portal_invite_expires_at');
            $table->unsignedSmallInteger('reliability_current_streak')->default(0)->after('reliability_score');
            $table->unsignedSmallInteger('reliability_best_streak')->default(0)->after('reliability_current_streak');
            $table->unsignedSmallInteger('reliability_on_time_count')->default(0)->after('reliability_best_streak');
            $table->unsignedSmallInteger('reliability_late_count')->default(0)->after('reliability_on_time_count');
            $table->unsignedSmallInteger('reliability_missed_count')->default(0)->after('reliability_late_count');
            $table->decimal('reliability_consistency_rate', 5, 2)->nullable()->after('reliability_missed_count');
            $table->unsignedSmallInteger('reliability_tracked_periods')->default(0)->after('reliability_consistency_rate');
            $table->timestamp('reliability_calculated_at')->nullable()->after('reliability_tracked_periods');
        });
    }

    public function down(): void
    {
        Schema::table('payment_histories', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'payment_outcome']);
            $table->dropIndex(['tenant_id', 'due_date', 'payment_outcome']);
            $table->dropColumn(['payment_method', 'notes', 'days_late', 'payment_outcome']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'reliability_score',
                'reliability_current_streak',
                'reliability_best_streak',
                'reliability_on_time_count',
                'reliability_late_count',
                'reliability_missed_count',
                'reliability_consistency_rate',
                'reliability_tracked_periods',
                'reliability_calculated_at',
            ]);
        });
    }
};

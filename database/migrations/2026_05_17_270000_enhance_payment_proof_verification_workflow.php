<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->timestamp('claimed_paid_at')->nullable()->after('tenant_note');
            $table->boolean('tenant_marked_paid')->default(false)->after('claimed_paid_at');
            $table->text('landlord_note')->nullable()->after('reviewed_at');
            $table->foreignId('reviewed_by_user_id')->nullable()->after('landlord_note')->constrained('users')->nullOnDelete();

            $table->index(['status', 'created_at']);
        });

        DB::table('payment_proofs')
            ->where('status', 'pending_review')
            ->update(['status' => 'pending']);
    }

    public function down(): void
    {
        DB::table('payment_proofs')
            ->where('status', 'pending')
            ->update(['status' => 'pending_review']);

        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by_user_id']);
            $table->dropColumn([
                'claimed_paid_at',
                'tenant_marked_paid',
                'landlord_note',
                'reviewed_by_user_id',
            ]);
            $table->dropIndex(['status', 'created_at']);
        });
    }
};

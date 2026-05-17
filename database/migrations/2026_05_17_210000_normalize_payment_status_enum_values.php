<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_histories')->where('status', 'pending')->update(['status' => 'due_soon']);
        DB::table('payment_histories')->where('status', 'partial')->update(['status' => 'partially_paid']);
    }

    public function down(): void
    {
        DB::table('payment_histories')->where('status', 'due_soon')->update(['status' => 'pending']);
        DB::table('payment_histories')->where('status', 'partially_paid')->update(['status' => 'partial']);
    }
};

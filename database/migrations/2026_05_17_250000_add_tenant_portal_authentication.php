<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken()->after('password');
            $table->timestamp('portal_enabled_at')->nullable()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('portal_enabled_at');
            $table->string('portal_invite_token', 64)->nullable()->unique()->after('last_login_at');
            $table->timestamp('portal_invite_expires_at')->nullable()->after('portal_invite_token');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn([
                'password',
                'remember_token',
                'portal_enabled_at',
                'last_login_at',
                'portal_invite_token',
                'portal_invite_expires_at',
            ]);
        });
    }
};

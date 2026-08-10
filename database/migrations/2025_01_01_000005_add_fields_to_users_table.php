<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('role')->default('customer')->after('avatar');
            $table->date('date_of_birth')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('date_of_birth');
            $table->boolean('email_notifications')->default(true)->after('is_active');
            $table->boolean('sms_notifications')->default(false)->after('email_notifications');
            $table->dateTime('last_login_at')->nullable()->after('sms_notifications');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->softDeletes();

            $table->index('role');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'avatar', 'role', 'date_of_birth',
                'is_active', 'email_notifications', 'sms_notifications',
                'last_login_at', 'last_login_ip',
            ]);
            $table->dropSoftDeletes();
        });
    }
};

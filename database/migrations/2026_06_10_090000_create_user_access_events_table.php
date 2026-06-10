<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'login_count')) {
                $table->unsignedInteger('login_count')->default(0)->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'failed_login_count')) {
                $table->unsignedInteger('failed_login_count')->default(0)->after('login_count');
            }

            if (!Schema::hasColumn('users', 'logout_count')) {
                $table->unsignedInteger('logout_count')->default(0)->after('failed_login_count');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->dateTime('last_login_at')->nullable()->after('logout_count');
            }

            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }

            if (!Schema::hasColumn('users', 'last_login_user_agent')) {
                $table->string('last_login_user_agent', 500)->nullable()->after('last_login_ip');
            }

            if (!Schema::hasColumn('users', 'last_logout_at')) {
                $table->dateTime('last_logout_at')->nullable()->after('last_login_user_agent');
            }
        });

        Schema::create('user_access_events', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('event_type', 40);
            $table->string('login_identifier', 120)->nullable();
            $table->string('identifier_hash', 64)->nullable();
            $table->string('auth_method', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('session_id', 120)->nullable();
            $table->string('route_name', 120)->nullable();
            $table->string('details', 500)->nullable();
            $table->dateTime('occurred_at')->useCurrent();

            $table->index(['user_id', 'event_type'], 'ix_user_access_events_user_type');
            $table->index(['event_type', 'occurred_at'], 'ix_user_access_events_type_date');
            $table->index('session_id', 'ix_user_access_events_session');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_access_events');

        Schema::table('users', function (Blueprint $table) {
            foreach ([
                'last_logout_at',
                'last_login_user_agent',
                'last_login_ip',
                'last_login_at',
                'logout_count',
                'failed_login_count',
                'login_count',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

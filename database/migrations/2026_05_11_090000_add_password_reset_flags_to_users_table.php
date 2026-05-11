<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'force_password_change')) {
                $table->boolean('force_password_change')->default(false);
            }

            if (!Schema::hasColumn('users', 'password_reset_at')) {
                $table->timestamp('password_reset_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_reset_at')) {
                $table->dropColumn('password_reset_at');
            }

            if (Schema::hasColumn('users', 'force_password_change')) {
                $table->dropColumn('force_password_change');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'pref_iframe_mode')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('pref_iframe_mode')->default(false)->after('profile_photo_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'pref_iframe_mode')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('pref_iframe_mode');
            });
        }
    }
};

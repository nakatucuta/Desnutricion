<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vacunas', 'docis_original')) {
            Schema::table('vacunas', function (Blueprint $table) {
                $table->string('docis_original', 255)->nullable()->after('docis');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vacunas', 'docis_original')) {
            Schema::table('vacunas', function (Blueprint $table) {
                $table->dropColumn('docis_original');
            });
        }
    }
};


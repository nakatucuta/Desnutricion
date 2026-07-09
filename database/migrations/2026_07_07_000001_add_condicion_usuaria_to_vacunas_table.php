<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('vacunas', 'condicion_usuaria')) {
            Schema::table('vacunas', function (Blueprint $table) {
                $table->string('condicion_usuaria')->nullable()->after('regimen');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vacunas', 'condicion_usuaria')) {
            Schema::table('vacunas', function (Blueprint $table) {
                $table->dropColumn('condicion_usuaria');
            });
        }
    }
};

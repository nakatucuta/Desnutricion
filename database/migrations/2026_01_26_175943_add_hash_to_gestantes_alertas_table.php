<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gestantes_alertas', function (Blueprint $table) {
            // sha256 => 64 chars
            if (!Schema::hasColumn('gestantes_alertas', 'hash')) {
                $table->string('hash', 64)->nullable()->index();
            }
        });

        Schema::table('gestantes_alertas', function (Blueprint $table) {
            // evita duplicados
            $table->unique('hash', 'gestantes_alertas_hash_unique');
        });
    }

    public function down(): void
    {
        Schema::table('gestantes_alertas', function (Blueprint $table) {
            $table->dropUnique('gestantes_alertas_hash_unique');
            $table->dropColumn('hash');
        });
    }
};

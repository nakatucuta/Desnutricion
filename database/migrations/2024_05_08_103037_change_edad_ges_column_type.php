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
        Schema::table('sivigilas', function (Blueprint $table) {
            $table->float('edad_ges', 8, 2)->change(); // Ajusta los parámetros según tus necesidades
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sivigilas', function (Blueprint $table) {
            $table->integer('edad_ges')->change();
        });
    }
};

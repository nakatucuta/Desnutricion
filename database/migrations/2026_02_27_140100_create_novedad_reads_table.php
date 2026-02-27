<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('novedad_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('novedad_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('read_at');
            $table->timestamps();

            $table->unique(['novedad_id', 'user_id'], 'ux_novedad_reads_novedad_user');
            $table->index(['user_id', 'read_at'], 'ix_novedad_reads_user_readat');
            $table->foreign('novedad_id')->references('id')->on('novedades')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novedad_reads');
    }
};

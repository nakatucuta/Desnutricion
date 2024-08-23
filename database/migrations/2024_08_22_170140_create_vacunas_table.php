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
        Schema::create('vacunas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre')->nullable();
            $table->string('docis')->nullable();
            $table->string('laboratorio')->nullable();
            $table->string('lote')->nullable();
            $table->string('jeringa')->nullable();
            $table->string('lote_jeringa')->nullable();
            $table->string('diluyente')->nullable();
            $table->string('lote_diluyente')->nullable();
            $table->string('observacion')->nullable();
            $table->string('gotero')->nullable();
            $table->string('tipo_neumococo')->nullable();
            $table->string('num_frascos_utilizados')->nullable();
            $table->date('fecha_vacuna')->nullable();

            $table->string('responsable')->nullable();
            $table->string('fuen_ingresado_paiweb')->nullable();
            $table->string('motivo_noingreso')->nullable();
            $table->string('observaciones')->nullable();
            
            $table->integer('batch_verifications_id')->nullable();
            $table->foreign('batch_verifications_id')->references('id')->on('batch_verifications');
            
            $table->integer('afiliado_id')->unsigned()->nullable();
            $table->foreign('afiliado_id')->references('id')->on('afiliados');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacunas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ✅ Como es módulo nuevo, lo más seguro es recrear
        Schema::dropIfExists('gestantes_alertas');

        Schema::create('gestantes_alertas', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ✅ En SQL Server NO hay unsigned. Usa integer normal.
            $table->integer('ges_tipo1_id')->nullable()->index();
            $table->integer('seguimiento_id')->nullable()->index();
            $table->integer('user_id')->nullable()->index();

            $table->string('modulo', 30)->index();          // ges_tipo1
            $table->string('campo', 80)->index();           // vih_tamiz1_resultado
            $table->string('examen', 150)->nullable();      // VIH Tamiz 1
            $table->string('resultado', 100)->nullable();   // REACTIVO
            $table->string('severidad', 20)->default('media'); // baja|media|alta

            $table->string('pdf_path', 255)->nullable();

            // ✅ anti-duplicados
            $table->string('hash', 64)->unique();

            // opcionales
            $table->dateTime('seen_at')->nullable();
            $table->dateTime('resolved_at')->nullable();

            // ✅ CLAVE EN SQL SERVER: sin milisegundos
            $table->timestamps(0);

            // ✅ FKs sin cascada (NO ACTION por defecto)
            $table->foreign('ges_tipo1_id')->references('id')->on('ges_tipo1');
            $table->foreign('seguimiento_id')->references('id')->on('ges_tipo1_seguimientos');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gestantes_alertas');
    }
};

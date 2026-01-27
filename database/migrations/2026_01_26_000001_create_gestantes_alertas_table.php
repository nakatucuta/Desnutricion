<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gestantes_alertas', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ✅ PKs INT en tu proyecto (increments) => unsignedInteger
            $table->unsignedInteger('ges_tipo1_id')->nullable()->index();
            $table->unsignedInteger('seguimiento_id')->nullable()->index();
            $table->unsignedInteger('user_id')->nullable()->index();

            // Info alerta
            $table->string('modulo', 30)->index();
            $table->string('campo', 80)->index();
            $table->string('label', 150)->nullable();
            $table->string('resultado', 100)->nullable();
            $table->text('detalle')->nullable();
            $table->string('pdf_path')->nullable();
            $table->boolean('correo_enviado')->default(0);
            $table->timestamp('correo_enviado_at')->nullable();

            $table->timestamps();

            // ✅ FKs SIN cascada (NO ACTION)
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

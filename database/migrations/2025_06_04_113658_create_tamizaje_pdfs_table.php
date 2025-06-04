<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tamizaje_pdfs', function (Blueprint $table) {
            $table->increments('id');

            // Relación “un tamizaje tiene muchos PDFs”
            $table->unsignedInteger('tamizaje_id')->nullable();
            $table->foreign('tamizaje_id')
                  ->references('id')
                  ->on('tamizajes')
                  ->onDelete('cascade');

            // Campos para poder asociar por persona si no encontramos tamizaje exacto
            $table->string('tipo_identificacion');
            $table->string('numero_identificacion');

            // Datos del archivo
            $table->string('original_name'); 
            $table->string('file_path');      // ruta en disk('public')

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tamizaje_pdfs');
    }
};

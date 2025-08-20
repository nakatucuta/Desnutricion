<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('seguimiento_alertas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seguimiento_id')
                  ->constrained('seguimient_maestrosiv549')
                  ->onDelete('cascade');

            // Clave del hito: '48h72h', '7d', '14d', '21d', '28d', '6m', '1y'
            $table->string('hito', 20);

            // En SQL Server usa dateTime, NO "timestamp" (rowversion)
            $table->dateTime('sent_at')->nullable();

            $table->timestamps();

            $table->unique(['seguimiento_id', 'hito'], 'seguimiento_hito_unique');
            $table->index(['hito']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seguimiento_alertas');
    }
};

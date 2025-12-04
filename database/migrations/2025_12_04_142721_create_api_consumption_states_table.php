<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('api_consumption_states', function (Blueprint $table) {
            $table->increments('id'); // INT identity en SQL Server

            // ✅ user_id como INT para que coincida con users.id
            $table->integer('user_id');

            $table->string('endpoint', 100); // ej: afiliados
            $table->integer('last_anio')->nullable();
            $table->integer('last_mes')->nullable();
            $table->string('last_carnet', 50)->nullable();
            $table->timestamps();

            // ✅ único por usuario/endpoint
            $table->unique(['user_id', 'endpoint']);

            // ✅ FK compatible con INT
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_consumption_states');
    }
};

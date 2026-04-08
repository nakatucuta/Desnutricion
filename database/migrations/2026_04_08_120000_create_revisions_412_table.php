<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('revisions_412')) {
            return;
        }

        Schema::create('revisions_412', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('estado')->default(1);
            $table->unsignedInteger('seguimiento_412_id')->unique();
            $table->foreign('seguimiento_412_id')
                ->references('id')
                ->on('seguimiento_412s')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions_412');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preconcepcional_import_batches', function (Blueprint $table) {

            // ✅ PK INT (coherente con users.id)
            $table->increments('id');

            // ✅ FK a users.id (INT)
            $table->integer('user_id')->nullable()->index();

            // info archivo
            $table->string('original_name')->nullable();
            $table->string('file_hash', 64)->nullable()->index();
            $table->bigInteger('file_size')->nullable();

            // métricas
            $table->integer('rows_total')->default(0);
            $table->integer('rows_created')->default(0);
            $table->integer('rows_updated')->default(0);
            $table->integer('rows_skipped')->default(0);

            $table->decimal('duration_seconds', 10, 2)->nullable();

            $table->timestamps();

            // ✅ FK con mismo tipo (INT)
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preconcepcional_import_batches');
    }
};

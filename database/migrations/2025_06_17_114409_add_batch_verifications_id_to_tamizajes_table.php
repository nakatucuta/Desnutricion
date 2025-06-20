<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamizajes', function (Blueprint $table) {
            $table->unsignedInteger('batch_verifications_id')
                  ->nullable()
                  ->after('user_id');

            $table->foreign('batch_verifications_id')
                  ->references('id')
                  ->on('batch_verifications')
                  ->onDelete('set null'); // o cascade / restrict
        });
    }

    public function down(): void
    {
        Schema::table('tamizajes', function (Blueprint $table) {
            $table->dropForeign(['batch_verifications_id']);
            $table->dropColumn('batch_verifications_id');
        });
    }
};

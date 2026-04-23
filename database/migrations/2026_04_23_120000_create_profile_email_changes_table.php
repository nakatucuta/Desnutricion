<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_email_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('new_email', 150);
            $table->string('token_hash', 64)->unique();
            $table->timestamp('requested_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('requested_ip', 45)->nullable();
            $table->string('requested_user_agent', 255)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'confirmed_at'], 'profile_email_changes_user_confirmed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_email_changes');
    }
};

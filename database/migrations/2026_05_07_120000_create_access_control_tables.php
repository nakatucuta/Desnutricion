<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 120)->unique();
            $table->string('name', 180);
            $table->string('description', 500)->nullable();
            $table->boolean('is_assignable')->default(true);
            $table->timestamps();
        });

        Schema::create('user_module_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('module_permission_id');
            $table->unsignedInteger('granted_by_user_id')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'module_permission_id'], 'ux_user_module_permission');
            $table->index('module_permission_id', 'ix_user_module_permission_module');
            $table->index('granted_by_user_id', 'ix_user_module_permission_granted_by');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('module_permission_id')->references('id')->on('module_permissions')->onDelete('cascade');
            $table->foreign('granted_by_user_id')->references('id')->on('users');
        });

        Schema::create('module_access_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('module_permission_id');
            $table->string('status', 20)->default('pending');
            $table->string('requested_reason', 500)->nullable();
            $table->string('admin_response', 500)->nullable();
            $table->unsignedInteger('resolved_by_user_id')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'ix_access_requests_user_status');
            $table->index(['module_permission_id', 'status'], 'ix_access_requests_module_status');
            $table->index('resolved_by_user_id', 'ix_access_requests_resolved_by');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('module_permission_id')->references('id')->on('module_permissions')->onDelete('cascade');
            $table->foreign('resolved_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_access_requests');
        Schema::dropIfExists('user_module_permissions');
        Schema::dropIfExists('module_permissions');
    }
};

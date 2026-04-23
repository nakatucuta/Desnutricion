<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfilePasswordUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropIfExists('profile_change_audits');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('usertype')->default(2);
            $table->string('codigohabilitacion')->nullable();
            $table->string('profile_photo_path', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('profile_change_audits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('changed_by_id')->nullable();
            $table->json('changed_fields');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
        });

        Mail::fake();
    }

    public function test_password_update_rejects_weak_password(): void
    {
        $user = User::create([
            'name' => 'Usuario Password',
            'email' => 'pass.user@gmail.com',
            'password' => bcrypt('Actual123!'),
            'usertype' => 2,
            'codigohabilitacion' => 'PASS001',
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.password.update'), [
            'current_password' => 'Actual123!',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_update_rejects_same_password(): void
    {
        $user = User::create([
            'name' => 'Usuario Password',
            'email' => 'pass.user2@gmail.com',
            'password' => bcrypt('Actual123!'),
            'usertype' => 2,
            'codigohabilitacion' => 'PASS002',
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.password.update'), [
            'current_password' => 'Actual123!',
            'password' => 'Actual123!',
            'password_confirmation' => 'Actual123!',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors([
            'password' => 'La nueva contrasena debe ser diferente a la actual.',
        ]);
    }

    public function test_password_update_successfully_updates_hash(): void
    {
        $user = User::create([
            'name' => 'Usuario Password',
            'email' => 'pass.user3@gmail.com',
            'password' => bcrypt('Actual123!'),
            'usertype' => 2,
            'codigohabilitacion' => 'PASS003',
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.password.update'), [
            'current_password' => 'Actual123!',
            'password' => 'NuevaSegura123#',
            'password_confirmation' => 'NuevaSegura123#',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'Contrasena actualizada de forma segura. Se cerraron otras sesiones activas.');

        $user->refresh();
        $this->assertTrue(Hash::check('NuevaSegura123#', $user->password));
    }
}


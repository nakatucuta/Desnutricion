<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
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

    public function test_profile_update_requires_email_with_spanish_message(): void
    {
        $user = User::create([
            'name' => 'Usuario Prueba',
            'email' => 'usuario.prueba@gmail.com',
            'password' => bcrypt('Secret123!'),
            'usertype' => 2,
            'codigohabilitacion' => 'USR001',
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => 'Usuario Prueba',
            'email' => '',
            'codigohabilitacion' => 'USR001',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors([
            'email' => 'El correo es obligatorio.',
        ]);
    }

    public function test_profile_update_rejects_invalid_email_format_with_spanish_message(): void
    {
        $user = User::create([
            'name' => 'Usuario Prueba',
            'email' => 'usuario.formato@gmail.com',
            'password' => bcrypt('Secret123!'),
            'usertype' => 2,
            'codigohabilitacion' => 'USR002',
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => 'Usuario Prueba',
            'email' => 'correo-invalido',
            'codigohabilitacion' => 'USR002',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors([
            'email' => 'Correo invalido. Usa el formato usuario@dominio.com y verifica que el dominio exista.',
        ]);
    }

    public function test_profile_update_rejects_duplicated_email_with_spanish_message(): void
    {
        $user = User::create([
            'name' => 'Usuario Uno',
            'email' => 'usuario.uno@gmail.com',
            'password' => bcrypt('Secret123!'),
            'usertype' => 2,
            'codigohabilitacion' => 'USR003',
        ]);

        User::create([
            'name' => 'Usuario Dos',
            'email' => 'usuario.dos@gmail.com',
            'password' => bcrypt('Secret123!'),
            'usertype' => 2,
            'codigohabilitacion' => 'USR004',
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => 'Usuario Uno',
            'email' => 'usuario.dos@gmail.com',
            'codigohabilitacion' => 'USR003',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors([
            'email' => 'Este correo ya esta siendo usado por otro usuario.',
        ]);
    }

    public function test_profile_update_normalizes_email_and_updates_successfully(): void
    {
        $user = User::create([
            'name' => 'Usuario Normaliza',
            'email' => 'usuario.normaliza@gmail.com',
            'password' => bcrypt('Secret123!'),
            'usertype' => 2,
            'codigohabilitacion' => 'USR005',
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => 'Usuario Normaliza',
            'email' => '  Nuevo.Correo@GMAIL.com  ',
            'codigohabilitacion' => 'USR005',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'Perfil actualizado correctamente.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'nuevo.correo@gmail.com',
        ]);
    }
}


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('vacunas', 'regimen')) {
            Schema::table('vacunas', function (Blueprint $table) {
                $table->string('regimen')->nullable()->after('observaciones');
            });
        }

        $now = now();

        $this->upsertReferenciaVacuna(
            55,
            'VIRUS SINCITIAL RESPIRATORIO (VSR)',
            $now
        );

        $this->upsertReferenciaVacuna(
            56,
            'DENGUE',
            $now
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vacunas', 'regimen')) {
            Schema::table('vacunas', function (Blueprint $table) {
                $table->dropColumn('regimen');
            });
        }

        foreach ([55, 56] as $id) {
            $inUse = DB::table('vacunas')->where('vacunas_id', $id)->exists();
            if (!$inUse) {
                DB::table('referencia_vacunas')->where('id', $id)->delete();
            }
        }
    }

    private function upsertReferenciaVacuna(int $id, string $nombre, $now): void
    {
        $exists = DB::table('referencia_vacunas')->where('id', $id)->exists();

        if ($exists) {
            DB::table('referencia_vacunas')
                ->where('id', $id)
                ->update([
                    'nombre' => $nombre,
                    'updated_at' => $now,
                ]);
            return;
        }

        // SQL Server: ejecutamos ON + INSERT + OFF en un solo batch para asegurar misma sesión.
        $nombreEsc = str_replace("'", "''", $nombre);
        DB::unprepared("
            SET IDENTITY_INSERT [dbo].[referencia_vacunas] ON;
            INSERT INTO [dbo].[referencia_vacunas] ([id], [nombre], [created_at], [updated_at])
            VALUES ({$id}, N'{$nombreEsc}', GETDATE(), GETDATE());
            SET IDENTITY_INSERT [dbo].[referencia_vacunas] OFF;
        ");
    }
};

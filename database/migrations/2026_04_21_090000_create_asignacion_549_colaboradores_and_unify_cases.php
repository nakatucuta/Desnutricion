<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si quedo creada parcialmente por un intento fallido, la recreamos limpia.
        if (Schema::hasTable('asignaciones_maestrosiv549_colaboradores')) {
            Schema::drop('asignaciones_maestrosiv549_colaboradores');
        }

        Schema::create('asignaciones_maestrosiv549_colaboradores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asignacion_id');
            $table->unsignedInteger('user_id'); // users.id en este proyecto es INT
            $table->timestamps();

            $table->unique(['asignacion_id', 'user_id'], 'ux_asig549_colab_asig_user');
            $table->foreign('asignacion_id')->references('id')->on('asignaciones_maestrosiv549')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Unifica por caso+periodo en una sola asignacion canonica.
        DB::statement("
            WITH base AS (
                SELECT
                    a.id,
                    a.user_id,
                    LTRIM(RTRIM(COALESCE(a.tip_ide_, ''))) tip,
                    LTRIM(RTRIM(COALESCE(a.num_ide_, ''))) num,
                    LTRIM(RTRIM(COALESCE(a.nom_eve, ''))) eve,
                    COALESCE(NULLIF(LTRIM(RTRIM(COALESCE(a.[year], ''))), ''), CONVERT(varchar(4), YEAR(a.fec_not)), '0000') yr,
                    COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(a.semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, a.fec_not)), 2), '00') sem,
                    CASE WHEN EXISTS (SELECT 1 FROM seguimient_maestrosiv549 s WHERE s.asignacion_id = a.id) THEN 1 ELSE 0 END has_seg
                FROM asignaciones_maestrosiv549 a
            ),
            canon AS (
                SELECT
                    id,
                    user_id,
                    tip,
                    num,
                    eve,
                    yr,
                    sem,
                    ROW_NUMBER() OVER (
                        PARTITION BY tip, num, eve, yr, sem
                        ORDER BY has_seg DESC, id ASC
                    ) rn
                FROM base
            )
            INSERT INTO asignaciones_maestrosiv549_colaboradores (asignacion_id, user_id, created_at, updated_at)
            SELECT DISTINCT
                c0.id as asignacion_id,
                c.user_id,
                GETDATE(),
                GETDATE()
            FROM canon c
            JOIN canon c0
              ON c.tip = c0.tip
             AND c.num = c0.num
             AND c.eve = c0.eve
             AND c.yr = c0.yr
             AND c.sem = c0.sem
             AND c0.rn = 1
            WHERE NOT EXISTS (
                SELECT 1
                FROM asignaciones_maestrosiv549_colaboradores x
                WHERE x.asignacion_id = c0.id AND x.user_id = c.user_id
            );
        ");

        // Reasigna seguimientos a la asignacion canonica.
        DB::statement("
            WITH base AS (
                SELECT
                    a.id,
                    LTRIM(RTRIM(COALESCE(a.tip_ide_, ''))) tip,
                    LTRIM(RTRIM(COALESCE(a.num_ide_, ''))) num,
                    LTRIM(RTRIM(COALESCE(a.nom_eve, ''))) eve,
                    COALESCE(NULLIF(LTRIM(RTRIM(COALESCE(a.[year], ''))), ''), CONVERT(varchar(4), YEAR(a.fec_not)), '0000') yr,
                    COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(a.semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, a.fec_not)), 2), '00') sem,
                    CASE WHEN EXISTS (SELECT 1 FROM seguimient_maestrosiv549 s WHERE s.asignacion_id = a.id) THEN 1 ELSE 0 END has_seg
                FROM asignaciones_maestrosiv549 a
            ),
            canon AS (
                SELECT
                    id,
                    tip,
                    num,
                    eve,
                    yr,
                    sem,
                    ROW_NUMBER() OVER (
                        PARTITION BY tip, num, eve, yr, sem
                        ORDER BY has_seg DESC, id ASC
                    ) rn
                FROM base
            ),
            map AS (
                SELECT c.id source_id, c0.id canonical_id
                FROM canon c
                JOIN canon c0
                  ON c.tip = c0.tip
                 AND c.num = c0.num
                 AND c.eve = c0.eve
                 AND c.yr = c0.yr
                 AND c.sem = c0.sem
                 AND c0.rn = 1
            )
            UPDATE s
            SET s.asignacion_id = m.canonical_id
            FROM seguimient_maestrosiv549 s
            JOIN map m ON m.source_id = s.asignacion_id
            WHERE s.asignacion_id <> m.canonical_id;
        ");

        // Elimina asignaciones no canonicas.
        DB::statement("
            WITH base AS (
                SELECT
                    a.id,
                    LTRIM(RTRIM(COALESCE(a.tip_ide_, ''))) tip,
                    LTRIM(RTRIM(COALESCE(a.num_ide_, ''))) num,
                    LTRIM(RTRIM(COALESCE(a.nom_eve, ''))) eve,
                    COALESCE(NULLIF(LTRIM(RTRIM(COALESCE(a.[year], ''))), ''), CONVERT(varchar(4), YEAR(a.fec_not)), '0000') yr,
                    COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(a.semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, a.fec_not)), 2), '00') sem,
                    CASE WHEN EXISTS (SELECT 1 FROM seguimient_maestrosiv549 s WHERE s.asignacion_id = a.id) THEN 1 ELSE 0 END has_seg
                FROM asignaciones_maestrosiv549 a
            ),
            canon AS (
                SELECT
                    id,
                    ROW_NUMBER() OVER (
                        PARTITION BY tip, num, eve, yr, sem
                        ORDER BY has_seg DESC, id ASC
                    ) rn
                FROM base
            )
            DELETE a
            FROM asignaciones_maestrosiv549 a
            JOIN canon c ON c.id = a.id
            WHERE c.rn > 1;
        ");

        // Índice único final: 1 asignación por caso+periodo.
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.indexes
                WHERE name = 'ux_asig549_user_case_period_unique'
                  AND object_id = OBJECT_ID('asignaciones_maestrosiv549')
            )
            BEGIN
                DROP INDEX ux_asig549_user_case_period_unique ON asignaciones_maestrosiv549;
            END
        ");

        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes
                WHERE name = 'ux_asig549_case_period_unique'
                  AND object_id = OBJECT_ID('asignaciones_maestrosiv549')
            )
            BEGIN
                CREATE UNIQUE INDEX ux_asig549_case_period_unique
                ON asignaciones_maestrosiv549 (
                    tip_ide_norm,
                    num_ide_norm,
                    nom_eve_norm,
                    year_period_norm,
                    semana_period_norm
                );
            END
        ");
    }

    public function down(): void
    {
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.indexes
                WHERE name = 'ux_asig549_case_period_unique'
                  AND object_id = OBJECT_ID('asignaciones_maestrosiv549')
            )
            BEGIN
                DROP INDEX ux_asig549_case_period_unique ON asignaciones_maestrosiv549;
            END
        ");

        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes
                WHERE name = 'ux_asig549_user_case_period_unique'
                  AND object_id = OBJECT_ID('asignaciones_maestrosiv549')
            )
            BEGIN
                CREATE UNIQUE INDEX ux_asig549_user_case_period_unique
                ON asignaciones_maestrosiv549 (
                    user_id,
                    tip_ide_norm,
                    num_ide_norm,
                    nom_eve_norm,
                    year_period_norm,
                    semana_period_norm
                );
            END
        ");

        Schema::dropIfExists('asignaciones_maestrosiv549_colaboradores');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'year_period_norm') IS NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549
                ADD year_period_norm AS COALESCE(
                    NULLIF(LTRIM(RTRIM(COALESCE([year], ''))), ''),
                    CONVERT(varchar(4), YEAR(fec_not)),
                    '0000'
                ) PERSISTED;
            END
        ");

        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'semana_period_norm') IS NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549
                ADD semana_period_norm AS COALESCE(
                    NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(semana, ''))), 2), ''),
                    RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, fec_not)), 2),
                    '00'
                ) PERSISTED;
            END
        ");

        DB::statement("
            WITH marks AS (
                SELECT
                    a.id,
                    ROW_NUMBER() OVER (
                        PARTITION BY
                            UPPER(LTRIM(RTRIM(COALESCE(a.tip_ide_, '')))),
                            LTRIM(RTRIM(COALESCE(a.num_ide_, ''))),
                            UPPER(LTRIM(RTRIM(COALESCE(a.nom_eve, '')))),
                            COALESCE(NULLIF(LTRIM(RTRIM(COALESCE(a.[year], ''))), ''), CONVERT(varchar(4), YEAR(a.fec_not)), '0000'),
                            COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(a.semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, a.fec_not)), 2), '00')
                        ORDER BY
                            CASE WHEN EXISTS (SELECT 1 FROM seguimient_maestrosiv549 s WHERE s.asignacion_id = a.id) THEN 1 ELSE 0 END DESC,
                            a.id DESC
                    ) AS rn
                FROM asignaciones_maestrosiv549 a
            )
            DELETE FROM asignaciones_maestrosiv549
            WHERE id IN (SELECT id FROM marks WHERE rn > 1);
        ");

        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.indexes
                WHERE name = 'ux_asig549_case_unique'
                  AND object_id = OBJECT_ID('asignaciones_maestrosiv549')
            )
            BEGIN
                DROP INDEX ux_asig549_case_unique ON asignaciones_maestrosiv549;
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
            IF EXISTS (
                SELECT 1 FROM sys.indexes
                WHERE name = 'ux_asig549_case_unique'
                  AND object_id = OBJECT_ID('asignaciones_maestrosiv549')
            )
            BEGIN
                DROP INDEX ux_asig549_case_unique ON asignaciones_maestrosiv549;
            END
        ");

        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes
                WHERE name = 'ux_asig549_case_unique'
                  AND object_id = OBJECT_ID('asignaciones_maestrosiv549')
            )
            BEGIN
                CREATE UNIQUE INDEX ux_asig549_case_unique
                ON asignaciones_maestrosiv549 (tip_ide_norm, num_ide_norm, fec_not_norm, nom_eve_norm);
            END
        ");

        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'semana_period_norm') IS NOT NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549 DROP COLUMN semana_period_norm;
            END
        ");

        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'year_period_norm') IS NOT NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549 DROP COLUMN year_period_norm;
            END
        ");
    }
};


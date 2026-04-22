<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'tip_ide_norm') IS NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549
                ADD tip_ide_norm AS UPPER(LTRIM(RTRIM(ISNULL(tip_ide_, '')))) PERSISTED;
            END
        ");

        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'num_ide_norm') IS NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549
                ADD num_ide_norm AS LTRIM(RTRIM(ISNULL(num_ide_, ''))) PERSISTED;
            END
        ");

        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'nom_eve_norm') IS NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549
                ADD nom_eve_norm AS UPPER(LTRIM(RTRIM(ISNULL(nom_eve, '')))) PERSISTED;
            END
        ");

        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'fec_not_norm') IS NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549
                ADD fec_not_norm AS CAST(fec_not AS date) PERSISTED;
            END
        ");

        DB::statement("
            IF NOT EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = 'ux_asig549_case_unique'
                  AND object_id = OBJECT_ID('asignaciones_maestrosiv549')
            )
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM asignaciones_maestrosiv549
                    GROUP BY tip_ide_norm, num_ide_norm, fec_not_norm, nom_eve_norm
                    HAVING COUNT(*) > 1
                )
                BEGIN
                    CREATE UNIQUE INDEX ux_asig549_case_unique
                    ON asignaciones_maestrosiv549 (tip_ide_norm, num_ide_norm, fec_not_norm, nom_eve_norm);
                END
                ELSE
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1
                        FROM sys.indexes
                        WHERE name = 'ix_asig549_case_lookup'
                          AND object_id = OBJECT_ID('asignaciones_maestrosiv549')
                    )
                    BEGIN
                        CREATE INDEX ix_asig549_case_lookup
                        ON asignaciones_maestrosiv549 (tip_ide_norm, num_ide_norm, fec_not_norm, nom_eve_norm);
                    END
                END
            END
        ");
    }

    public function down(): void
    {
        DB::statement("
            IF EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = 'ux_asig549_case_unique'
                  AND object_id = OBJECT_ID('asignaciones_maestrosiv549')
            )
            BEGIN
                DROP INDEX ux_asig549_case_unique ON asignaciones_maestrosiv549;
            END
        ");

        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'fec_not_norm') IS NOT NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549 DROP COLUMN fec_not_norm;
            END
        ");

        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'nom_eve_norm') IS NOT NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549 DROP COLUMN nom_eve_norm;
            END
        ");

        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'num_ide_norm') IS NOT NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549 DROP COLUMN num_ide_norm;
            END
        ");

        DB::statement("
            IF COL_LENGTH('asignaciones_maestrosiv549', 'tip_ide_norm') IS NOT NULL
            BEGIN
                ALTER TABLE asignaciones_maestrosiv549 DROP COLUMN tip_ide_norm;
            END
        ");
    }
};

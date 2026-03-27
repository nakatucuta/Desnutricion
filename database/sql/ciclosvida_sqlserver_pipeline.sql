/*
    Framework SQL Server para materializar ciclos de vida directamente en las tablas cache
    usadas por la aplicacion Laravel.

    OBJETIVO
    - Cargar historico masivo por curso/modulo/ventana sin pasar millones de filas por PHP.
    - Mantener compatibilidad con:
        dbo.ciclo_vida_cache_runs
        dbo.ciclo_vida_cache_records
        dbo.ciclo_vida_cache_summaries

    ENFOQUE
    1. Cada modulo debe exponerse como una fuente SQL estandarizada.
       Puede ser:
       - una vista: dbo.v_cv_<curso>_<modulo>
       - o un procedimiento: dbo.sp_cv_src_<curso>_<modulo>

    2. La fuente estandarizada debe devolver estas columnas:
       event_date              date          null
       tipo_identificacion     varchar(20)   null
       identificacion          varchar(40)   null
       primer_nombre           varchar(120)  null
       segundo_nombre          varchar(120)  null
       primer_apellido         varchar(120)  null
       segundo_apellido        varchar(120)  null
       fecha_nacimiento        date          null
       edad                    int           null
       edad_meses              int           null
       rango_edad              varchar(80)   null
       codigo_ips              varchar(50)   null
       ips_primaria            varchar(200)  null
       codigo_servicio         varchar(60)   null
       descripcion_servicio    varchar(255)  null
       diagnostico_principal   varchar(60)   null
       finalidad               varchar(60)   null
       payload_json            nvarchar(max) null

    3. Este script centraliza:
       - registro de ejecucion
       - borrado por ventana
       - insercion en cache
       - resumen por ventana
       - backfill por meses/trimestres/anios

    IMPORTANTE
    - Este framework no reemplaza automaticamente tus .sql actuales.
    - Primero debes envolver la logica de cada modulo en vistas o SPs con la forma estandarizada.
*/

SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

IF OBJECT_ID('dbo.cv_sql_module_config', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.cv_sql_module_config (
        id                    bigint IDENTITY(1,1) PRIMARY KEY,
        course_key            varchar(80)  NOT NULL,
        module_key            varchar(80)  NOT NULL,
        module_label          varchar(160) NULL,
        record_type           varchar(30)  NOT NULL CONSTRAINT DF_cv_sql_module_config_record_type DEFAULT ('event'),
        source_type           varchar(20)  NOT NULL, -- VIEW | PROC
        source_object         varchar(256) NOT NULL, -- dbo.v_xxx o dbo.sp_xxx
        is_enabled            bit          NOT NULL CONSTRAINT DF_cv_sql_module_config_enabled DEFAULT (1),
        created_at            datetime2(0) NOT NULL CONSTRAINT DF_cv_sql_module_config_created DEFAULT (sysdatetime()),
        updated_at            datetime2(0) NOT NULL CONSTRAINT DF_cv_sql_module_config_updated DEFAULT (sysdatetime()),
        CONSTRAINT UQ_cv_sql_module_config UNIQUE (course_key, module_key)
    );
END;
GO

CREATE OR ALTER FUNCTION dbo.fn_cv_rango_edad(@edad int)
RETURNS varchar(80)
AS
BEGIN
    DECLARE @out varchar(80);

    SET @out = CASE
        WHEN @edad IS NULL THEN NULL
        WHEN @edad BETWEEN 0 AND 5  THEN '0-5 anos'
        WHEN @edad BETWEEN 6 AND 11 THEN '6-11 anos'
        WHEN @edad BETWEEN 12 AND 17 THEN '12-17 anos'
        WHEN @edad BETWEEN 18 AND 28 THEN '18-28 anos'
        WHEN @edad BETWEEN 29 AND 59 THEN '29-59 anos'
        WHEN @edad >= 60 THEN '60 anos y mas'
        ELSE NULL
    END;

    RETURN @out;
END;
GO

CREATE OR ALTER PROCEDURE dbo.sp_cv_refresh_module_window
    @course_key varchar(80),
    @module_key varchar(80),
    @from_date  date,
    @to_date    date
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE
        @run_id bigint,
        @module_label varchar(160),
        @record_type varchar(30),
        @source_type varchar(20),
        @source_object varchar(256),
        @sql nvarchar(max),
        @records_loaded int = 0;

    SELECT
        @module_label = module_label,
        @record_type = record_type,
        @source_type = source_type,
        @source_object = source_object
    FROM dbo.cv_sql_module_config
    WHERE course_key = @course_key
      AND module_key = @module_key
      AND is_enabled = 1;

    IF @source_object IS NULL
    BEGIN
        RAISERROR('No existe configuracion activa para el curso/modulo solicitado.', 16, 1);
        RETURN;
    END;

    INSERT INTO dbo.ciclo_vida_cache_runs (
        course_key,
        module_key,
        module_label,
        range_start,
        range_end,
        status,
        records_loaded,
        started_at,
        created_at,
        updated_at
    )
    VALUES (
        @course_key,
        @module_key,
        @module_label,
        @from_date,
        @to_date,
        'running',
        0,
        SYSDATETIME(),
        SYSDATETIME(),
        SYSDATETIME()
    );

    SET @run_id = SCOPE_IDENTITY();

    BEGIN TRY
        IF OBJECT_ID('tempdb..#cv_source') IS NOT NULL
            DROP TABLE #cv_source;

        CREATE TABLE #cv_source (
            event_date            date           NULL,
            tipo_identificacion   varchar(20)    NULL,
            identificacion        varchar(40)    NULL,
            primer_nombre         varchar(120)   NULL,
            segundo_nombre        varchar(120)   NULL,
            primer_apellido       varchar(120)   NULL,
            segundo_apellido      varchar(120)   NULL,
            fecha_nacimiento      date           NULL,
            edad                  int            NULL,
            edad_meses            int            NULL,
            rango_edad            varchar(80)    NULL,
            codigo_ips            varchar(50)    NULL,
            ips_primaria          varchar(200)   NULL,
            codigo_servicio       varchar(60)    NULL,
            descripcion_servicio  varchar(255)   NULL,
            diagnostico_principal varchar(60)    NULL,
            finalidad             varchar(60)    NULL,
            payload_json          nvarchar(max)  NULL
        );

        IF UPPER(@source_type) = 'VIEW'
        BEGIN
            SET @sql = N'
                INSERT INTO #cv_source (
                    event_date, tipo_identificacion, identificacion,
                    primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
                    fecha_nacimiento, edad, edad_meses, rango_edad,
                    codigo_ips, ips_primaria, codigo_servicio, descripcion_servicio,
                    diagnostico_principal, finalidad, payload_json
                )
                SELECT
                    event_date, tipo_identificacion, identificacion,
                    primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
                    fecha_nacimiento, edad, edad_meses, rango_edad,
                    codigo_ips, ips_primaria, codigo_servicio, descripcion_servicio,
                    diagnostico_principal, finalidad, payload_json
                FROM ' + QUOTENAME(PARSENAME(@source_object, 2)) + N'.' + QUOTENAME(PARSENAME(@source_object, 1)) + N'
                WHERE event_date >= @p_from
                  AND event_date <  @p_to;';

            EXEC sp_executesql
                @sql,
                N'@p_from date, @p_to date',
                @p_from = @from_date,
                @p_to = @to_date;
        END
        ELSE IF UPPER(@source_type) = 'PROC'
        BEGIN
            SET @sql = N'
                INSERT INTO #cv_source (
                    event_date, tipo_identificacion, identificacion,
                    primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
                    fecha_nacimiento, edad, edad_meses, rango_edad,
                    codigo_ips, ips_primaria, codigo_servicio, descripcion_servicio,
                    diagnostico_principal, finalidad, payload_json
                )
                EXEC ' + @source_object + N' @p_from, @p_to;';

            EXEC sp_executesql
                @sql,
                N'@p_from date, @p_to date',
                @p_from = @from_date,
                @p_to = @to_date;
        END
        ELSE
        BEGIN
            RAISERROR('source_type no soportado. Usa VIEW o PROC.', 16, 1);
        END;

        BEGIN TRANSACTION;

            DELETE FROM dbo.ciclo_vida_cache_records
            WHERE course_key = @course_key
              AND module_key = @module_key
              AND (
                    (event_date >= @from_date AND event_date < @to_date)
                    OR (event_date IS NULL AND range_start = @from_date AND range_end = @to_date)
                  );

            DELETE FROM dbo.ciclo_vida_cache_summaries
            WHERE course_key = @course_key
              AND module_key = @module_key
              AND range_start = @from_date
              AND range_end = @to_date;

            ;WITH src AS (
                SELECT
                    @course_key AS course_key,
                    @module_key AS module_key,
                    @module_label AS module_label,
                    @record_type AS record_type,
                    @from_date AS range_start,
                    @to_date AS range_end,
                    s.event_date,
                    NULLIF(LTRIM(RTRIM(s.tipo_identificacion)), '') AS tipo_identificacion,
                    NULLIF(LTRIM(RTRIM(s.identificacion)), '') AS identificacion,
                    NULLIF(LTRIM(RTRIM(s.primer_nombre)), '') AS primer_nombre,
                    NULLIF(LTRIM(RTRIM(s.segundo_nombre)), '') AS segundo_nombre,
                    NULLIF(LTRIM(RTRIM(s.primer_apellido)), '') AS primer_apellido,
                    NULLIF(LTRIM(RTRIM(s.segundo_apellido)), '') AS segundo_apellido,
                    s.fecha_nacimiento,
                    s.edad,
                    s.edad_meses,
                    COALESCE(NULLIF(LTRIM(RTRIM(s.rango_edad)), ''), dbo.fn_cv_rango_edad(s.edad)) AS rango_edad,
                    NULLIF(LTRIM(RTRIM(s.codigo_ips)), '') AS codigo_ips,
                    NULLIF(LTRIM(RTRIM(s.ips_primaria)), '') AS ips_primaria,
                    NULLIF(LTRIM(RTRIM(s.codigo_servicio)), '') AS codigo_servicio,
                    NULLIF(LTRIM(RTRIM(s.descripcion_servicio)), '') AS descripcion_servicio,
                    NULLIF(LTRIM(RTRIM(s.diagnostico_principal)), '') AS diagnostico_principal,
                    NULLIF(LTRIM(RTRIM(s.finalidad)), '') AS finalidad,
                    CONVERT(varchar(64), HASHBYTES(
                        'SHA2_256',
                        CONCAT(
                            @course_key, '|',
                            @module_key, '|',
                            COALESCE(s.tipo_identificacion, ''), '|',
                            COALESCE(s.identificacion, ''), '|',
                            CONVERT(varchar(10), s.event_date, 23), '|',
                            COALESCE(s.codigo_servicio, ''), '|',
                            COALESCE(s.descripcion_servicio, ''), '|',
                            COALESCE(s.diagnostico_principal, '')
                        )
                    ), 2) AS record_hash,
                    s.payload_json,
                    @run_id AS source_run_id
                FROM #cv_source s
            )
            INSERT INTO dbo.ciclo_vida_cache_records (
                course_key,
                module_key,
                module_label,
                record_type,
                range_start,
                range_end,
                event_date,
                tipo_identificacion,
                identificacion,
                primer_nombre,
                segundo_nombre,
                primer_apellido,
                segundo_apellido,
                fecha_nacimiento,
                edad,
                edad_meses,
                rango_edad,
                codigo_ips,
                ips_primaria,
                codigo_servicio,
                descripcion_servicio,
                diagnostico_principal,
                finalidad,
                record_hash,
                payload,
                source_run_id,
                created_at,
                updated_at
            )
            SELECT
                course_key,
                module_key,
                module_label,
                record_type,
                range_start,
                range_end,
                event_date,
                tipo_identificacion,
                identificacion,
                primer_nombre,
                segundo_nombre,
                primer_apellido,
                segundo_apellido,
                fecha_nacimiento,
                edad,
                edad_meses,
                rango_edad,
                codigo_ips,
                ips_primaria,
                codigo_servicio,
                descripcion_servicio,
                diagnostico_principal,
                finalidad,
                record_hash,
                payload_json,
                source_run_id,
                SYSDATETIME(),
                SYSDATETIME()
            FROM src;

            SET @records_loaded = @@ROWCOUNT;

            INSERT INTO dbo.ciclo_vida_cache_summaries (
                course_key,
                module_key,
                range_start,
                range_end,
                total_records,
                unique_patients,
                unique_ips,
                unique_services,
                metadata,
                created_at,
                updated_at
            )
            SELECT
                @course_key,
                @module_key,
                @from_date,
                @to_date,
                COUNT(1),
                COUNT(DISTINCT CONCAT(COALESCE(tipo_identificacion, ''), '|', COALESCE(identificacion, ''))),
                COUNT(DISTINCT COALESCE(NULLIF(ips_primaria, ''), '#SIN_IPS')),
                COUNT(DISTINCT CONCAT(COALESCE(codigo_servicio, ''), '|', COALESCE(descripcion_servicio, ''))),
                CONCAT(
                    '{"source_run_id":', @run_id,
                    ',"generated_at":"', CONVERT(varchar(19), SYSDATETIME(), 126), '"}'
                ),
                SYSDATETIME(),
                SYSDATETIME()
            FROM dbo.ciclo_vida_cache_records
            WHERE course_key = @course_key
              AND module_key = @module_key
              AND range_start = @from_date
              AND range_end = @to_date;

        COMMIT TRANSACTION;

        UPDATE dbo.ciclo_vida_cache_runs
        SET
            status = 'success',
            records_loaded = @records_loaded,
            finished_at = SYSDATETIME(),
            updated_at = SYSDATETIME()
        WHERE id = @run_id;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        UPDATE dbo.ciclo_vida_cache_runs
        SET
            status = 'failed',
            finished_at = SYSDATETIME(),
            error_message = LEFT(ERROR_MESSAGE(), 65000),
            updated_at = SYSDATETIME()
        WHERE id = @run_id;

        THROW;
    END CATCH
END;
GO

CREATE OR ALTER PROCEDURE dbo.sp_cv_backfill_course
    @course_key varchar(80),
    @from_date  date,
    @to_date    date,
    @chunk      varchar(20) = 'quarter', -- month | quarter | year
    @step       int = 1,
    @resume     bit = 1
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @cursor_from date = @from_date;
    DECLARE @cursor_to date;

    IF @to_date <= @from_date
    BEGIN
        RAISERROR('@to_date debe ser mayor que @from_date.', 16, 1);
        RETURN;
    END;

    WHILE @cursor_from < @to_date
    BEGIN
        SET @cursor_to = CASE LOWER(@chunk)
            WHEN 'month' THEN DATEADD(MONTH, @step, @cursor_from)
            WHEN 'quarter' THEN DATEADD(MONTH, @step * 3, @cursor_from)
            WHEN 'year' THEN DATEADD(YEAR, @step, @cursor_from)
            ELSE NULL
        END;

        IF @cursor_to IS NULL
        BEGIN
            RAISERROR('@chunk debe ser month, quarter o year.', 16, 1);
            RETURN;
        END;

        IF @cursor_to > @to_date
            SET @cursor_to = @to_date;

        DECLARE module_cursor CURSOR LOCAL FAST_FORWARD FOR
            SELECT module_key
            FROM dbo.cv_sql_module_config
            WHERE course_key = @course_key
              AND is_enabled = 1
            ORDER BY module_key;

        DECLARE @module_key varchar(80);

        OPEN module_cursor;
        FETCH NEXT FROM module_cursor INTO @module_key;

        WHILE @@FETCH_STATUS = 0
        BEGIN
            IF @resume = 1
               AND EXISTS (
                    SELECT 1
                    FROM dbo.ciclo_vida_cache_runs
                    WHERE course_key = @course_key
                      AND module_key = @module_key
                      AND range_start = @cursor_from
                      AND range_end = @cursor_to
                      AND status IN ('success', 'running')
               )
            BEGIN
                FETCH NEXT FROM module_cursor INTO @module_key;
                CONTINUE;
            END;

            EXEC dbo.sp_cv_refresh_module_window
                @course_key = @course_key,
                @module_key = @module_key,
                @from_date = @cursor_from,
                @to_date = @cursor_to;

            FETCH NEXT FROM module_cursor INTO @module_key;
        END;

        CLOSE module_cursor;
        DEALLOCATE module_cursor;

        SET @cursor_from = @cursor_to;
    END;
END;
GO

/*
    EJEMPLOS DE CONFIGURACION

    IMPORTANTE:
    Solo son ejemplos de como registrar los modulos cuando ya tengas creada
    la vista o procedimiento estandarizado.

    INSERT INTO dbo.cv_sql_module_config (course_key, module_key, module_label, record_type, source_type, source_object)
    VALUES
        ('primera_infancia', 'medica', 'Atencion en salud medica', 'event', 'VIEW', 'dbo.v_cv_primera_infancia_medica'),
        ('primera_infancia', 'enfermeria', 'Atencion por enfermeria', 'event', 'VIEW', 'dbo.v_cv_primera_infancia_enfermeria'),
        ('infancia', 'medica', 'Atencion en salud medica', 'event', 'VIEW', 'dbo.v_cv_infancia_medica'),
        ('adolescencia', 'planificacion_r202', 'Metodo de planificacion R202', 'event', 'VIEW', 'dbo.v_cv_adolescencia_planificacion_r202'),
        ('juventud', 'riesgo_cardiometabolico', 'Riesgo cardiovascular', 'event', 'VIEW', 'dbo.v_cv_juventud_riesgo_cardiometabolico'),
        ('adultez', 'mamografia', 'Mamografia', 'event', 'VIEW', 'dbo.v_cv_adultez_mamografia'),
        ('vejez', 'psa', 'PSA', 'event', 'VIEW', 'dbo.v_cv_vejez_psa');

    EJEMPLOS DE EJECUCION

    -- refrescar una ventana puntual
    EXEC dbo.sp_cv_refresh_module_window
        @course_key = 'primera_infancia',
        @module_key = 'medica',
        @from_date = '2025-01-01',
        @to_date = '2025-02-01';

    -- backfill historico trimestral
    EXEC dbo.sp_cv_backfill_course
        @course_key = 'primera_infancia',
        @from_date = '2020-01-01',
        @to_date = '2026-03-27',
        @chunk = 'quarter',
        @step = 1,
        @resume = 1;
*/

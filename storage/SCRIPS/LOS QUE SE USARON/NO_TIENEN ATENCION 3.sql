/* ========================= PARÁMETROS =========================
   - @Desde / @HastaExclusivo: ventana de calendario en la que buscas atenciones.
   - @Hoy: FECHA de referencia para calcular edad (hoy).
   - @AplicarFiltroEdad: 1 = filtrar por rango de edad; 0 = no filtrar.
   - @EdadMinAnios/@EdadMaxAnios: rango de edad (en años) a incluir (ej. 0–5).
================================================================= */
DECLARE @Desde date = '2025-01-01';
DECLARE @HastaExclusivo date = '2025-02-10';
DECLARE @Hoy date = CAST(GETDATE() AS date);

DECLARE @AplicarFiltroEdad bit   = 1;  -- 1 = activo, 0 = inactivo
DECLARE @EdadMinAnios    int     = 0;
DECLARE @EdadMaxAnios    int     = 5;

/* =========================
   AFILIADOS
   - Normaliza fechaNacimiento.
   - Trae nombres e IPS primaria.
   - (Opcional) Filtra por edad actual en AÑOS: 0–5.
   - La edad en AÑOS se calcula de forma "segura" (evita sumar 1 si aún no cumple).
========================= */
WITH Afiliados AS (
    SELECT
		afi.numeroCarnet,
        afi.tipoIdentificacion,
        afi.identificacion,
        afi.primerNombre,
        afi.segundoNombre,
        afi.primerApellido,
        afi.segundoApellido,
        -- Normalizamos fechaNacimiento intentando varios formatos
        COALESCE(
            TRY_CONVERT(date, afi.fechaNacimiento, 23),  -- yyyy-mm-dd
            TRY_CONVERT(date, afi.fechaNacimiento, 103), -- dd/mm/yyyy
            TRY_CONVERT(date, afi.fechaNacimiento, 120), -- yyyy-mm-dd hh:mi:ss
            TRY_CONVERT(date, afi.fechaNacimiento, 121)  -- ODBC canonical
        ) AS fechaNacimiento,
        h.descrip AS ips_Prim,
		i.codigo as codigoHabilitacion
    FROM sga..maestroafiliados afi
    --INNER JOIN sga..maestroidentificaciones X ON X.numeroCarnet = afi.numeroCarnet
    LEFT JOIN sga..maestroIps g  ON afi.numeroCarnet = g.numeroCarnet
    LEFT JOIN sga..maestroIpsGru h ON g.idGrupoIps = h.id
	LEFT JOIN sga..maestroipsgrudet i ON h.id = i.idd AND i.servicio=1
    WHERE
        -- Asegura que pudimos convertir fechaNacimiento
        COALESCE(
            TRY_CONVERT(date, afi.fechaNacimiento, 23),
            TRY_CONVERT(date, afi.fechaNacimiento, 103),
            TRY_CONVERT(date, afi.fechaNacimiento, 120),
            TRY_CONVERT(date, afi.fechaNacimiento, 121)
        ) IS NOT NULL

        -- ================== FILTRO DE EDAD 0–5 (OPCIONAL) ==================
        AND (
            @AplicarFiltroEdad = 0
            OR (
                -- Edad actual "segura" en AÑOS entre @EdadMinAnios y @EdadMaxAnios
                (
                  DATEDIFF(YEAR, 
                           COALESCE(
                               TRY_CONVERT(date, afi.fechaNacimiento, 23),
                               TRY_CONVERT(date, afi.fechaNacimiento, 103),
                               TRY_CONVERT(date, afi.fechaNacimiento, 120),
                               TRY_CONVERT(date, afi.fechaNacimiento, 121)
                           ),
                           @Hoy
                  )
                  - CASE 
                        WHEN DATEADD(
                                YEAR, 
                                DATEDIFF(YEAR, 
                                         COALESCE(
                                             TRY_CONVERT(date, afi.fechaNacimiento, 23),
                                             TRY_CONVERT(date, afi.fechaNacimiento, 103),
                                             TRY_CONVERT(date, afi.fechaNacimiento, 120),
                                             TRY_CONVERT(date, afi.fechaNacimiento, 121)
                                         ), 
                                         @Hoy
                                ), 
                                COALESCE(
                                    TRY_CONVERT(date, afi.fechaNacimiento, 23),
                                    TRY_CONVERT(date, afi.fechaNacimiento, 103),
                                    TRY_CONVERT(date, afi.fechaNacimiento, 120),
                                    TRY_CONVERT(date, afi.fechaNacimiento, 121)
                                )
                           ) > @Hoy 
                      THEN 1 ELSE 0 END
                ) BETWEEN @EdadMinAnios AND @EdadMaxAnios
            )
        )
),


/* =============================================================
   ATENCIONES POR CATEGORÍA (solo LLAVES para chequear existencia)
   -------------------------------------------------------------
   - Importante: se filtra por fechas @Desde–@HastaExclusivo y por
     códigos/diagnósticos/finalidad según tu regla.
   - El objetivo es que el NOT EXISTS sea rápido y directo.
============================================================== */

/* MEDICINA GENERAL (primera vez): ripsAC + ripsnAC */
AtnMG AS (
    SELECT id.numeroCarnet
    FROM sga..ripsAC A
	INNER JOIN sga..maestroidentificaciones id
        ON A.tipoidentificacion = id.tipoIdentificacion AND A.identificacion = id.identificacion
    WHERE A.codigoConsulta IN ('890201','890283','890263','890301','890383','890363','890101')
      AND A.diagnosticoPrincipal IN ('Z001','Z000','Z003')
      AND A.finalidadConsulta IN ('04','05','07')
      AND A.fechaConsulta >= @Desde AND A.fechaConsulta < @HastaExclusivo
    UNION
    SELECT id.numeroCarnet
    FROM sga..ripsnAC A	
    INNER JOIN sga..ripsnus U ON U.consecutivo = A.id
	INNER JOIN sga..maestroidentificaciones id
    ON U.tipoDocumentoIdentificacion = id.tipoIdentificacion AND U.numDocumentoIdentificacion = id.identificacion
    WHERE A.codConsulta IN ('890201','890283','890263','890301','890383','890363','890101')
      AND A.codDiagnosticoPrincipal IN ('Z001','Z000','Z003')
      AND A.fechaInicioAtencion >= @Desde AND A.fechaInicioAtencion < @HastaExclusivo
),

/* ENFERMERÍA (control/seguimiento): ripsAC + ripsnAC */
AtnENF AS (
    SELECT id.numeroCarnet
    FROM sga..ripsAC A
	INNER JOIN sga..maestroidentificaciones id
        ON A.tipoidentificacion = id.tipoIdentificacion AND A.identificacion = id.identificacion
    WHERE A.codigoConsulta IN ('890205','890305','890105')
      AND A.diagnosticoPrincipal IN ('Z001','Z000','Z003')
      AND A.finalidadConsulta IN ('04','05','07')
      AND A.fechaConsulta >= @Desde AND A.fechaConsulta < @HastaExclusivo
    UNION
    SELECT id.numeroCarnet
    FROM sga..ripsnAC A
    INNER JOIN sga..ripsnus U ON U.consecutivo = A.id
	INNER JOIN sga..maestroidentificaciones id
    ON U.tipoDocumentoIdentificacion = id.tipoIdentificacion AND U.numDocumentoIdentificacion = id.identificacion
    WHERE A.codConsulta IN ('890205','890305','890105')
      AND A.codDiagnosticoPrincipal IN ('Z001','Z000','Z003')
      AND A.fechaInicioAtencion >= @Desde AND A.fechaInicioAtencion < @HastaExclusivo
),

/* ODONTOLOGÍA GENERAL (control/seguimiento): ripsAC + ripsnAC */
AtnODONTO AS (
    SELECT id.numeroCarnet
    FROM sga..ripsAC A
	INNER JOIN sga..maestroidentificaciones id
        ON A.tipoidentificacion = id.tipoIdentificacion AND A.identificacion = id.identificacion
    WHERE A.codigoConsulta IN ('890203','890303','890103')
      AND A.diagnosticoPrincipal IN ('Z001','Z000','Z003','Z002','Z012')
      AND A.finalidadConsulta IN ('04','05','07')
      AND A.fechaConsulta >= @Desde AND A.fechaConsulta < @HastaExclusivo
    UNION
    SELECT id.numeroCarnet
    FROM sga..ripsnAC A
    INNER JOIN sga..ripsnus U ON U.consecutivo = A.id
	INNER JOIN sga..maestroidentificaciones id
    ON U.tipoDocumentoIdentificacion = id.tipoIdentificacion AND U.numDocumentoIdentificacion = id.identificacion
    WHERE A.codConsulta IN ('890203','890303','890103')
      AND A.codDiagnosticoPrincipal IN ('Z001','Z000','Z003','Z002','Z012')
      AND A.fechaInicioAtencion >= @Desde AND A.fechaInicioAtencion < @HastaExclusivo
),

/* TOPICACIÓN DE FLÚOR EN BARNIZ: ripsAP + ripsnAP (997106; Z00x; fin 03 en ripsAP) */
AtnFLUOR AS (
    SELECT id.numeroCarnet
    FROM sga..ripsAP A
	INNER JOIN sga..maestroidentificaciones id
        ON A.tipoidentificacion = id.tipoIdentificacion AND A.identificacion = id.identificacion
    WHERE A.codigoProcedimiento = '997106'
      AND A.diagnostico IN ('Z001','Z002','Z003')
      AND A.finalidadProcedimiento IN ('03')
      AND A.fechaProcedimiento >= @Desde AND A.fechaProcedimiento < @HastaExclusivo
    UNION
    SELECT id.numeroCarnet
    FROM sga..ripsnAP A
    INNER JOIN sga..ripsnus U ON U.consecutivo = A.id
	INNER JOIN sga..maestroidentificaciones id
    ON U.tipoDocumentoIdentificacion = id.tipoIdentificacion AND U.numDocumentoIdentificacion = id.identificacion
    WHERE A.codProcedimiento = '997106'
      AND A.codDiagnosticoPrincipal IN ('Z001','Z002','Z003')
      AND A.fechaInicioAtencion >= @Desde AND A.fechaInicioAtencion < @HastaExclusivo
),

/* CONTROL DE PLACA DENTAL: ripsAP + ripsnAP (997002; Z00x; fin 03 en ripsAP) */
AtnPLACA AS (
    SELECT id.numeroCarnet
    FROM sga..ripsAP A
	INNER JOIN sga..maestroidentificaciones id
        ON A.tipoidentificacion = id.tipoIdentificacion AND A.identificacion = id.identificacion
    WHERE A.codigoProcedimiento = '997002'
      AND A.diagnostico IN ('Z001','Z002','Z003')
      AND A.finalidadProcedimiento IN ('03')
      AND A.fechaProcedimiento >= @Desde AND A.fechaProcedimiento < @HastaExclusivo
    UNION
    SELECT id.numeroCarnet
    FROM sga..ripsnAP A
    INNER JOIN sga..ripsnus U ON U.consecutivo = A.id
	INNER JOIN sga..maestroidentificaciones id
    ON U.tipoDocumentoIdentificacion = id.tipoIdentificacion AND U.numDocumentoIdentificacion = id.identificacion
    WHERE A.codProcedimiento = '997002'
      AND A.codDiagnosticoPrincipal IN ('Z001','Z002','Z003')
      AND A.fechaInicioAtencion >= @Desde AND A.fechaInicioAtencion < @HastaExclusivo
),

/* APLICACIÓN DE SELLANTES: ripsAP + ripsnAP (997107; Z00x; fin 03 en ripsAP) */
AtnSELLANTES AS (
    SELECT id.numeroCarnet
    FROM sga..ripsAP A
	INNER JOIN sga..maestroidentificaciones id
        ON A.tipoidentificacion = id.tipoIdentificacion AND A.identificacion = id.identificacion
    WHERE A.codigoProcedimiento = '997107'
      AND A.diagnostico IN ('Z001','Z002','Z003')
      AND A.finalidadProcedimiento IN ('03')
      AND A.fechaProcedimiento >= @Desde AND A.fechaProcedimiento < @HastaExclusivo
    UNION
    SELECT id.numeroCarnet
    FROM sga..ripsnAP A
    INNER JOIN sga..ripsnus U ON U.consecutivo = A.id
	INNER JOIN sga..maestroidentificaciones id
    ON U.tipoDocumentoIdentificacion = id.tipoIdentificacion AND U.numDocumentoIdentificacion = id.identificacion
    WHERE A.codProcedimiento = '997107'
      AND A.codDiagnosticoPrincipal IN ('Z001','Z002','Z003')
      AND A.fechaInicioAtencion >= @Desde AND A.fechaInicioAtencion < @HastaExclusivo
)

/* =============================================================
   RESULTADO: ¿QUÉ ATENCIÓN LE HACE FALTA A CADA AFILIADO?
   -------------------------------------------------------------
   - Para cada categoría, hacemos NOT EXISTS contra su CTE.
   - Devolvemos TODOS los campos del afiliado + la etiqueta en 'descrip'.
============================================================== */

SELECT
    af.tipoIdentificacion,
    af.identificacion,
    af.primerNombre, af.segundoNombre, af.primerApellido, af.segundoApellido,
    af.fechaNacimiento,
    /* Edad actual en AÑOS (seguro) */
    DATEDIFF(YEAR, af.fechaNacimiento, @Hoy) 
      - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, af.fechaNacimiento, @Hoy), af.fechaNacimiento) > @Hoy THEN 1 ELSE 0 END AS edadAnios,
    /* Edad actual en MESES (útil para cortes finos) */
    DATEDIFF(MONTH, af.fechaNacimiento, @Hoy) AS edadMeses,
    af.ips_Prim, af.codigoHabilitacion,
    'CONSULTA DE PRIMERA VEZ POR MEDICINA GENERAL' AS descrip
FROM Afiliados af
WHERE NOT EXISTS (
    SELECT 1 FROM AtnMG x 
    WHERE  x.numeroCarnet = af.numeroCarnet
)

UNION ALL
SELECT
    af.tipoIdentificacion,
    af.identificacion,
    af.primerNombre, af.segundoNombre, af.primerApellido, af.segundoApellido,
    af.fechaNacimiento,
    DATEDIFF(YEAR, af.fechaNacimiento, @Hoy) 
      - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, af.fechaNacimiento, @Hoy), af.fechaNacimiento) > @Hoy THEN 1 ELSE 0 END,
    DATEDIFF(MONTH, af.fechaNacimiento, @Hoy),
    af.ips_Prim, af.codigoHabilitacion,
    'CONSULTA DE CONTROL O DE SEGUIMIENTO POR ENFERMERÍA' AS descrip
FROM Afiliados af
WHERE NOT EXISTS (
    SELECT 1 FROM AtnENF x 
    WHERE x.numeroCarnet = af.numeroCarnet
)

UNION ALL
SELECT
    af.tipoIdentificacion,
    af.identificacion,
    af.primerNombre, af.segundoNombre, af.primerApellido, af.segundoApellido,
    af.fechaNacimiento,
    DATEDIFF(YEAR, af.fechaNacimiento, @Hoy) 
      - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, af.fechaNacimiento, @Hoy), af.fechaNacimiento) > @Hoy THEN 1 ELSE 0 END,
    DATEDIFF(MONTH, af.fechaNacimiento, @Hoy),
    af.ips_Prim, af.codigoHabilitacion,
    'CONSULTA DE CONTROL O DE SEGUIMIENTO POR ODONTOLOGÍA GENERAL' AS descrip
FROM Afiliados af
WHERE NOT EXISTS (
    SELECT 1 FROM AtnODONTO x 
    WHERE x.numeroCarnet = af.numeroCarnet
)

UNION ALL
SELECT
    af.tipoIdentificacion,
    af.identificacion,
    af.primerNombre, af.segundoNombre, af.primerApellido, af.segundoApellido,
    af.fechaNacimiento,
    DATEDIFF(YEAR, af.fechaNacimiento, @Hoy) 
      - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, af.fechaNacimiento, @Hoy), af.fechaNacimiento) > @Hoy THEN 1 ELSE 0 END,
    DATEDIFF(MONTH, af.fechaNacimiento, @Hoy),
    af.ips_Prim, af.codigoHabilitacion,
    'TOPICACIÓN DE FLÚOR EN BARNIZ' AS descrip
FROM Afiliados af
WHERE NOT EXISTS (
    SELECT 1 FROM AtnFLUOR x 
    WHERE x.numeroCarnet = af.numeroCarnet
)

UNION ALL
SELECT
    af.tipoIdentificacion,
    af.identificacion,
    af.primerNombre, af.segundoNombre, af.primerApellido, af.segundoApellido,
    af.fechaNacimiento,
    DATEDIFF(YEAR, af.fechaNacimiento, @Hoy) 
      - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, af.fechaNacimiento, @Hoy), af.fechaNacimiento) > @Hoy THEN 1 ELSE 0 END,
    DATEDIFF(MONTH, af.fechaNacimiento, @Hoy),
    af.ips_Prim, af.codigoHabilitacion,
    'CONTROL DE PLACA DENTAL' AS descrip
FROM Afiliados af
WHERE NOT EXISTS (
    SELECT 1 FROM AtnPLACA x 
    WHERE x.numeroCarnet = af.numeroCarnet
)

UNION ALL
SELECT
    af.tipoIdentificacion,
    af.identificacion,
    af.primerNombre, af.segundoNombre, af.primerApellido, af.segundoApellido,
    af.fechaNacimiento,
    DATEDIFF(YEAR, af.fechaNacimiento, @Hoy) 
      - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, af.fechaNacimiento, @Hoy), af.fechaNacimiento) > @Hoy THEN 1 ELSE 0 END,
    DATEDIFF(MONTH, af.fechaNacimiento, @Hoy),
    af.ips_Prim, af.codigoHabilitacion,

    'APLICACIÓN DE SELLANTES' AS descrip
FROM Afiliados af
WHERE NOT EXISTS (
    SELECT 1 FROM AtnSELLANTES x 
    WHERE x.numeroCarnet = af.numeroCarnet
)


ORDER BY
    primerApellido, segundoApellido, primerNombre, segundoNombre, descrip;

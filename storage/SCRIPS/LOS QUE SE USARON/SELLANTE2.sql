/* =====================================================================================================
   REPORTE DE ATENCIONES FALTANTES POR VENTANAS DE EDAD (SQL Server)
   Autor: Adaptado para Anas Wayuu EPSI (Juan)

   DESCRIPCIÓN
   - Compara fechaNacimiento vs fecha de atención y valida ventanas de edad por servicio.
   - Devuelve SOLO ventanas que NO tienen atención (faltantes) dentro del período [@Desde, @HastaExclusivo).

   COBERTURAS
   * MEDICINA GENERAL (primera vez): 1m, 4–5m, 12–17m, 24–29m, 36m, 60m
   * ENFERMERÍA (control/seguimiento): 2–3m, 6–8m, 9–11m, 18–23m, 30–35m, 48m
   * ODONTOLOGÍA (control/seguimiento): desde 6m, 1 vez/año (periodos anuales)
   * TOPICACIÓN DE FLÚOR EN BARNIZ: 0–5m (1 vez) y, desde 5 años, 2 veces/año (semestres)
   * CONTROL DE PLACA DENTAL: igual que flúor (0–5m y desde 5 años, 2/año)  [CUPS 997002]
   * APLICACIÓN DE SELLANTES: desde 3 años (?36m), ?1 vez  [CUPS 997107 + Z00x + finalidad 03 en ripsAP]

   CÓMO AMPLIAR
   - Agrega CUPS/reglas en CTE "Atenciones" (CASE de CategoriaServicio).
   - Agrega ventanas en "VentanasBaseFijas" o crea CTEs recurrentes (anuales/semestrales).
===================================================================================================== */

DECLARE @Desde date = '2025-01-01';
DECLARE @HastaExclusivo date = '2025-09-10';
DECLARE @Hoy date = CAST(GETDATE() AS date);

/* Tope de edad a medir (en meses). Déjalo grande para no truncar odonto en mayores. */
DECLARE @EdadMaxMeses int = 2000;

/* =========================================
   NORMALIZA AFILIADOS + fechaNacimiento
   ========================================= */
WITH Afiliados AS (
    SELECT 
        X.tipoIdentificacion,
        X.identificacion,
        X.numeroCarnet,
        afi.primerNombre,
        afi.segundoNombre,
        afi.primerApellido,
        afi.segundoApellido,
        COALESCE(
            TRY_CONVERT(date, afi.fechaNacimiento, 23),  -- yyyy-mm-dd
            TRY_CONVERT(date, afi.fechaNacimiento, 103), -- dd/mm/yyyy
            TRY_CONVERT(date, afi.fechaNacimiento, 120), -- yyyy-mm-dd hh:mi:ss
            TRY_CONVERT(date, afi.fechaNacimiento, 121)  -- ODBC canonical
        ) AS fechaNacimiento,
        h.descrip AS ips_Prim
    FROM sga..maestroidentificaciones X
    INNER JOIN sga..maestroafiliados afi 
        ON X.numeroCarnet = afi.numeroCarnet
    LEFT JOIN sga..maestroIps g
        ON X.numeroCarnet = g.numeroCarnet
    LEFT JOIN sga..maestroIpsGru h
        ON g.idGrupoIps = h.id
),

/* =========================================
   UNIFICA Y CLASIFICA ATENCIONES (CONS/PROC)
   -> CategoriaServicio: MED_GEN_PRIMERA, ENF_CONTROL, ODONTO_CONTROL,
                         TOPIC_FLUOR, CONTROL_PLACA, SELLANTES
   ========================================= */
Atenciones AS (
    /* ripsAC (consultas ambulatorias) */
    SELECT
        A.tipoidentificacion       AS tipoIdentificacion,
        A.identificacion           AS identificacion,
        CAST(A.fechaConsulta AS date) AS fechaAtencion,
        A.codigoConsulta           AS codigo,
        B.descrip                  AS descrip,
        A.diagnosticoPrincipal     AS diag,
        A.finalidadConsulta        AS finalidad,
        CASE 
            WHEN A.codigoConsulta IN ('890201','890283','890263','890301','890383','890363','890101')
                 AND A.diagnosticoPrincipal IN ('Z001','Z000','Z003')
                 AND A.finalidadConsulta IN ('04','05','07')     THEN 'MED_GEN_PRIMERA'
            WHEN A.codigoConsulta IN ('890203','890303','890103')
                 AND A.diagnosticoPrincipal IN ('Z001','Z000','Z003','Z002','Z012')
                 AND A.finalidadConsulta IN ('04','05','07')     THEN 'ODONTO_CONTROL'
            WHEN A.codigoConsulta IN ('890205','890305','890105')
                 AND A.diagnosticoPrincipal IN ('Z001','Z000','Z003')
                 AND A.finalidadConsulta IN ('04','05','07')     THEN 'ENF_CONTROL'
            ELSE NULL
        END AS CategoriaServicio
    FROM sga..ripsAC A
    INNER JOIN sga..refCups B ON A.codigoConsulta = B.codigo
    WHERE A.fechaConsulta >= @Desde AND A.fechaConsulta < @HastaExclusivo

    UNION ALL

    /* ripsnAC (consultas no POS) */
    SELECT
        U.tipoDocumentoIdentificacion AS tipoIdentificacion,
        U.numDocumentoIdentificacion  AS identificacion,
        CAST(A.fechaInicioAtencion AS date) AS fechaAtencion,
        A.codConsulta                    AS codigo,
        B.descrip                        AS descrip,
        A.codDiagnosticoPrincipal        AS diag,
        NULL                             AS finalidad,
        CASE 
            WHEN A.codConsulta IN ('890201','890283','890263','890301','890383','890363','890101')
                 AND A.codDiagnosticoPrincipal IN ('Z001','Z000','Z003')      THEN 'MED_GEN_PRIMERA'
            WHEN A.codConsulta IN ('890203','890303','890103')
                 AND A.codDiagnosticoPrincipal IN ('Z001','Z000','Z003','Z002','Z012') THEN 'ODONTO_CONTROL'
            WHEN A.codConsulta IN ('890205','890305','890105')
                 AND A.codDiagnosticoPrincipal IN ('Z001','Z000','Z003')      THEN 'ENF_CONTROL'
            ELSE NULL
        END AS CategoriaServicio
    FROM sga..ripsnAC A
    INNER JOIN sga..ripsnus U ON U.consecutivo = A.id
    INNER JOIN sga..refCups B ON A.codConsulta = B.codigo
    WHERE A.fechaInicioAtencion >= @Desde AND A.fechaInicioAtencion < @HastaExclusivo

    UNION ALL

    /* ripsAP (procedimientos) */
    SELECT
        A.tipoidentificacion,
        A.identificacion,
        CAST(A.fechaProcedimiento AS date) AS fechaAtencion,
        A.codigoProcedimiento AS codigo,
        B.descrip             AS descrip,
        A.diagnostico         AS diag,
        A.finalidadProcedimiento AS finalidad,
        CASE
            /* TOPICACIÓN DE FLÚOR (preventivo) */
            WHEN A.codigoProcedimiento = '997106'
             AND A.diagnostico IN ('Z001','Z002','Z003')
             AND A.finalidadProcedimiento IN ('03')                THEN 'TOPIC_FLUOR'

            /* CONTROL DE PLACA (997002) con Z00x y finalidad 03, como en tu base */
            WHEN A.codigoProcedimiento = '997002'
             AND A.diagnostico IN ('Z001','Z002','Z003')
             AND A.finalidadProcedimiento IN ('03')                THEN 'CONTROL_PLACA'

            /* SELLANTES (997107) con Z00x y finalidad 03 */
            WHEN A.codigoProcedimiento = '997107'
             AND A.diagnostico IN ('Z001','Z002','Z003')
             AND A.finalidadProcedimiento IN ('03')                THEN 'SELLANTES'
            ELSE NULL
        END AS CategoriaServicio
    FROM sga..ripsAP A
    INNER JOIN sga..refCups B ON A.codigoProcedimiento = B.codigo
    WHERE A.fechaProcedimiento >= @Desde AND A.fechaProcedimiento < @HastaExclusivo

    UNION ALL

    /* ripsnAP (procedimientos no POS) */
    SELECT
        U.tipoDocumentoIdentificacion,
        U.numDocumentoIdentificacion,
        CAST(A.fechaInicioAtencion AS date) AS fechaAtencion,
        A.codProcedimiento AS codigo,
        B.descrip          AS descrip,
        A.codDiagnosticoPrincipal AS diag,
        NULL AS finalidad,
        CASE
            WHEN A.codProcedimiento = '997106'
             AND A.codDiagnosticoPrincipal IN ('Z001','Z002','Z003') THEN 'TOPIC_FLUOR'
            WHEN A.codProcedimiento = '997002'
             AND A.codDiagnosticoPrincipal IN ('Z001','Z002','Z003') THEN 'CONTROL_PLACA'
            WHEN A.codProcedimiento = '997107'
             AND A.codDiagnosticoPrincipal IN ('Z001','Z002','Z003') THEN 'SELLANTES'
            ELSE NULL
        END AS CategoriaServicio
    FROM sga..ripsnAP A
    INNER JOIN sga..ripsnus U ON U.consecutivo = A.id
    INNER JOIN sga..refCups B ON A.codProcedimiento = B.codigo
    WHERE A.fechaInicioAtencion >= @Desde AND A.fechaInicioAtencion < @HastaExclusivo
),

/* =========================================
   ATENCIONES + EDAD (meses al evento)
   ========================================= */
AtencionesConEdad AS (
    SELECT
        af.tipoIdentificacion,
        af.identificacion,
        af.numeroCarnet,
        af.primerNombre, af.segundoNombre, af.primerApellido, af.segundoApellido,
        af.ips_Prim,
        af.fechaNacimiento,
        a.fechaAtencion,
        a.codigo, a.descrip, a.diag, a.finalidad,
        a.CategoriaServicio,
        CASE WHEN af.fechaNacimiento IS NULL THEN NULL
             ELSE DATEDIFF(MONTH, af.fechaNacimiento, a.fechaAtencion)
        END AS edadMeses_Atencion
    FROM Atenciones a
    INNER JOIN Afiliados af
      ON af.tipoIdentificacion = a.tipoIdentificacion
     AND af.identificacion     = a.identificacion
    WHERE a.CategoriaServicio IS NOT NULL
),

/* =========================================
   TALLY para series (0..9999)
   ========================================= */
Nums AS (
  SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
  UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
), Tally AS (
  SELECT n0.n + 10*n1.n + 100*n2.n + 1000*n3.n AS n
  FROM Nums n0 CROSS JOIN Nums n1 CROSS JOIN Nums n2 CROSS JOIN Nums n3
),

/* =========================================
   VENTANAS FIJAS (minMes..maxMes)
   ========================================= */
VentanasBaseFijas AS (
    /* MEDICINA GENERAL (primera vez) */
    SELECT 'MED_GEN_PRIMERA' AS CategoriaServicio, '1 mes' AS Ventana, 1 AS MinMes, 1 AS MaxMes
    UNION ALL SELECT 'MED_GEN_PRIMERA','4-5 meses', 4, 5
    UNION ALL SELECT 'MED_GEN_PRIMERA','12-17 meses',12,17
    UNION ALL SELECT 'MED_GEN_PRIMERA','24-29 meses',24,29
    UNION ALL SELECT 'MED_GEN_PRIMERA','3 años',     36,36
    UNION ALL SELECT 'MED_GEN_PRIMERA','5 años',     60,60

    /* ENFERMERÍA (control/seguimiento) */
    UNION ALL SELECT 'ENF_CONTROL','2-3 meses', 2,3
    UNION ALL SELECT 'ENF_CONTROL','6-8 meses', 6,8
    UNION ALL SELECT 'ENF_CONTROL','9-11 meses',9,11
    UNION ALL SELECT 'ENF_CONTROL','18-23 meses',18,23
    UNION ALL SELECT 'ENF_CONTROL','30-35 meses',30,35
    UNION ALL SELECT 'ENF_CONTROL','4 años',   48,48

    /* TOPIC_FLUOR / CONTROL_PLACA: primer semestre de vida (0–5m) -> 1 vez */
    UNION ALL SELECT 'TOPIC_FLUOR','Primer semestre (0-5m)', 0,5
    UNION ALL SELECT 'CONTROL_PLACA','Primer semestre (0-5m)', 0,5

    /* SELLANTES: desde 3 años (al menos 1 vez en la vida) */
    UNION ALL SELECT 'SELLANTES','Desde 3 años (>=36m)', 36, @EdadMaxMeses
),

/* =========================================
   VENTANAS RECURRENTES
   - ODONTO_CONTROL: anual desde 6m  -> [6-17], [18-29], ...
   - TOPIC_FLUOR / CONTROL_PLACA: desde 60m -> 2/ año (semestres)
   ========================================= */
VentanasOdontoAnuales AS (
    SELECT 
      af.tipoIdentificacion, af.identificacion, af.fechaNacimiento,
      'ODONTO_CONTROL' AS CategoriaServicio,
      CONCAT('Anual desde 6m - Año ', t.n+1) AS Ventana,
      6 + 12*t.n  AS MinMes,
      17 + 12*t.n AS MaxMes
    FROM Afiliados af
    CROSS JOIN (SELECT n FROM Tally WHERE n < 200) t
    WHERE (6 + 12*t.n) <= @EdadMaxMeses
),
VentanasSemestrales_5Anios AS (
    /* TOPIC_FLUOR */
    SELECT 
      af.tipoIdentificacion, af.identificacion, af.fechaNacimiento,
      'TOPIC_FLUOR' AS CategoriaServicio,
      CONCAT('Semestre A (>=5 años) Y', t.n+1) AS Ventana,
      60 + 12*t.n  AS MinMes,
      65 + 12*t.n  AS MaxMes
    FROM Afiliados af
    CROSS JOIN (SELECT n FROM Tally WHERE n < 200) t
    WHERE (60 + 12*t.n) <= @EdadMaxMeses
    UNION ALL
    SELECT 
      af.tipoIdentificacion, af.identificacion, af.fechaNacimiento,
      'TOPIC_FLUOR' AS CategoriaServicio,
      CONCAT('Semestre B (>=5 años) Y', t.n+1) AS Ventana,
      66 + 12*t.n  AS MinMes,
      71 + 12*t.n  AS MaxMes
    FROM Afiliados af
    CROSS JOIN (SELECT n FROM Tally WHERE n < 200) t
    WHERE (66 + 12*t.n) <= @EdadMaxMeses

    /* CONTROL_PLACA */
    UNION ALL
    SELECT 
      af.tipoIdentificacion, af.identificacion, af.fechaNacimiento,
      'CONTROL_PLACA' AS CategoriaServicio,
      CONCAT('Semestre A (>=5 años) Y', t.n+1) AS Ventana,
      60 + 12*t.n  AS MinMes,
      65 + 12*t.n  AS MaxMes
    FROM Afiliados af
    CROSS JOIN (SELECT n FROM Tally WHERE n < 200) t
    WHERE (60 + 12*t.n) <= @EdadMaxMeses
    UNION ALL
    SELECT 
      af.tipoIdentificacion, af.identificacion, af.fechaNacimiento,
      'CONTROL_PLACA' AS CategoriaServicio,
      CONCAT('Semestre B (>=5 años) Y', t.n+1) AS Ventana,
      66 + 12*t.n  AS MinMes,
      71 + 12*t.n  AS MaxMes
    FROM Afiliados af
    CROSS JOIN (SELECT n FROM Tally WHERE n < 200) t
    WHERE (66 + 12*t.n) <= @EdadMaxMeses
),

/* =========================================
   CONSOLIDA VENTANAS (fijas + recurrentes)
   ========================================= */
Ventanas AS (
    /* Fijas (cartesian product con afiliados) */
    SELECT 
        af.tipoIdentificacion, af.identificacion, af.fechaNacimiento,
        v.CategoriaServicio, v.Ventana, v.MinMes, v.MaxMes
    FROM Afiliados af
    CROSS JOIN VentanasBaseFijas v
    UNION ALL
    /* Recurrentes: ya vienen por afiliado */
    SELECT tipoIdentificacion, identificacion, fechaNacimiento, CategoriaServicio, Ventana, MinMes, MaxMes
    FROM VentanasOdontoAnuales
    UNION ALL
    SELECT tipoIdentificacion, identificacion, fechaNacimiento, CategoriaServicio, Ventana, MinMes, MaxMes
    FROM VentanasSemestrales_5Anios
),

/* =========================================
   FECHA INI/FIN DE CADA VENTANA + recorte por período
   ========================================= */
VentanasFechadas AS (
    SELECT
        v.*,
        /* Edad actual en meses (para filtrar futuras si quieres) */
        DATEDIFF(MONTH, v.fechaNacimiento, @Hoy) AS edadMeses_Hoy,

        /* Inicio de ventana (seguro) */
        DATEADD(MONTH, v.MinMes, v.fechaNacimiento) AS FechaIniVentana,

        /* Fin de ventana (seguro):
           - Si v.MaxMes + 1 meses desde el nacimiento se pasa de 9999-12-31,
             cortamos el fin en 9999-12-31.
           - Si no, usamos el fin normal: último día del mes MaxMes.
        */
        CASE 
            WHEN DATEADD(MONTH, v.MaxMes + 1, v.fechaNacimiento) > CAST('9999-12-31' AS date)
                THEN CAST('9999-12-31' AS date)
            ELSE DATEADD(DAY, -1, DATEADD(MONTH, v.MaxMes + 1, v.fechaNacimiento))
        END AS FechaFinVentana
    FROM Ventanas v
    WHERE v.fechaNacimiento IS NOT NULL
      /* Asegura rangos razonables de meses */
      AND v.MinMes >= 0
      AND v.MinMes <= @EdadMaxMeses
      /* Quita la línea de abajo si quieres incluir ventanas futuras */
      AND v.MinMes <= DATEDIFF(MONTH, v.fechaNacimiento, @Hoy)
),
VentanasEnRango AS (
    /* Mantén solo las ventanas que se solapan con [@Desde, @HastaExclusivo) */
    SELECT *
    FROM VentanasFechadas
    WHERE FechaIniVentana < @HastaExclusivo
      AND FechaFinVentana >= @Desde
    /* Si quieres evaluar TODA la historia (sin cortar por período), comenta este WHERE */
),

/* =========================================
   ¿HAY AL MENOS 1 ATENCIÓN EN LA VENTANA?
   ========================================= */
MatchVentana AS (
    SELECT
        v.tipoIdentificacion, v.identificacion,
        v.fechaNacimiento,
        v.CategoriaServicio, v.Ventana, v.MinMes, v.MaxMes,
        v.FechaIniVentana, v.FechaFinVentana,
        v.edadMeses_Hoy,
        CASE WHEN EXISTS (
            SELECT 1
            FROM AtencionesConEdad a
            WHERE a.tipoIdentificacion = v.tipoIdentificacion
              AND a.identificacion     = v.identificacion
              AND a.CategoriaServicio  = v.CategoriaServicio
              AND a.fechaAtencion >= v.FechaIniVentana
              AND a.fechaAtencion <= v.FechaFinVentana
        ) THEN 1 ELSE 0 END AS TieneAtencion
    FROM VentanasEnRango v
)

/* =========================================
   RESULTADO FINAL: SOLO FALTANTES
   ========================================= */
SELECT
    m.CategoriaServicio,
    m.Ventana,
    m.MinMes, m.MaxMes,
    m.FechaIniVentana, m.FechaFinVentana,
    /* Afiliado */
    af.tipoIdentificacion,
    af.identificacion,
    af.primerNombre, af.segundoNombre, af.primerApellido, af.segundoApellido,
    af.ips_Prim,
    af.fechaNacimiento,
    /* Edad actual en años (cálculo seguro) */
    DATEDIFF(YEAR, af.fechaNacimiento, @Hoy) 
      - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, af.fechaNacimiento, @Hoy), af.fechaNacimiento) > @Hoy THEN 1 ELSE 0 END AS edadAnios,
    m.edadMeses_Hoy,
    /* Marca de faltante */
    1 AS FaltaAtencion
FROM MatchVentana m
INNER JOIN Afiliados af
  ON af.tipoIdentificacion = m.tipoIdentificacion
 AND af.identificacion     = m.identificacion
WHERE m.TieneAtencion = 0
ORDER BY
    af.primerApellido, af.segundoApellido, af.primerNombre, af.segundoNombre,
    m.CategoriaServicio,
    m.MinMes, m.MaxMes;

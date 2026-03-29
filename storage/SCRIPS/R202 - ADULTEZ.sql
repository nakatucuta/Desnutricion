SET NOCOUNT ON;

DECLARE @PeriodoIni VARCHAR(6) = CONVERT(VARCHAR(4), YEAR(@Desde))
    + RIGHT('00' + CONVERT(VARCHAR(2), MONTH(@Desde)), 2);
DECLARE @PeriodoFin VARCHAR(6) = CONVERT(VARCHAR(4), YEAR(@HastaExclusivo))
    + RIGHT('00' + CONVERT(VARCHAR(2), MONTH(@HastaExclusivo)), 2);

IF OBJECT_ID('tempdb..#r202_adultez_base') IS NOT NULL DROP TABLE #r202_adultez_base;
IF OBJECT_ID('tempdb..#r202_adultez_ids') IS NOT NULL DROP TABLE #r202_adultez_ids;

SELECT
    A.identificacion,
    B.periodo,
    NULLIF(TRY_CONVERT(date, A.fecConOdo), CAST('1845-01-01' AS date)) AS fecConOdoDate,
    NULLIF(TRY_CONVERT(date, A.citCerUte), CAST('1845-01-01' AS date)) AS citCerUteDate,
    NULLIF(TRY_CONVERT(date, A.fecTomPSA), CAST('1845-01-01' AS date)) AS fecTomPSADate,
    NULLIF(TRY_CONVERT(date, A.fecTacRec), CAST('1845-01-01' AS date)) AS fecTacRecDate,
    NULLIF(TRY_CONVERT(date, A.fecTamCaCol), CAST('1845-01-01' AS date)) AS fecTamCaColDate,
    NULLIF(TRY_CONVERT(date, A.fecTamCol), CAST('1845-01-01' AS date)) AS fecTamColDate,
    NULLIF(TRY_CONVERT(date, A.plaFamPvez), CAST('1845-01-01' AS date)) AS plaFamPvezDate,
    NULLIF(TRY_CONVERT(date, A.fecSumAnt), CAST('1845-01-01' AS date)) AS fecSumAntDate,
    NULLIF(TRY_CONVERT(date, A.fecPruVih), CAST('1845-01-01' AS date)) AS fecPruVihDate,
    NULLIF(TRY_CONVERT(date, A.fecAntHepB), CAST('1845-01-01' AS date)) AS fecAntHepBDate,
    NULLIF(TRY_CONVERT(date, A.fecTamHep), CAST('1845-01-01' AS date)) AS fecTamHepDate,
    NULLIF(LTRIM(RTRIM(CONVERT(VARCHAR(50), A.claRieCar))), '') AS claRieCarText,
    NULLIF(LTRIM(RTRIM(CONVERT(VARCHAR(50), A.sumAntc))), '') AS sumAntcText,
    A.conRnac,
    A.fecConOdo,
    A.claRieCar,
    A.citCerUte,
    A.fecTomPSA,
    A.fecTacRec,
    A.fecTamCaCol,
    A.fecTamCol,
    A.plaFamPvez,
    A.fecSumAnt,
    A.sumAntc,
    A.fecPruVih,
    A.fecAntHepB,
    A.fecTamHep
INTO #r202_adultez_base
FROM sga..maestroInfNominalR202EV B
INNER JOIN sga..maestroInfNominalR202 A
    ON A.id = B.id
WHERE B.periodo BETWEEN @PeriodoIni AND @PeriodoFin
  AND A.identificacion IS NOT NULL
  AND (
      NULLIF(TRY_CONVERT(date, A.fecConOdo), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.citCerUte), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecTomPSA), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecTacRec), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecTamCaCol), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecTamCol), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.plaFamPvez), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecSumAnt), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecPruVih), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecAntHepB), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecTamHep), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(LTRIM(RTRIM(CONVERT(VARCHAR(50), A.claRieCar))), '') IS NOT NULL
      OR NULLIF(LTRIM(RTRIM(CONVERT(VARCHAR(50), A.sumAntc))), '') IS NOT NULL
  );

CREATE INDEX IX_r202_adultez_base_identificacion
    ON #r202_adultez_base (identificacion);

;WITH ids AS (
    SELECT
        MX.tipoIdentificacion,
        MX.identificacion,
        MX.numeroCarnet,
        ROW_NUMBER() OVER (
            PARTITION BY MX.identificacion
            ORDER BY CASE MX.tipoIdentificacion
                WHEN 'CC' THEN 1
                WHEN 'CE' THEN 2
                WHEN 'PT' THEN 3
                WHEN 'PE' THEN 4
                WHEN 'PA' THEN 5
                WHEN 'RC' THEN 6
                WHEN 'TI' THEN 7
                ELSE 99
            END,
            MX.numeroCarnet DESC
        ) AS rn
    FROM sga..maestroidentificaciones MX
    INNER JOIN (
        SELECT DISTINCT identificacion
        FROM #r202_adultez_base
    ) base
        ON base.identificacion = MX.identificacion
)
SELECT
    tipoIdentificacion,
    identificacion,
    numeroCarnet
INTO #r202_adultez_ids
FROM ids
WHERE rn = 1;

CREATE UNIQUE CLUSTERED INDEX IX_r202_adultez_ids_identificacion
    ON #r202_adultez_ids (identificacion);

SELECT DISTINCT
    i.codigo AS codigoIps,
    X.tipoIdentificacion,
    X.identificacion,
    afi.primerNombre,
    afi.segundoNombre,
    afi.primerApellido,
    afi.segundoApellido,
    COALESCE(
        B.fecConOdoDate,
        B.citCerUteDate,
        B.fecTomPSADate,
        B.fecTacRecDate,
        B.fecTamCaColDate,
        B.fecTamColDate,
        B.plaFamPvezDate,
        B.fecSumAntDate,
        B.fecPruVihDate,
        B.fecAntHepBDate,
        B.fecTamHepDate,
        TRY_CONVERT(date, CONCAT(B.periodo, '01'))
    ) AS fechaConsulta,
    'R202 adultez' AS descrip,
    h.descrip AS ips_Prim,
    FN.fechaNacimientoDate AS fechaNacimiento,
    B.conRnac,
    B.fecConOdo,
    B.claRieCar,
    B.citCerUte,
    B.fecTomPSA,
    B.fecTacRec,
    B.fecTamCaCol,
    B.fecTamCol,
    B.plaFamPvez,
    B.fecSumAnt,
    B.sumAntc,
    B.fecPruVih,
    B.fecAntHepB,
    B.fecTamHep,
    B.periodo,
    i.codigo AS codigoHabilitacion
FROM #r202_adultez_base B
INNER JOIN #r202_adultez_ids X
    ON X.identificacion = B.identificacion
INNER JOIN sga..maestroAfiliados afi
    ON afi.numeroCarnet = X.numeroCarnet
CROSS APPLY (
    SELECT TRY_CONVERT(date, afi.fechaNacimiento) AS fechaNacimientoDate
) FN
LEFT JOIN sga..maestroIps g
    ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
    ON g.idGrupoIps = h.id
LEFT JOIN sga..maestroipsgrudet i
    ON h.id = i.idd
    AND i.servicio = 1
WHERE FN.fechaNacimientoDate IS NOT NULL;

DECLARE @PeriodoIni VARCHAR(6) = CONVERT(VARCHAR(4), YEAR(@Desde))
    + RIGHT('00' + CONVERT(VARCHAR(2), MONTH(@Desde)), 2);
DECLARE @PeriodoFin VARCHAR(6) = CONVERT(VARCHAR(4), YEAR(@HastaExclusivo))
    + RIGHT('00' + CONVERT(VARCHAR(2), MONTH(@HastaExclusivo)), 2);

SELECT DISTINCT
    i.codigo AS codigoIps,
    X.tipoIdentificacion,
    X.identificacion,
    afi.primerNombre,
    afi.segundoNombre,
    afi.primerApellido,
    afi.segundoApellido,
    COALESCE(
        NULLIF(TRY_CONVERT(date, A.fecConOdo), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.citCerUte), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecColp), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecBiopCerv), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecTomBiopSen), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecTomPSA), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecTacRec), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecTamCaCol), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecTamCol), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.plaFamPvez), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecSumAnt), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecPruVih), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecAntHepB), CAST('1845-01-01' AS date)),
        NULLIF(TRY_CONVERT(date, A.fecTamHep), CAST('1845-01-01' AS date)),
        TRY_CONVERT(date, CONCAT(B.periodo, '01'))
    ) AS fechaConsulta,
    'R202 vejez' AS descrip,
    h.descrip AS ips_Prim,
    TRY_CONVERT(date, afi.fechaNacimiento) AS fechaNacimiento,
    A.conRnac,
    A.fecConOdo,
    A.claRieCar,
    A.citCerUte,
    A.fecColp,
    A.fecBiopCerv,
    A.fecTomBiopSen,
    A.fecTomPSA,
    A.fecTacRec,
    A.fecTamCaCol,
    A.fecTamCol,
    A.plaFamPvez,
    A.fecSumAnt,
    A.sumAntc,
    A.fecPruVih,
    A.fecAntHepB,
    A.fecTamHep,
    B.periodo,
    i.codigo AS codigoHabilitacion
FROM sga..maestroInfNominalR202EV B
INNER JOIN sga..maestroInfNominalR202 A
    ON A.id = B.id
OUTER APPLY (
    SELECT TOP 1
        MX.tipoIdentificacion,
        MX.identificacion,
        MX.numeroCarnet
    FROM sga..maestroidentificaciones MX
    WHERE MX.identificacion = A.identificacion
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
) X
INNER JOIN sga..maestroAfiliados afi
    ON afi.numeroCarnet = X.numeroCarnet
LEFT JOIN sga..maestroIps g
    ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
    ON g.idGrupoIps = h.id
LEFT JOIN sga..maestroipsgrudet i
    ON h.id = i.idd
    AND i.servicio = 1
WHERE B.periodo BETWEEN @PeriodoIni AND @PeriodoFin
  AND X.identificacion IS NOT NULL
  AND TRY_CONVERT(date, afi.fechaNacimiento) IS NOT NULL
  AND (
      NULLIF(TRY_CONVERT(date, A.fecConOdo), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.citCerUte), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecColp), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecBiopCerv), CAST('1845-01-01' AS date)) IS NOT NULL
      OR NULLIF(TRY_CONVERT(date, A.fecTomBiopSen), CAST('1845-01-01' AS date)) IS NOT NULL
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

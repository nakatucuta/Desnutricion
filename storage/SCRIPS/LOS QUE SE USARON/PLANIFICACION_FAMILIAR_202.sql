SET NOCOUNT ON;

DECLARE @PeriodoIni VARCHAR(6) = CONVERT(VARCHAR(4), YEAR(@Desde))
    + RIGHT('00' + CONVERT(VARCHAR(2), MONTH(@Desde)), 2);
DECLARE @PeriodoFin VARCHAR(6) = CONVERT(VARCHAR(4), YEAR(@HastaExclusivo))
    + RIGHT('00' + CONVERT(VARCHAR(2), MONTH(@HastaExclusivo)), 2);

SELECT DISTINCT
    R1.codigoIpsPrimaria AS codigoIps,
    X.tipoIdentificacion AS tipoIdentificacion,
    X.identificacion AS identificacion,
    afi.primerNombre AS primerNombre,
    afi.segundoNombre AS segundoNombre,
    afi.primerApellido AS primerApellido,
    afi.segundoApellido AS segundoApellido,
    COALESCE(
        NULLIF(TRY_CONVERT(date, R1.plaFamPvez), CAST('1845-01-01' AS date)),
        TRY_CONVERT(date, CONCAT(R2.periodo, '01'))
    ) AS fechaConsulta,
    '' AS codigoConsulta,
    'Metodo de planificacion R202' AS descrip,
    '' AS finalidadConsulta,
    '' AS diagnosticoPrincipal,
    h.descrip AS ips_Prim,
    FN.fechaNac AS fechaNacimiento,
    i.codigo AS codigoHabilitacion
FROM sga..maestroInfNominalR202 R1
INNER JOIN sga..maestroInfNominalR202EV R2
    ON R1.id = R2.id
INNER JOIN sga..maestroidentificaciones X
    ON R1.tipoIdentificacion = X.tipoIdentificacion
   AND R1.identificacion = X.identificacion
INNER JOIN sga..maestroafiliados afi
    ON X.numeroCarnet = afi.numeroCarnet
LEFT JOIN sga..maestroIps g
    ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
    ON g.idGrupoIps = h.id
LEFT JOIN sga..maestroipsgrudet i
    ON h.id = i.idd
   AND i.servicio = 1
CROSS APPLY (
    SELECT COALESCE(
        TRY_CONVERT(date, afi.fechaNacimiento, 23),
        TRY_CONVERT(date, afi.fechaNacimiento, 103),
        TRY_CONVERT(date, afi.fechaNacimiento, 120),
        TRY_CONVERT(date, afi.fechaNacimiento, 121)
    ) AS fechaNac
) AS FN
WHERE R2.periodo BETWEEN @PeriodoIni AND @PeriodoFin
  AND R1.identificacion IS NOT NULL
  AND COALESCE(
      NULLIF(TRY_CONVERT(date, R1.plaFamPvez), CAST('1845-01-01' AS date)),
      TRY_CONVERT(date, CONCAT(R2.periodo, '01'))
  ) >= @Desde
  AND COALESCE(
      NULLIF(TRY_CONVERT(date, R1.plaFamPvez), CAST('1845-01-01' AS date)),
      TRY_CONVERT(date, CONCAT(R2.periodo, '01'))
  ) < @HastaExclusivo
  AND FN.fechaNac IS NOT NULL;

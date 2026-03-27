DECLARE @Desde date = '2025-01-01';
DECLARE @HastaExclusivo date = '2025-09-10';
DECLARE @Hoy date = CAST(GETDATE() AS date);
DECLARE @PeriodoIni VARCHAR(6) = CONVERT(VARCHAR(4), YEAR(@Desde)) 
	+ RIGHT('00' + CONVERT(VARCHAR(2), MONTH(@Desde)), 2);
DECLARE @PeriodoFin VARCHAR(6) = CONVERT(VARCHAR(4), YEAR(@HastaExclusivo)) 
	+ RIGHT('00' + CONVERT(VARCHAR(2), MONTH(@HastaExclusivo)), 2);

--  VARIABLE 53: Fecha de Atenci�n en Salud Para la Asesor�a en Anticoncepci�n  
-- ======== PRIMERA PARTE (R202) ========
SELECT 
    R1.codigoIpsPrimaria												AS codigoIps,
    Afi.tipoIdentificacion												AS tipoIdentificacion,
    Afi.identificacion													AS identificacion,
    afi.primerNombre													AS primerNombre,
    afi.segundoNombre													AS segundoNombre,
    afi.primerApellido													AS primerApellido,
    afi.segundoApellido													AS segundoApellido,
    CAST(
		CASE WHEN R1.plaFamPvez < '1900-01-01' 
		THEN CONCAT(R2.periodo, '01') ELSE R1.plaFamPvez END 
	AS date)															AS fechaConsulta,
    ''																	AS codigoConsulta,
    'Asesor�a en Anticoncepci�n'										AS descrip,
    ''																	AS finalidadConsulta,
    ''																	AS diagnosticoPrincipal,
    h.descrip															AS ips_Prim,
    afi.fechaNacimiento													AS fechaNacimiento,
    -- Edad segura
    CASE 
       WHEN FN.fechaNac IS NULL THEN NULL
       ELSE DATEDIFF(YEAR, FN.fechaNac, @Hoy) 
            - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, FN.fechaNac, @Hoy), FN.fechaNac) > @Hoy THEN 1 ELSE 0 END
    END AS edad,
    -- Rango de edad
    '0-5 a�os' AS rangoEdad
FROM sga..maestroInfNominalR202 R1
INNER JOIN sga..maestroInfNominalR202EV R2
 on R1.id=R2.id	
INNER JOIN sga..maestroidentificaciones X
    ON R1.tipoIdentificacion = X.tipoIdentificacion 
   AND R1.identificacion     = X.identificacion
INNER JOIN sga..maestroafiliados afi 
    ON X.numeroCarnet = afi.numeroCarnet
LEFT JOIN sga..maestroIps g
    ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
    ON g.idGrupoIps = h.id
CROSS APPLY (
    -- Normaliza fecha de nacimiento a DATE (4 formatos)
    SELECT COALESCE(
        TRY_CONVERT(date, afi.fechaNacimiento, 23),
        TRY_CONVERT(date, afi.fechaNacimiento, 103),
        TRY_CONVERT(date, afi.fechaNacimiento, 120),
        TRY_CONVERT(date, afi.fechaNacimiento, 121)
    ) AS fechaNac
) AS FN
WHERE    
    R2.periodo BETWEEN @PeriodoIni AND @PeriodoFin
    -- Filtros pedidos:
	AND cast(R1.plaFamPvez as date) BETWEEN @Desde AND @HastaExclusivo
    AND FN.fechaNac IS NOT NULL
    AND (
        DATEDIFF(YEAR, FN.fechaNac, @Hoy) 
        - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, FN.fechaNac, @Hoy), FN.fechaNac) > @Hoy THEN 1 ELSE 0 END
    ) BETWEEN 29 AND 59
ORDER BY Afi.numeroCarnet, fechaConsulta



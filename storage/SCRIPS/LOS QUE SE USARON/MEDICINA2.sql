DECLARE @Desde date = '2025-01-01';
DECLARE @HastaExclusivo date = '2025-09-10';
DECLARE @Hoy date = CAST(GETDATE() AS date);

-- ======== PRIMERA PARTE (ripsAC) ========
SELECT 
    A.codigoIps                                         AS codigoIps,
    X.tipoIdentificacion                                AS tipoIdentificacion,
    X.identificacion                                    AS identificacion,
    afi.primerNombre                                    AS primerNombre,
    afi.segundoNombre                                   AS segundoNombre,
    afi.primerApellido                                  AS primerApellido,
    afi.segundoApellido                                 AS segundoApellido,
    CAST(A.fechaConsulta AS date)                       AS fechaConsulta,
    A.codigoConsulta                                    AS codigoConsulta,
    B.descrip                                           AS descrip,
    A.finalidadConsulta                                 AS finalidadConsulta,
    A.diagnosticoPrincipal                              AS diagnosticoPrincipal,
    h.descrip                                           AS ips_Prim,
    afi.fechaNacimiento                                 AS fechaNacimiento,
    -- Edad segura
    CASE 
       WHEN FN.fechaNac IS NULL THEN NULL
       ELSE DATEDIFF(YEAR, FN.fechaNac, @Hoy) 
            - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, FN.fechaNac, @Hoy), FN.fechaNac) > @Hoy THEN 1 ELSE 0 END
    END AS edad,
    -- Rango de edad
    '0-5 años' AS rangoEdad
FROM sga..ripsAC A
INNER JOIN sga..maestroidentificaciones X
    ON A.tipoidentificacion = X.tipoIdentificacion 
   AND A.identificacion     = X.identificacion
INNER JOIN sga..refCups B
    ON A.codigoConsulta = B.codigo
LEFT JOIN sga..maestroIps g
    ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
    ON g.idGrupoIps = h.id
INNER JOIN sga..maestroafiliados afi 
    ON X.numeroCarnet = afi.numeroCarnet
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
    A.codigoConsulta IN ('890201','890283','890263','890301','890383','890363','890101')
    AND A.diagnosticoPrincipal IN ('Z001','Z000','Z003')
    AND A.finalidadConsulta IN ('04','05','07')
    AND A.fechaConsulta >= @Desde
    AND A.fechaConsulta <  @HastaExclusivo
    -- Filtros pedidos:
    AND FN.fechaNac IS NOT NULL
    AND (
        DATEDIFF(YEAR, FN.fechaNac, @Hoy) 
        - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, FN.fechaNac, @Hoy), FN.fechaNac) > @Hoy THEN 1 ELSE 0 END
    ) BETWEEN 0 AND 5

UNION ALL

-- ======== SEGUNDA PARTE (ripsnAC) ========
SELECT 
    A.codPrestador                                      AS codigoIps,
    U.tipoDocumentoIdentificacion                       AS tipoIdentificacion,
    U.numDocumentoIdentificacion                        AS identificacion,
    afi.primerNombre                                    AS primerNombre,
    afi.segundoNombre                                   AS segundoNombre,
    afi.primerApellido                                  AS primerApellido,
    afi.segundoApellido                                 AS segundoApellido,
    CAST(A.fechaInicioAtencion AS date)                 AS fechaConsulta,
    A.codConsulta                                       AS codigoConsulta,
    B.descrip                                           AS descrip,
    A.finalidadTecnologiaSalud                          AS finalidadConsulta,
    A.codDiagnosticoPrincipal                           AS diagnosticoPrincipal,
    h.descrip                                           AS ips_Prim,
    afi.fechaNacimiento                                  AS fechaNacimiento,
    -- Edad segura
    CASE 
       WHEN FN.fechaNac IS NULL THEN NULL
       ELSE DATEDIFF(YEAR, FN.fechaNac, @Hoy) 
            - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, FN.fechaNac, @Hoy), FN.fechaNac) > @Hoy THEN 1 ELSE 0 END
    END AS edad,
    -- Rango de edad
    '0-5 años' AS rangoEdad
FROM sga..ripsnAC A
INNER JOIN sga..ripsnus U
    ON U.consecutivo = A.id
INNER JOIN sga..maestroidentificaciones X
    ON U.tipoDocumentoIdentificacion = X.tipoidentificacion 
   AND U.numDocumentoIdentificacion  = X.identificacion
INNER JOIN sga..refCups B
    ON A.codConsulta = B.codigo
LEFT JOIN sga..maestroIps g
    ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
    ON g.idGrupoIps = h.id
INNER JOIN sga..maestroafiliados afi 
    ON X.numeroCarnet = afi.numeroCarnet
CROSS APPLY (
    SELECT COALESCE(
        TRY_CONVERT(date, afi.fechaNacimiento, 23),
        TRY_CONVERT(date, afi.fechaNacimiento, 103),
        TRY_CONVERT(date, afi.fechaNacimiento, 120),
        TRY_CONVERT(date, afi.fechaNacimiento, 121)
    ) AS fechaNac
) AS FN
WHERE 
    A.codConsulta IN ('890201','890283','890263','890301','890383','890363','890101')
    AND A.codDiagnosticoPrincipal IN ('Z001','Z000','Z003')
    --AND A.finalidadTecnologiaSalud IN ('04','05','07') -- descomentar si aplica
    AND A.fechaInicioAtencion >= @Desde
    AND A.fechaInicioAtencion <  @HastaExclusivo
    -- Filtros pedidos:
    AND FN.fechaNac IS NOT NULL
    AND (
        DATEDIFF(YEAR, FN.fechaNac, @Hoy) 
        - CASE WHEN DATEADD(YEAR, DATEDIFF(YEAR, FN.fechaNac, @Hoy), FN.fechaNac) > @Hoy THEN 1 ELSE 0 END
    ) BETWEEN 0 AND 5;

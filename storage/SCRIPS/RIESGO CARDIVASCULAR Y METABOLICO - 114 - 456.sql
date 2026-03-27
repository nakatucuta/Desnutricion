
--select * from SGA..ripsAP

SELECT 
codigoIps
,X.tipoIdentificacion
,X.Identificacion
,fechaProcedimiento
,codigoProcedimiento
,B.descrip
,finalidadProcedimiento
,diagnostico
, h.descrip as Ips_Prim
FROM SGA..ripsAP A
inner join sga..maestroidentificaciones X
ON A.tipoidentificacion = X.tipoIdentificacion AND A.identificacion = X.identificacion
inner join sga..refCups B
ON A.codigoProcedimiento=B.codigo
inner join sga..maestroidentificaciones c
ON A.tipoIdentificacion=C.tipoidentificacion AND A.identificacion=C.identificacion
left join sga..maestroIps g
ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
ON g.idGrupoIps = h.id

WHERE A.codigoProcedimiento IN ('907106','903841','903895','903868','903818','903815','903816') AND
a.diagnostico in ('Z000','Z003') AND
a.finalidadProcedimiento IN ('4') AND
a.fechaProcedimiento between '2025-01-01' and '2025-06-30'

UNION ALL

select 
 codPrestador
,U.tipoDocumentoIdentificacion
,U.numDocumentoIdentificacion
,fechaInicioAtencion
,codProcedimiento
,B.descrip
,finalidadTecnologiaSalud
,codDiagnosticoPrincipal
, h.descrip
from sga..ripsnAP A
inner join sga..ripsnus U
ON U.consecutivo = A.id
inner join sga..maestroidentificaciones X
ON U.tipoDocumentoIdentificacion=X.tipoidentificacion AND U.numDocumentoIdentificacion=X.identificacion
INNER JOIN 
sga..refCups B
ON A.codProcedimiento=B.codigo
left join sga..maestroIps g
ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
ON g.idGrupoIps = h.id

where codProcedimiento in ('907106','903841','903895','903868','903818','903815','903816')  and
coddiagnosticoPrincipal IN ('Z000','Z003') AND
--finalidadTecnologiaSalud IN ('03') AND
fechaInicioAtencion between '2025-01-01' and '2025-06-30'






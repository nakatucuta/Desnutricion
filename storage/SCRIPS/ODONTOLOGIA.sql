

select 
 codigoIps
,X.tipoIdentificacion
,X.identificacion
,fechaConsulta
,codigoConsulta
,B.descrip
,finalidadConsulta
,diagnosticoPrincipal
,h.descrip as ips_Prim
from sga..ripsAC A
inner join sga..maestroidentificaciones X
ON A.tipoidentificacion = X.tipoIdentificacion AND A.identificacion = X.identificacion
INNER JOIN 
sga..refCups B
ON A.codigoConsulta=B.codigo
left join sga..maestroIps g
ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
ON g.idGrupoIps = h.id


where codigoConsulta in ('890203','890303','890103')  and
diagnosticoPrincipal IN ('Z001','Z002','Z003','Z000','Z012') AND
finalidadConsulta IN ('04','05','07') AND
fechaConsulta between '2025-01-01' and '2025-06-30'

UNION ALL

select 
 codPrestador
,U.tipoDocumentoIdentificacion
,U.numDocumentoIdentificacion
,fechaInicioAtencion
,codConsulta
,B.descrip
,finalidadTecnologiaSalud
,codDiagnosticoPrincipal
, h.descrip
from sga..ripsnAC A
inner join sga..ripsnus U
ON U.consecutivo = A.id
inner join sga..maestroidentificaciones X
ON U.tipoDocumentoIdentificacion=X.tipoidentificacion AND U.numDocumentoIdentificacion=X.identificacion
INNER JOIN 
sga..refCups B
ON A.codConsulta=B.codigo
left join sga..maestroIps g
ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
ON g.idGrupoIps = h.id

where codConsulta in ('890203','890303','890103')  and
coddiagnosticoPrincipal IN ('Z001','Z002','Z003','Z000','Z012') AND
--finalidadTecnologiaSalud IN ('04','05','07') AND
fechaInicioAtencion between '2025-01-01' and '2025-06-30'

--rips ap
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

WHERE A.codigoProcedimiento IN ('997301') 
and a.diagnostico in ('K020','K021','K036','K040','K046','K050','K051','K052','K053','K081','K083','K500','Y98X','Z000','Z001','Z002','Z003','Z012','Z300','Z321','Z348','Z357','Z358','Z359','Z713') AND
--a.finalidadProcedimiento IN ('3') AND
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

where codProcedimiento in ('997301')  and
coddiagnosticoPrincipal IN ('K020','K021','K036','K040','K046','K050','K051','K052','K053','K081','K083','K500','Y98X','Z000','Z001','Z002','Z003','Z012','Z300','Z321','Z348','Z357','Z358','Z359','Z713') AND
--finalidadTecnologiaSalud IN ('03') AND
fechaInicioAtencion between '2025-01-01' and '2025-06-30'

--select * from SGA..ripsAP


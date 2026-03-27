
SELECT 
A.codigoIps
,X.tipoIdentificacion
,X.Identificacion
,fechaInicio
,codigoServicio
,B.descrip
, h.descrip as Ips_Prim
FROM SGA..ripsAT A
inner join sga..ripsAF F
ON A.numeroFactura = F.numeroFactura AND A.codigoIps = F.codigoIps
inner join sga..maestroidentificaciones X
ON A.tipoidentificacion = X.tipoIdentificacion AND A.identificacion = X.identificacion
inner join sga..refCups B
ON A.codigoServicio=B.codigo
inner join sga..maestroidentificaciones c
ON A.tipoIdentificacion=C.tipoidentificacion AND A.identificacion=C.identificacion
left join sga..maestroIps g
ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
ON g.idGrupoIps = h.id

WHERE A.codigoServicio IN ('V06DX') AND
F.fechaInicio between '2025-01-01' and '2025-06-30'

UNION ALL

select 
 codPrestador
,U.tipoDocumentoIdentificacion
,U.numDocumentoIdentificacion
,fechaSuministroTecnologia
,codTecnologiaSalud
,B.descrip
, h.descrip
from sga..ripsnAT A
inner join sga..ripsnus U
ON A.id = U.consecutivo
inner join sga..maestroidentificaciones X
ON U.tipoDocumentoIdentificacion=X.tipoidentificacion AND U.numDocumentoIdentificacion=X.identificacion
INNER JOIN 
sga..refCups B
ON A.codTecnologiaSalud=B.codigo
left join sga..maestroIps g
ON X.numeroCarnet = g.numeroCarnet
LEFT JOIN sga..maestroIpsGru h
ON g.idGrupoIps = h.id

where codTecnologiaSalud in ('V06DX')  and
fechaSuministroTecnologia between '2025-01-01' and '2025-06-30'

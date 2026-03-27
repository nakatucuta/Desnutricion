--rips ap
SELECT A.*,B.descrip,d.codigoMunicipio
FROM SGA..ripsAP A
inner join sga..refCups B
ON A.codigoProcedimiento=B.codigo
inner join sga..maestroidentificaciones c
ON A.tipoIdentificacion=C.tipoidentificacion AND A.identificacion=C.identificacion

inner join sga..maestroAfiliados d
on c.numeroCarnet=d.numeroCarnet
WHERE A.codigoProcedimiento IN ('876802') 

and a.diagnostico in ('Z000')AND
a.finalidadProcedimiento IN ('4','7') 
AND a.fechaProcedimiento between '2022-01-01' and '2025-06-30'


--select * from SGA..ripsAP


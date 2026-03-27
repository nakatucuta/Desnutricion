--rips at
 SELECT E.NUMEROFACTURA,A.codigoIps,f.descrip,A.tipoIdentificacion,A.Identificacion,d.primerApellido,d.segundoApellido,d.primerNombre,d.segundoNombre,
d.fechaNacimiento,d.genero,j.telefono,
E.fechaInicio,'' as ambito,'' as finalidad,'' as diagnostico,'' as descrip,a.codigoServicio,B.descrip
  ,CASE
						
						WHEN d.codigoMunicipio = '001' THEN 'RIOHACHA'
						WHEN d.codigoMunicipio = '035' THEN 'ALBANIA'
						WHEN d.codigoMunicipio = '078' THEN 'BARRANCAS'
						WHEN d.codigoMunicipio = '098' THEN 'DISTRACCION'
						WHEN d.codigoMunicipio = '430' THEN 'MAICAO'
						WHEN d.codigoMunicipio = '560' THEN 'MANAURE'
						WHEN d.codigoMunicipio = '847' THEN 'URIBIA'
					else d.codigoMunicipio END Nombre_Municipio
 
 ,H.descrip

FROM SGA..ripsAT A
inner join sga..refCups B
ON A.codigoServicio=B.codigo
inner join sga..maestroidentificaciones c
ON A.tipoIdentificacion=C.tipoidentificacion AND A.identificacion=C.identificacion
inner join sga..maestroAfiliados d
on c.numeroCarnet=d.numeroCarnet
left join sga..ripsAF E
ON a.numeroFactura=e.numeroFactura and a.codigoIps=e.codigoIps

left join sga..maestroIps g
			ON d.numeroCarnet = g.numeroCarnet
		LEFT JOIN sga..maestroIpsGru h
			ON g.idGrupoIps = h.id

left join SGA..refIps f
    on a.codigoIps=f.codigo

	 left join sga..datosSocioeconomicos j
    on c.numeroCarnet=j.numeroCarnet

WHERE A.codigoServicio IN ('PYP012') AND
E.fechaInicio between '2025-01-01' and '2025-05-30'

UNION ALL

SELECT X.NUMERO_FACTURA,a.codPrestador,f.descrip,U.tipoDocumentoIdentificacion,U.numDocumentoIdentificacion,d.primerApellido,d.segundoApellido,d.primerNombre,d.segundoNombre,
d.fechaNacimiento,d.genero,e.telefono,
a.fechaSuministroTecnologia,A.numAutorizacion,'','','',a.codTecnologiaSalud,B.descrip

 ,CASE
						
						WHEN d.codigoMunicipio = '001' THEN 'RIOHACHA'
						WHEN d.codigoMunicipio = '035' THEN 'ALBANIA'
						WHEN d.codigoMunicipio = '078' THEN 'BARRANCAS'
						WHEN d.codigoMunicipio = '098' THEN 'DISTRACCION'
						WHEN d.codigoMunicipio = '430' THEN 'MAICAO'
						WHEN d.codigoMunicipio = '560' THEN 'MANAURE'
						WHEN d.codigoMunicipio = '847' THEN 'URIBIA'
					else d.codigoMunicipio END Nombre_Municipio,

H.DESCRIP

  FROM SGA..ripsnAT A
	inner join SGA..ripsnUS U
	ON A.id = U.consecutivo
	inner join sga..ripsntr V
	ON V.consecutivo = U.id
	inner join sga..ripsnaf X
	ON V.consecutivo = X.id
    inner join sga..refCups B
    ON A.codTecnologiaSalud=B.codigo

    inner join sga..maestroidentificaciones c
    ON U.tipoDocumentoIdentificacion=C.tipoidentificacion AND U.numDocumentoIdentificacion=C.identificacion

    inner join sga..maestroAfiliados d
    on c.numeroCarnet=d.numeroCarnet

    left join sga..datosSocioeconomicos e
    on c.numeroCarnet=e.numeroCarnet

    left join SGA..refIps f
    on a.codPrestador=f.codigo

	left join sga..maestroIps g
			ON d.numeroCarnet = g.numeroCarnet
		LEFT JOIN sga..maestroIpsGru h
			ON g.idGrupoIps = h.id

     WHERE A.codTecnologiaSalud IN ('PYP012') 
      AND a.fechaSuministroTecnologia between '2025-01-01' and '2025-06-30'

	
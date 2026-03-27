 select
 a.identificacion
 ,a.primerApellido
 ,a.primerNombre
 ,a.conRnac
 ,a.fecConOdo
 ,a.fecTomHem
 ,b.periodo
 
               from sga..maestroInfNominalR202EV b
		      inner join  sga..maestroInfNominalR202 a
		      on a.id=b.id	
		      where  b.periodo between '202501' and '202506' 			  
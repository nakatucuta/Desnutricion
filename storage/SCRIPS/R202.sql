 select
 a.tipoReg
 ,a.consecutivo
 ,a.codigoIpsPrimaria
 ,a.tipoIdentificacion
 ,a.identificacion
 ,a.primerApellido
 ,a.segundoApellido
 ,a.primerNombre
 ,a.segundoNombre
 ,a.fechaNacimiento
 ,a.genero
 ,a.pEtnica
 ,a.ocupacion
 ,a.nEducativo
 ,a.tamHepC
 ,a.fecConLac
 ,a.conRnac
 ,a.plaFamPvez
 ,a.sumAntc
 ,a.fecSumAnt
 ,a.fecTacRec
 ,a.fecTamCol
 ,a.fecTamCaCol
 ,a.SumSulFult
 ,a.sumVitA
 ,a.fecTomLDL
 ,a.fecTomPSA
 ,a.preEntrITS
 ,a.fecConOdo
 ,a.sumHiePriInf
 ,a.fecAntHepB
 ,a.fecPruVih
 ,a.tasCanCutr
 ,a.citCerUte
 ,a.fecColp
 ,a.fecBiopCerv
 ,a.fecMamogra
 ,a.fecTomBiopSen
 ,a.fecTomHem
 ,a.fecTomGli
 ,a.fecCreati
 ,a.fecEntPre
 ,a.fecTamHep
 ,a.fecHdl
 ,a.fecBacDiag
 ,a.fecTomTri
 ,b.periodo


               from sga..maestroInfNominalR202EV b
		      inner join  sga..maestroInfNominalR202 a
		      on a.id=b.id	
		      where  b.periodo between '202301' and '202306' 			  
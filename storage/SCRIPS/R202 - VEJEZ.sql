DECLARE @PeriodoIni VARCHAR(6) = CONVERT(VARCHAR(4), YEAR(@Desde))
    + RIGHT('00' + CONVERT(VARCHAR(2), MONTH(@Desde)), 2);
DECLARE @PeriodoFin VARCHAR(6) = CONVERT(VARCHAR(4), YEAR(@HastaExclusivo))
    + RIGHT('00' + CONVERT(VARCHAR(2), MONTH(@HastaExclusivo)), 2);

 select
a.identificacion
 ,a.primerApellido
 ,a.primerNombre
 ,a.conRnac
 ,a.fecConOdo
 ,a.claRieCar
 ,a.citCerUte
 ,a.fecColp
 ,a.fecBiopCerv
 ,a.conRnac
 ,a.fecTomBiopSen
 ,a.fecTomPSA
 ,a.fecTacRec
 ,a.fecTamCaCol
 ,a.fecTamCol
 ,a.plaFamPvez
 ,a.fecSumAnt
 ,a.sumAntc
 ,a.fecPruVih
 ,a.fecAntHepB
 ,a.fecTamHep
 ,b.periodo

               from sga..maestroInfNominalR202EV b
		      inner join  sga..maestroInfNominalR202 a
		      on a.id=b.id	
		      where  b.periodo between @PeriodoIni and @PeriodoFin			  

<?php

namespace App\Console\Commands;

use App\Models\SeguimientMaestrosiv549;
use App\Services\Seguimiento549AlertService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EnviarAlertasSeguimientos extends Command
{
    public function __construct(private readonly Seguimiento549AlertService $alertService)
    {
        parent::__construct();
    }

    protected $signature = 'seguimientos:enviar-alertas';
    protected $description = 'Envia correos por hitos vencidos en seguimientos con control de una alerta por hito.';

    public function handle()
    {
        $now = Carbon::now();
        $totalEnviados = 0;

        SeguimientMaestrosiv549::with(['asignacion.user', 'alertasEnviadas'])
            ->select([
                'id',
                'asignacion_id',
                'created_at',
                'fecha_hospitalizacion',
                'fecha_egreso',
                'fecha_control_rn_inmediato',
                'descripcion_seguimiento_inmediato',
                'fecha_seguimiento_1',
                'fecha_seguimiento_2',
                'fecha_seguimiento_3',
                'fecha_seguimiento_4',
                'fecha_seguimiento_5',
                'fecha_consulta_6_meses',
                'fecha_consulta_1_ano',
            ])
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use ($now, &$totalEnviados) {
                foreach ($chunk as $seguimiento) {
                    $asignacion = $seguimiento->asignacion;
                    if (!$asignacion) {
                        continue;
                    }

                    $this->alertService->syncSuperImmediateAlert($seguimiento, $now);
                    $totalEnviados += $this->alertService->dispatchOverdueAlerts($seguimiento, $now);
                }
            });

        $this->info("Alertas enviadas: {$totalEnviados}");

        return Command::SUCCESS;
    }
}

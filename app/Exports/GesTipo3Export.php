<?php

// app/Exports/GesTipo3Export.php

namespace App\Exports;

use App\Models\GesTipo3;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GesTipo3Export implements FromQuery, WithHeadings, WithMapping
{
    protected $from, $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function query()
    {
        $query = GesTipo3::query()
            ->whereBetween('created_at', [$this->from, $this->to]);

        if (Auth::user()->usertype === 2) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID','Usuario','Gestante','Tipo Reg.','Consec.','Tipo ID','No ID','F. Tec.','CUPS','Finalidad',
            'Riesgo Gest.','Riesgo Pree.','ASA','Ác. Fólico','Ferroso','Calcio','F. Post','Mét. Post',
            'F. Salida','F. Term.','Tipo Term.','PAS','PAD','IMC','Hb','IPAU','Creado','Actualizado'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->user_id,
            $row->ges_tipo1_id,
            $row->tipo_de_registro,
            $row->consecutivo_de_registro,
            $row->tipo_identificacion_de_la_usuaria,
            $row->no_id_del_usuario,
            $row->fecha_tecnologia_en_salud,
            $row->codigo_cups_de_la_tecnologia_en_salud,
            $row->finalidad_de_la_tecnologia_en_salud,
            $row->clasificacion_riesgo_gestacional,
            $row->clasificacion_riesgo_preeclampsia,
            (int)$row->suministro_acido_acetilsalicilico_ASA,
            (int)$row->suministro_acido_folico_en_el_control_prenatal,
            (int)$row->suministro_sulfato_ferroso_en_el_control_prenatal,
            (int)$row->suministro_calcio_en_el_control_prenatal,
            $row->fecha_suministro_de_anticonceptivo_post_evento_obstetrico,
            (int)$row->suministro_metodo_anticonceptivo_post_evento_obstetrico,
            $row->fecha_de_salida_de_aborto_o_atencion_del_parto_o_cesarea,
            $row->fecha_de_terminacion_de_la_gestacion,
            $row->tipo_de_terminacion_de_la_gestacion,
            $row->tension_arterial_sistolica_PAS_mmHg,
            $row->tension_arterial_diastolica_PAD_mmHg,
            $row->indice_de_masa_corporal,
            $row->resultado_de_la_hemoglobina,
            $row->indice_de_pulsatilidad_de_arterias_uterinas,
            $row->created_at,
            $row->updated_at,
        ];
    }
}

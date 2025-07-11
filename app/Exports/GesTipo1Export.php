<?php

// app/Exports/GesTipo1Export.php

namespace App\Exports;

use App\Models\GesTipo1;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GesTipo1Export implements FromQuery, WithHeadings, WithMapping
{
    protected $from, $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function query()
    {
        $query = GesTipo1::query()
            ->whereBetween('created_at', [$this->from, $this->to]);

        if (Auth::user()->usertype === 2) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID','Usuario','Tipo Registro','Consec.','País','Municipio','Zona','IPS Primaria',
            'Tipo ID','No ID','Carnet','1er Apellido','2º Apellido','1er Nombre','2º Nombre',
            'F. Nac.','Cód Étnico','Cód Ocupación','Nivel Educ.','FPP','Dirección',
            'HTA','Preeclampsia','Diabetes','Autoinmune','S. Metabólico','ERC','Trombofilia',
            'Anemia','Sepsis','Tabaco','Intergenésico','Múltiple','Método Concepción',
            'Creado','Actualizado'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->user_id,
            $row->tipo_de_registro,
            $row->consecutivo,
            $row->pais_de_la_nacionalidad,
            $row->municipio_de_residencia_habitual,
            $row->zona_territorial_de_residencia,
            $row->codigo_de_habilitacion_ips_primaria_de_la_gestante,
            $row->tipo_de_identificacion_de_la_usuaria,
            $row->no_id_del_usuario,
            $row->numero_carnet,
            $row->primer_apellido,
            $row->segundo_apellido,
            $row->primer_nombre,
            $row->segundo_nombre,
            $row->fecha_de_nacimiento,
            $row->codigo_pertenencia_etnica,
            $row->codigo_de_ocupacion,
            $row->codigo_nivel_educativo_de_la_gestante,
            $row->fecha_probable_de_parto,
            $row->direccion_de_residencia_de_la_gestante,
            $row->antecedente_hipertension_cronica,
            $row->antecedente_preeclampsia,
            $row->antecedente_diabetes,
            $row->antecedente_les_enfermedad_autoinmune,
            $row->antecedente_sindrome_metabolico,
            $row->antecedente_erc,
            $row->antecedente_trombofilia_o_trombosis_venosa_profunda,
            $row->antecedentes_anemia_celulas_falciformes,
            $row->antecedente_sepsis_durante_gestaciones_previas,
            $row->consumo_tabaco_durante_la_gestacion,
            $row->periodo_intergenesico,
            $row->embarazo_multiple,
            $row->metodo_de_concepcion,
            $row->created_at,
            $row->updated_at,
        ];
    }
}

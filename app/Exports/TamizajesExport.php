<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TamizajesExport implements FromCollection, WithHeadings
{
    protected $tamizajes;

    public function __construct($tamizajes)
    {
        $this->tamizajes = $tamizajes;
    }

    public function collection()
    {
        return $this->tamizajes->map(function($t){
            return [
                // Afiliado: identificación y carnet antes de los nombres
                'Usuario'               => $t->usuario,
                'Tipo Identificación'   => $t->tipo_identificacion,
                'Número Identificación' => $t->numero_identificacion,
                'Número Carnet'         => $t->numero_carnet,

                // Afiliado: nombres y apellidos
                'Primer Nombre'         => $t->primerNombre,
                'Segundo Nombre'        => $t->segundoNombre,
                'Primer Apellido'       => $t->primerApellido,
                'Segundo Apellido'      => $t->segundoApellido,
                'Dirección'             => $t->direccion,
                'Teléfono'              => $t->telefono,

                // Nombre completo
                'Nombre Completo'       => $t->nombre_completo,

                // Tamizaje
                'ID'                    => $t->id,
                'Fecha Tamizaje'        => $t->fecha_tamizaje,
                'Tipo Tamizaje ID'      => $t->tipo_tamizaje_id,
                'Resultado Tamizaje ID' => $t->resultado_tamizaje_id,

                // Tipo y resultado
                'Tipo Tamizaje'         => $t->tipo_tamizaje,
                'Código Resultado'      => $t->codigo_resultado,
                'Descripción Código'    => $t->descripcion_codigo,

                // Resultado
                'Valor Laboratorio'     => $t->valor_laboratorio,
                'Descripción Resultado' => $t->descript_resultado,

                // Usuario y timestamps
                'Usuario ID'            => $t->user_id,
              
                'Creado en'             => $t->created_at,
                'Actualizado en'        => $t->updated_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            // Afiliado
            'IPS',
            'Tipo Identificación',
            'Número Identificación',
            'Número Carnet',
            'Primer Nombre',
            'Segundo Nombre',
            'Primer Apellido',
            'Segundo Apellido',
            'Dirección',
            'Teléfono',
            'Nombre Completo',

            // Tamizaje
            'ID',
            'Fecha Tamizaje',
            'Tipo Tamizaje ID',
            'Resultado Tamizaje ID',

            // Tipo y resultado
            'Tipo Tamizaje',
            'Código Resultado',
            'Descripción Código',

            // Resultado
            'Valor Laboratorio',
            'Descripción Resultado',

            // Usuario y timestamps
            'Usuario ID',
       
            'Creado en',
            'Actualizado en',
        ];
    }
}

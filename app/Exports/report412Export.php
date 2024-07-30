<?php

namespace App\Exports;

use App\Models\Cargue412;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class report412Export implements ToCollection, WithStartRow
{
     
   

    
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $data = [
                'numero_orden' => $row[0],
                'nombre_coperante' => $row[1],
                'nombre_profesional' => $row[2],
                'numero_profesional' => $row[3],
                'fecha_captacion' => isset($row[4]) ? Date::excelToDateTimeObject($row[4])->format('Y-m-d') : null,
                'municipio' => $row[5],
                'uds' => $row[6],
                'nombre_rancheria' => $row[7],
                'ubicacion_casa' => $row[8],
                'nombre_cuidador' => $row[9],
                'identioficacion_cuidador' => $row[10],
                'telefono_cuidador' => $row[11],
                'nombre_eapb_cuidador' => $row[12],
                'nombre_autoridad_trad_ansestral' => $row[13],
                'datos_contacto_autoridad' => $row[14],
                'primer_nombre' => $row[15],
                'segundo_nombre' => $row[16],
                'primer_apellido' => $row[17],
                'segundo_apellido' => $row[18],
                'tipo_identificacion' => $row[19],
                'numero_identificacion' => $row[20],
                'sexo' => $row[21],
                'fecha_nacimieto_nino' => isset($row[22]) ? Date::excelToDateTimeObject($row[22])->format('Y-m-d') : null,
                'edad_meses' => $row[23],
                'regimen_afiliacion' => $row[24],
                'nombre_eapb_menor' => $row[25],
                'peso_kg' => $row[26],
                'logitud_talla_cm' => $row[27],
                'perimetro_braqueal' => $row[28],
                'signos_peligro_infeccion_respiratoria' => $row[29],
                'sexosignos_desnutricion' => $row[30],
                'puntaje_z' => $row[31],
                'calsificacion_antropometrica' => $row[32],
            ];

            // Define las reglas de validación
            $rules = [
                'numero_orden' => 'nullable',
                'nombre_coperante' => 'nullable|string',
                'nombre_profesional' => 'nullable|string',
                'numero_profesional' => 'nullable',
                'fecha_captacion' => 'nullable|date',
                'municipio' => 'nullable|string',
                'uds' => 'nullable|string',
                'nombre_rancheria' => 'nullable|string',
                'ubicacion_casa' => 'nullable|string',
                'nombre_cuidador' => 'nullable|string',
                'identioficacion_cuidador' => 'nullable',
                'telefono_cuidador' => 'nullable',
                'nombre_eapb_cuidador' => 'nullable|string',
                'nombre_autoridad_trad_ansestral' => 'nullable|string',
                'datos_contacto_autoridad' => 'nullable',
                'primer_nombre' => 'nullable|string',
                'segundo_nombre' => 'nullable|string',
                'primer_apellido' => 'nullable|string',
                'segundo_apellido' => 'nullable|string',
                'tipo_identificacion' => 'nullable|string',
                'numero_identificacion' => 'nullable|string',
                'sexo' => 'nullable|string',
                'fecha_nacimieto_nino' => 'nullable|date',
                'edad_meses' => 'nullable|integer',
                'regimen_afiliacion' => 'nullable|string',
                'nombre_eapb_menor' => 'nullable|string',
                'peso_kg' => 'nullable|numeric',
                'logitud_talla_cm' => 'nullable|numeric',
                'perimetro_braqueal' => 'nullable|numeric',
                'signos_peligro_infeccion_respiratoria' => 'nullable|string',
                'sexosignos_desnutricion' => 'nullable|string',
                'puntaje_z' => 'nullable|numeric',
                'calsificacion_antropometrica' => 'nullable|string',
            ];

            // Define los mensajes de error
            $messages = [
                'date' => 'El campo :attribute debe ser una fecha válida.',
                'string' => 'El campo :attribute debe ser un texto válido.',
                'integer' => 'El campo :attribute debe ser un número entero válido.',
                'numeric' => 'El campo :attribute debe ser un número válido.',
            ];

            // Realiza la validación
            $validator = Validator::make($data, $rules, $messages);
        }

        if ($validator->fails()) {
            // Registra los errores y arroja una excepción de validación
            foreach ($validator->errors()->all() as $error) {
                Log::error($error);
            }
            throw new ValidationException($validator);
        } 




        foreach ($rows as $row) {
            // Verifica si $row[2] (fecha_captacion) y $row[19] (fecha_nacimieto_nino) son fechas válidas
            // if (!is_numeric($row[2]) || !is_numeric($row[19])) {
            //     \Log::error("Valor no numérico encontrado en fila para 'fecha_captacion' o 'fecha_nacimieto_nino': ", $row->toArray());
            //     continue; // O maneja el error como prefieras
            // }
            // Crea una nueva instancia del modelo Item
            $item = new Cargue412();
            
            // Asigna los valores de las columnas del archivo Excel a las propiedades del modelo
            $item->numero_orden = $row[0];
            $item->nombre_coperante = $row[1];
            $item->nombre_profesional = $row[2];
            $item->numero_profesional = $row[3];
            $item->fecha_captacion = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[4])->format('Y-m-d');
            $item->municipio = $row[5];
            $item->uds = $row[6];
            $item->nombre_rancheria = $row[7];
            $item->ubicacion_casa = $row[8];
            $item->nombre_cuidador = $row[9];
            $item->identioficacion_cuidador = $row[10];
            $item->telefono_cuidador = $row[11];
            $item->nombre_eapb_cuidador = $row[12];
            $item->nombre_autoridad_trad_ansestral = $row[13];
            $item->datos_contacto_autoridad = $row[14];
            $item->primer_nombre = $row[15];
            $item->segundo_nombre = $row[16];
            $item->primer_apellido = $row[17];
            $item->segundo_apellido = $row[18];
            $item->tipo_identificacion = $row[19];
            $item->numero_identificacion = $row[20];
            $item->sexo = $row[21];
            $item->fecha_nacimieto_nino = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[22])->format('Y-m-d');
            $item->edad_meses = $row[23];
            $item->regimen_afiliacion = $row[24];
            $item->nombre_eapb_menor = $row[25];
            $item->peso_kg = $row[26];
            $item->logitud_talla_cm = $row[27];
            $item->perimetro_braqueal = $row[28];
            $item->signos_peligro_infeccion_respiratoria = $row[29];
            $item->sexosignos_desnutricion = $row[30];
            $item->puntaje_z = $row[31];
            $item->calsificacion_antropometrica = $row[32];
            $item->estado = 1 ;
            // Asigna las demás columnas según corresponda

            // Guarda el modelo en la base de datos
            $item->save();
       // Añadir un mensaje de log para confirmar el guardado
Log::info('Afiliado guardado con éxito:');



    }
}

    public function startRow(): int
    {
        return 2; // Establece la fila de inicio predeterminada en 2 (puede variar según tus necesidades)
    }


}

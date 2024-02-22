<?php

namespace App\Exports;

use App\Models\Cargue412;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;

class report412Export implements ToCollection, WithStartRow
{
    /**
    * @return \Illuminate\Support\Collection
    */
    //public function collection()
   // {
        //return Cargue412::all();
    //}

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Crea una nueva instancia del modelo Item
            $item = new Cargue412();
            
            // Asigna los valores de las columnas del archivo Excel a las propiedades del modelo
            $item->numero_orden = $row[0];
            $item->nombre_coperante = $row[1];
            $item->fecha_captacion = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('Y-m-d');
            $item->municipio = $row[3];
            $item->nombre_rancheria = $row[4];
            $item->ubicacion_casa = $row[5];
            $item->nombre_cuidador = $row[6];
            $item->identioficacion_cuidador = $row[7];
            $item->telefono_cuidador = $row[8];
            $item->nombre_eapb_cuidador = $row[9];
            $item->nombre_autoridad_trad_ansestral = $row[10];
            $item->datos_contacto_autoridad = $row[11];
            $item->primer_nombre = $row[12];
            $item->segundo_nombre = $row[13];
            $item->primer_apellido = $row[14];
            $item->segundo_apellido = $row[15];
            $item->tipo_identificacion = $row[16];
            $item->numero_identificacion = $row[17];
            $item->sexo = $row[18];
            $item->fecha_nacimieto_nino = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[19])->format('Y-m-d');
            $item->edad_meses = $row[20];
            $item->regimen_afiliacion = $row[21];
            $item->nombre_eapb_menor = $row[22];
            $item->peso_kg = $row[23];
            $item->logitud_talla_cm = $row[24];
            $item->perimetro_braqueal = $row[25];
            $item->signos_peligro_infeccion_respiratoria = $row[26];
            $item->sexosignos_desnutricion = $row[27];
            $item->puntaje_z = $row[28];
            $item->calsificacion_antropometrica = $row[29];
            // Asigna las demás columnas según corresponda

            // Guarda el modelo en la base de datos
            $item->save();
        }
    }

    public function startRow(): int
    {
        return 1; // Establece la fila de inicio predeterminada en 2 (puede variar según tus necesidades)
    }


}

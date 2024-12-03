<?php
namespace App\Exports;

use App\Models\Cargue412;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Validator;

use Exception;

class report412Export implements ToCollection, WithStartRow
{
    protected $errores = [];

    public function collection(Collection $rows)
    {
        $fila = 1; // Empezamos a contar desde la fila 1 (ajustar según sea necesario)

        foreach ($rows as $row) {
            $fila++; // Incrementamos el contador de filas

            // Convertir y validar las fechas
            $fecha_captacion = $this->convertirFecha($row, 4, $fila, 'fecha_captacion');
            $fecha_nacimiento_nino = $this->convertirFecha($row, 22, $fila, 'fecha_nacimiento_nino');

            // Si hay errores, no continuamos y registramos el error
            if (!empty($this->errores)) {
                continue; // Pasamos a la siguiente fila si ya hay errores
            }

            // Los datos que se validarán
            $data = [
                'numero_orden' => $row[0],
                'nombre_coperante' => $row[1],
                'nombre_profesional' => $row[2],
                'numero_profesional' => $row[3],
                'fecha_captacion' => $fecha_captacion,
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
                'fecha_nacimieto_nino' => $fecha_nacimiento_nino,
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

            // Reglas de validación
            $rules = [
                'numero_orden' => 'nullable',
                'nombre_coperante' => 'nullable|string',
                'nombre_profesional' => 'nullable|string',
                'numero_profesional' => 'nullable',
                'fecha_captacion' => 'nullable|date_format:Y-m-d',
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
                'numero_identificacion' => 'nullable',
                'sexo' => 'nullable|string',
                'fecha_nacimieto_nino' => 'nullable|date_format:Y-m-d',
                'edad_meses' => 'nullable|integer',
                'regimen_afiliacion' => 'nullable|string',
                'nombre_eapb_menor' => 'nullable|string',
                'peso_kg' => 'nullable|numeric',
                'logitud_talla_cm' => 'nullable|numeric',
                'perimetro_braqueal' => 'nullable',
                'signos_peligro_infeccion_respiratoria' => 'nullable|string',
                'sexosignos_desnutricion' => 'nullable|string',
                'puntaje_z' => 'nullable',
                'calsificacion_antropometrica' => 'nullable|string',
            ];

            // Mensajes de error personalizados
            $messages = [
                'date_format' => "El campo :attribute debe tener el formato Y-m-d (por ejemplo, 2024-07-29) en la fila $fila.",
                'string' => "El campo :attribute debe ser un texto válido en la fila $fila.",
                'integer' => "El campo :attribute debe ser un número entero válido en la fila $fila.",
                'numeric' => "El campo :attribute debe ser un número válido en la fila $fila.",
            ];

                // Realizamos la validación
                $validator = Validator::make($data, $rules, $messages);

                // Si la validación falla, registrar errores
                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $error) {
                        Log::error($error . " (Fila: $fila)");
                        $this->errores[] = $error . " (Fila: $fila)";
                    }
                    continue; // Saltamos la fila con error
                }

                // Guardar los datos si pasan la validación
                $item = new Cargue412();
                $item->fill($data);
                $item->estado = 1;
                $item->save();
            }

            // Si hay errores, almacenarlos en la sesión y redirigir
            if (!empty($this->errores)) {
                // Guardar los errores en la sesión
                Session::flash('error1', implode("\n", $this->errores));
                // Puedes redirigir a la ruta que desees con un mensaje de error
                return redirect()->back();
            } else {
                // Si todo fue bien, guardamos el mensaje de éxito
                Session::flash('success', 'El archivo se cargó correctamente.');
                return redirect()->back();
            }
        }

        // Método para convertir la fecha en el formato correcto
        private function convertirFecha($row, $columna, $fila, $nombreCampo)
        {
            if (isset($row[$columna]) && is_numeric($row[$columna])) {                
                try {
                    return Date::excelToDateTimeObject($row[$columna])->format('Y-m-d');
                } catch (\Exception $e) {
                    $error = "Error al convertir la fecha en el campo '$nombreCampo' en la fila $fila, columna $columna.";
                    Log::error($error);
                    $this->errores[] = $error;
                    return null; // Si hay error, devolver nulo y agregar error
                }
            } elseif (isset($row[$columna]) && !is_numeric($row[$columna])) {
                // Verificamos si es una fecha en formato Y-m-d
                if (\DateTime::createFromFormat('Y-m-d', $row[$columna])) {
                    return $row[$columna]; // Si es válida, devolverla
                } else {
                    $error = "Fecha inválida en el campo '$nombreCampo' en la fila $fila, columna $columna. Debe estar en formato 'Y-m-d'.";
                    Log::error($error);
                    $this->errores[] = $error;
                    return null; // Si no es válida, devolver nulo y agregar error
                }
            }

            return null; // Si no hay fecha, devolver nulo
        }

        public function startRow(): int
        {
            return 2; // Establece la fila de inicio según sea necesario
        }
    }

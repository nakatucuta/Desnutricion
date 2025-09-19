<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PiPlaceholderController extends Controller
{
    public function show(Request $request)
    {
        // $request->key llega desde las rutas .defaults('key','...')
        $key = $request->get('key', 'en_construccion');

        // Aquí luego puedes redirigir a tus controladores reales por $key.
        // Por ahora mostramos una pantalla "En construcción".
        return view('ciclo_vidas.placeholder', [
            'key' => $key,
            'titulo' => match ($key) {
                'medica'             => 'Atención en salud médica',
                'enfermeria'         => 'Atención por enfermería',
                'bucal_fluor_sem1'   => 'Flúor · Primer semestre',
                'bucal_fluor_sem2'   => 'Flúor · Segundo semestre',
                'bucal_placa_sem1'   => 'Control de placa · Primer semestre',
                'bucal_placa_sem2'   => 'Control de placa · Segundo semestre',
                'bucal_sellantes'    => 'Sellantes',
                'nutri_hemoglobina'  => 'Tamizaje de hemoglobina',
                'nutri_lactancia'    => 'Apoyo a lactancia materna (R202)',
                'nutri_vitamina_a'   => 'Vitamina A (R202)',
                'nutri_hierro'       => 'Hierro (R202)',
                default              => 'En construcción',
            },
        ]);
    }
}

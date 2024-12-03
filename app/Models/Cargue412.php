<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargue412 extends Model
{
    use HasFactory;
    public function getDateFormat(){
        return 'Y-d-m h:m:s';
       }


       protected $table = 'cargue412s';

       protected $fillable = [
        'numero_orden',
        'nombre_coperante',
        'nombre_profesional',
        'numero_profesional',
        'fecha_captacion',
        'municipio',
        'uds',
        'nombre_rancheria',
        'ubicacion_casa',
        'nombre_cuidador',
        'identioficacion_cuidador',
        'telefono_cuidador',
        'nombre_eapb_cuidador',
        'nombre_autoridad_trad_ansestral',
        'datos_contacto_autoridad',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'tipo_identificacion',
        'numero_identificacion',
        'sexo',
        'fecha_nacimieto_nino',
        'edad_meses',
        'regimen_afiliacion',
        'nombre_eapb_menor',
        'peso_kg',
        'logitud_talla_cm',
        'perimetro_braqueal',
        'signos_peligro_infeccion_respiratoria',
        'sexosignos_desnutricion',
        'puntaje_z',
        'calsificacion_antropometrica',
        'user_id',
      
           // Agrega aquí más columnas si es necesario.
       ];
}

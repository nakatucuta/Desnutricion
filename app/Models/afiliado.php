<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Batch_verification;

class afiliado extends Model
{
    use HasFactory;
    public function getDateFormat(){
        return 'Y-d-m h:m:s';
       }

    protected $table = 'afiliados';


    protected $fillable = [
        'fecha_atencion',
        'tipo_identificacion',
        'numero_identificacion',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'fecha_nacimiento',
        'edad_anos',
        'edad_meses',
        'edad_dias',
        'total_meses',
        'esquema_completo',
        'sexo',
        'genero',
        'orientacion_sexual',
        'edad_gestacional',
        'pais_nacimiento',
        'estatus_migratorio',
        'lugar_atencion_parto',
        'regimen',
        'aseguradora',
        'pertenencia_etnica',
        'desplazado',
        'discapacitado',
        'fallecido',
        'victima_conflicto',
        'estudia',
        'pais_residencia',
        'departamento_residencia',
        'municipio_residencia',
        'comuna',
        'area',
        'direccion',
        'telefono_fijo',
        'celular',
        'email',
        'autoriza_llamadas',
        'autoriza_correos',
        'contraindicacion_vacuna',
        'enfermedad_contraindicacion',
        'reaccion_biologicos',
        'sintomas_reaccion',
        'condicion_usuaria',
        'fecha_ultima_menstruacion',
        'semanas_gestacion',
        'fecha_prob_parto',
        'embarazos_previos',
        'fecha_antecedente',
        'tipo_antecedente',
        'descripcion_antecedente',
        'observaciones_especiales',
        'madre_tipo_identificacion',
        'madre_identificacion',
        'madre_primer_nombre',
        'madre_segundo_nombre',
        'madre_primer_apellido',
        'madre_segundo_apellido',
        'madre_correo',
        'madre_telefono',
        'madre_celular',
        'madre_regimen',
        'madre_pertenencia_etnica',
        'madre_desplazada',
        'cuidador_tipo_identificacion',
        'cuidador_identificacion',
        'cuidador_primer_nombre',
        'cuidador_segundo_nombre',
        'cuidador_primer_apellido',
        'cuidador_segundo_apellido',
        'cuidador_parentesco',
        'cuidador_correo',
        'cuidador_telefono',
        'cuidador_celular',
        'esquema_vacunacion',
        'user_id',
        'batch_verifications_id',
    ];

     /**
     * Define the relationship to the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function Batch_verifications()
    {
        return $this->belongsTo(Batch_verification::class);
    }
}

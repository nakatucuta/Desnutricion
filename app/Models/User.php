<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public function getUsername()
    {
        return $this->codigohabilitacion;
    } //4. agregar esto en el modelo 



    use HasFactory;
    public function getDateFormat(){
        return 'Y-d-m h:m:s';
        }

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'usertype',
        'codigohabilitacion',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    
    protected $casts = [
        
        
        'email_verified_at' => 'datetime',
    ];

    public function Ingreso ()
    {
    
        return $this->hasMany('App\Models\Ingreso');
    
    }


    public function Sivigila ()
    {
    
        return $this->hasMany('App\Models\Sivigila');
    
    }


    public function Seguimiento ()
    {
    
        return $this->hasMany('App\Models\Seguimiento');
    
    }

    public function Afiliado ()
    {
    
        return $this->hasMany('App\Models\afiliado');
    
    }

    public function Vacuna ()
    {
    
        return $this->hasMany('App\Models\vacuna');
    
    }



}

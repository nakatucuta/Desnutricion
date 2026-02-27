<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function getUsername()
    {
        return $this->codigohabilitacion;
    } //4. agregar esto en el modelo 



    public function getDateFormat()
    {
        // Formato no ambiguo para SQL Server (evita interpretacion regional y errores de conversion).
        return 'Ymd H:i:s';
    }

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
        'profile_photo_path',
        'pref_iframe_mode',
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
        'pref_iframe_mode' => 'boolean',
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

    public function adminlte_image()
    {
        if (!empty($this->profile_photo_path)) {
            $relative = 'storage/' . ltrim((string) $this->profile_photo_path, '/');

            if (app()->runningInConsole()) {
                return '/' . $relative;
            }

            $base = request() ? rtrim((string) request()->getBaseUrl(), '/') : '';
            return $base . '/' . $relative;
        }

        return asset('img/logo.png');
    }

    public function adminlte_desc()
    {
        return (string) ($this->email ?? '');
    }

    public function usesIframeMode(): bool
    {
        return (bool) ($this->pref_iframe_mode ?? false);
    }



}

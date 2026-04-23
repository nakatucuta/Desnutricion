<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        // $this->middleware('auth'); //OJO ESTE ES EL QUEBLOQUA EL REGISTER
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(10)->letters()->mixedCase()->numbers()->symbols(),
                'different:codigohabilitacion',
            ],
            'usertype' => ['required', 'in:1,2,3'],
            'codigohabilitacion' => ['required', 'string', 'max:80'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo valido.',
            'email.unique' => 'Este correo ya esta en uso.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.confirmed' => 'La confirmacion de contrasena no coincide.',
            'password.different' => 'La contrasena no puede ser igual al codigo de habilitacion.',
            'usertype.required' => 'Selecciona un tipo de usuario.',
            'usertype.in' => 'El tipo de usuario seleccionado no es valido.',
            'codigohabilitacion.required' => 'El codigo de habilitacion es obligatorio.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'usertype' => $data['usertype'],
            'codigohabilitacion' => $data['codigohabilitacion'],
        ]);
    }
}

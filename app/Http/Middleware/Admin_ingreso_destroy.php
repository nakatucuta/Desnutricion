<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Session;

class Admin_ingreso_destroy
{

    protected $auth;

    public function _construct(Guard $auth) // OJO ESTE CONSTRUCTOR TAMBIEN VA
    {
    
        $this->auth = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::User()->usertype == '2') {
            Session::flash('error1','NO TIENES PERMISO PARA ESTA SECCION');
           return back();
       } else {

          return $next($request); 
       }
    }
}

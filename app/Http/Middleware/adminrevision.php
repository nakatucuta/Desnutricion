<?php

namespace App\Http\Middleware;


use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Session;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class adminrevision
{

    protected $auth;

    public function _construct(Guard $auth) // OJO ESTE CONSTRUCTOR TAMBIEN VA
    {
    
        $this->auth = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::User()->usertype == '2') {
            Session::flash('error1','NO TIENES PERMISO PARA ESTA SECCION');
           return back();
       } else {

          return $next($request); 
       }
    }
}

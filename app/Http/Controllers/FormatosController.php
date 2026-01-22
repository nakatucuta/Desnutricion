<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class FormatosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function download()
    {
        $path = 'formatos/formatos.rar'; // storage/app/public/formatos/formatos.rar

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'No se encontró el archivo de formatos.');
        }

        return Storage::disk('public')->download(
            $path,
            'formatos_cargues.rar',
            ['Content-Type' => 'application/x-rar-compressed']
        );
    }
}

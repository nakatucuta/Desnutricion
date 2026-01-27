<?php

namespace App\Http\Controllers;

use App\Models\GestanteAlerta;
use App\Services\GestantesAlertService;
use Illuminate\Http\Request;

class AlertasController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Cargamos la relación 'gestante' para usar nombre/documento en la vista
        $q = GestanteAlerta::with('gestante')
            ->orderByDesc('created_at');

        // filtros opcionales
        if ($request->filled('estado')) {
            if ($request->estado === 'pendientes') {
                $q->whereNull('resolved_at');
            }

            if ($request->estado === 'resueltas') {
                $q->whereNotNull('resolved_at');
            }
        }

        $alertas = $q->paginate(15);

        return view('alertas.index', compact('alertas'));
    }

    public function markSeen(GestanteAlerta $alerta)
    {
        if (!$alerta->seen_at) {
            $alerta->update(['seen_at' => now()]);
        }

        return back();
    }

    public function resolve(GestanteAlerta $alerta)
    {
        $alerta->update([
            'seen_at'     => $alerta->seen_at ?? now(),
            'resolved_at' => now(),
        ]);

        return back();
    }

    public function pdf(GestanteAlerta $alerta)
    {
        $url = GestantesAlertService::publicPdfUrl($alerta->pdf_path);
        if (!$url) abort(404);

        // redirige a la url pública del storage
        return redirect()->away($url);
    }
}

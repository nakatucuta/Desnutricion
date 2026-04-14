<?php

namespace App\Http\Controllers;

use App\Models\GestanteAlerta;
use App\Services\GestantesAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AlertasController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'estado' => 'nullable|in:pendientes,resueltas,vistas,no_vistas',
            'severidad' => 'nullable|in:alta,media,baja',
            'modulo' => 'nullable|string|max:50',
            'q' => 'nullable|string|max:120',
        ]);

        $q = GestanteAlerta::with('gestante')
            ->orderByDesc('created_at');

        if (Auth::check() && (int) (Auth::user()->usertype ?? 0) === 2) {
            $q->where(function ($scope) {
                $scope->where('user_id', Auth::id())
                    ->orWhereHas('gestante', function ($gq) {
                        $gq->where('user_id', Auth::id());
                    });
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'pendientes') {
                $q->whereNull('resolved_at');
            }

            if ($request->estado === 'resueltas') {
                $q->whereNotNull('resolved_at');
            }

            if ($request->estado === 'vistas') {
                $q->whereNotNull('seen_at');
            }

            if ($request->estado === 'no_vistas') {
                $q->whereNull('seen_at');
            }
        }

        if ($request->filled('severidad')) {
            $q->where('severidad', Str::lower((string) $request->severidad));
        }

        if ($request->filled('modulo')) {
            $q->where('modulo', 'like', '%' . trim((string) $request->modulo) . '%');
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->q);
            $q->where(function ($scope) use ($search) {
                $scope->where('examen', 'like', "%{$search}%")
                    ->orWhere('resultado', 'like', "%{$search}%")
                    ->orWhere('campo', 'like', "%{$search}%")
                    ->orWhere('modulo', 'like', "%{$search}%")
                    ->orWhereHas('gestante', function ($gq) use ($search) {
                        $gq->where('no_id_del_usuario', 'like', "%{$search}%")
                            ->orWhere('primer_nombre', 'like', "%{$search}%")
                            ->orWhere('segundo_nombre', 'like', "%{$search}%")
                            ->orWhere('primer_apellido', 'like', "%{$search}%")
                            ->orWhere('segundo_apellido', 'like', "%{$search}%");
                    });
            });
        }

        $base = clone $q;
        $stats = [
            'total' => (clone $base)->count(),
            'pendientes' => (clone $base)->whereNull('resolved_at')->count(),
            'resueltas' => (clone $base)->whereNotNull('resolved_at')->count(),
            'nuevas' => (clone $base)->whereNull('seen_at')->count(),
            'alta' => (clone $base)->where('severidad', 'alta')->count(),
            'media' => (clone $base)->where('severidad', 'media')->count(),
            'baja' => (clone $base)->where('severidad', 'baja')->count(),
        ];

        $alertas = $q->paginate(15);

        return view('alertas.index', compact('alertas', 'stats'));
    }

    public function markSeen(GestanteAlerta $alerta)
    {
        if (!$alerta->seen_at) {
            $alerta->update(['seen_at' => now()]);
        }

        return back()->with('ok', 'Alerta marcada como vista.');
    }

    public function resolve(GestanteAlerta $alerta)
    {
        $alerta->update([
            'seen_at'     => $alerta->seen_at ?? now(),
            'resolved_at' => now(),
        ]);

        return back()->with('ok', 'Alerta marcada como resuelta.');
    }

    public function pdf(GestanteAlerta $alerta)
    {
        $url = GestantesAlertService::publicPdfUrl($alerta->pdf_path);
        if (!$url) abort(404);

        // redirige a la url pública del storage
        return redirect()->away($url);
    }
}

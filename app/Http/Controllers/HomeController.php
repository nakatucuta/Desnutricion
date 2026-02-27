<?php

namespace App\Http\Controllers;

use App\Models\Novedad;
use App\Models\NovedadRead;
use App\Models\ProfileChangeAudit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->usesIframeMode()) {
            return redirect()->route('workspace.index');
        }

        return $this->renderDashboard();
    }

    public function dashboard()
    {
        return $this->renderDashboard();
    }

    public function workspace()
    {
        $user = Auth::user();

        if (!$user->usesIframeMode()) {
            return redirect()->route('home');
        }

        config([
            'adminlte.iframe.default_tab.url' => null,
            'adminlte.iframe.default_tab.title' => null,
        ]);

        return view('workspace');
    }

    public function toggleIframeMode(Request $request)
    {
        $user = Auth::user();
        $enable = $request->has('enabled')
            ? (bool) $request->boolean('enabled')
            : !$user->usesIframeMode();

        $user->pref_iframe_mode = $enable;
        $user->save();

        if ($enable) {
            return redirect()->route('workspace.index')
                ->with('status', 'Modo pestanas multiples activado.');
        }

        return redirect()->route('home')
            ->with('status', 'Modo pestanas multiples desactivado.');
    }

    private function renderDashboard()
    {
        $user = Auth::user();
        $userId = (int) $user->id;

        $unreadCount = Novedad::unreadCountForUser($userId);

        $latestUnreadNovedades = Novedad::query()
            ->where('is_active', true)
            ->whereDoesntHave('reads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'title', 'created_at']);

        $lastPasswordAudit = ProfileChangeAudit::query()
            ->where('user_id', $userId)
            ->where('changed_fields', 'like', '%password%')
            ->orderByDesc('id')
            ->first();

        $lastPasswordChangeAt = optional($lastPasswordAudit)->changed_at;
        $daysSincePasswordChange = $lastPasswordChangeAt
            ? Carbon::parse($lastPasswordChangeAt)->diffInDays(now())
            : null;

        $security = $this->buildSecurityStatus($daysSincePasswordChange, $lastPasswordChangeAt);

        $recentActivity = $this->buildRecentActivity($userId);

        return view('home', [
            'user' => $user,
            'unreadCount' => $unreadCount,
            'latestUnreadNovedades' => $latestUnreadNovedades,
            'security' => $security,
            'recentActivity' => $recentActivity,
            'iframeModeEnabled' => $user->usesIframeMode(),
        ]);
    }

    private function buildSecurityStatus(?int $daysSincePasswordChange, $lastPasswordChangeAt): array
    {
        if ($daysSincePasswordChange === null) {
            return [
                'label' => 'Riesgo alto',
                'badgeClass' => 'badge-danger',
                'recommendation' => 'No hay registro de cambio de contrasena. Actualizala hoy con una clave robusta.',
                'lastChangeText' => 'Sin registro',
            ];
        }

        if ($daysSincePasswordChange <= 60) {
            return [
                'label' => 'Estado optimo',
                'badgeClass' => 'badge-success',
                'recommendation' => 'Tu higiene de contrasena es adecuada. Mantener recambio cada 60 a 90 dias.',
                'lastChangeText' => Carbon::parse($lastPasswordChangeAt)->format('Y-m-d H:i'),
            ];
        }

        if ($daysSincePasswordChange <= 90) {
            return [
                'label' => 'Atencion',
                'badgeClass' => 'badge-warning',
                'recommendation' => 'Se recomienda renovar la contrasena pronto para reducir riesgo.',
                'lastChangeText' => Carbon::parse($lastPasswordChangeAt)->format('Y-m-d H:i'),
            ];
        }

        return [
            'label' => 'Riesgo alto',
            'badgeClass' => 'badge-danger',
            'recommendation' => 'Tu contrasena tiene mas de 90 dias. Cambiala hoy para fortalecer seguridad.',
            'lastChangeText' => Carbon::parse($lastPasswordChangeAt)->format('Y-m-d H:i'),
        ];
    }

    private function buildRecentActivity(int $userId)
    {
        $profileEvents = ProfileChangeAudit::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('changed_by_id', $userId);
            })
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $profileMapped = $profileEvents->map(function ($event) {
            $fields = collect((array) $event->changed_fields)->filter()->values();
            $summary = $fields->isNotEmpty() ? $fields->implode(', ') : 'datos del perfil';

            return [
                'at' => $event->changed_at,
                'icon' => 'fas fa-user-shield',
                'title' => 'Actualizacion de perfil',
                'description' => 'Cambios en: ' . $summary,
            ];
        });

        $novedadReads = NovedadRead::query()
            ->where('user_id', $userId)
            ->with('novedad:id,title')
            ->orderByDesc('read_at')
            ->limit(8)
            ->get();

        $novedadMapped = $novedadReads->flatMap(function ($read) {
            $items = [[
                'at' => $read->read_at,
                'icon' => 'fas fa-bell',
                'title' => 'Novedad leida',
                'description' => (string) ('Leiste: ' . (optional($read->novedad)->title ?? 'Novedad del sistema')),
            ]];

            if ($read->archived_at) {
                $items[] = [
                    'at' => $read->archived_at,
                    'icon' => 'fas fa-archive',
                    'title' => 'Novedad archivada',
                    'description' => (string) ('Archivaste: ' . (optional($read->novedad)->title ?? 'Novedad del sistema')),
                ];
            }

            return $items;
        });

        return $profileMapped
            ->concat($novedadMapped)
            ->filter(fn ($item) => !empty($item['at']))
            ->sortByDesc('at')
            ->take(10)
            ->values();
    }
}

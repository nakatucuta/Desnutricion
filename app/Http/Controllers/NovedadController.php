<?php

namespace App\Http\Controllers;

use App\Models\Novedad;
use App\Models\NovedadRead;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NovedadController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $baseQuery = function () use ($userId) {
            return Novedad::query()
                ->where('is_active', true)
                ->with(['creator:id,name', 'reads' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)->select('id', 'novedad_id', 'user_id', 'read_at');
                }]);
        };

        $unreadNovedades = $baseQuery()
            ->whereDoesntHave('reads', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->orderByDesc('id')
            ->get();

        $readNovedades = $baseQuery()
            ->whereHas('reads', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNull('archived_at');
            })
            ->orderByDesc('id')
            ->get();

        $archivedNovedades = $baseQuery()
            ->whereHas('reads', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNotNull('archived_at');
            })
            ->orderByDesc('id')
            ->get();

        $allNovedades = $unreadNovedades
            ->concat($readNovedades)
            ->concat($archivedNovedades)
            ->sortByDesc('id')
            ->values();

        $unreadCount = Novedad::unreadCountForUser($userId);
        $readCount = Novedad::query()
            ->where('is_active', true)
            ->whereHas('reads', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNull('archived_at');
            })
            ->count();

        $archivedCount = Novedad::query()
            ->where('is_active', true)
            ->whereHas('reads', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNotNull('archived_at');
            })
            ->count();

        return view('novedades.index', [
            'unreadNovedades' => $unreadNovedades,
            'readNovedades' => $readNovedades,
            'archivedNovedades' => $archivedNovedades,
            'allNovedades' => $allNovedades,
            'canPublish' => $this->isAdmin($user),
            'unreadCount' => $unreadCount,
            'readCount' => $readCount,
            'archivedCount' => $archivedCount,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($this->isAdmin($user), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:160',
            'message' => 'required|string|max:5000',
        ]);

        Novedad::create([
            'title' => trim((string) $validated['title']),
            'message' => trim((string) $validated['message']),
            'created_by' => (int) $user->id,
            'is_active' => true,
        ]);

        return back()->with('status', 'Novedad publicada para todos los usuarios.');
    }

    public function markRead(Novedad $novedad)
    {
        $userId = (int) Auth::id();

        NovedadRead::firstOrCreate(
            [
                'novedad_id' => (int) $novedad->id,
                'user_id' => $userId,
            ],
            [
                'read_at' => now(),
            ]
        );

        return redirect()->route('novedades.index')
            ->with('status', 'Novedad marcada como leida.');
    }

    public function markAllRead()
    {
        $userId = (int) Auth::id();

        $ids = Novedad::query()
            ->where('is_active', true)
            ->whereDoesntHave('reads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->pluck('id');

        foreach ($ids as $id) {
            NovedadRead::firstOrCreate(
                [
                    'novedad_id' => (int) $id,
                    'user_id' => $userId,
                ],
                [
                    'read_at' => now(),
                ]
            );
        }

        return redirect()->route('novedades.index')
            ->with('status', 'Todas las novedades quedaron marcadas como leidas.');
    }

    public function archive(Novedad $novedad)
    {
        $userId = (int) Auth::id();

        $read = NovedadRead::query()
            ->where('novedad_id', (int) $novedad->id)
            ->where('user_id', $userId)
            ->first();

        if (!$read) {
            return redirect()->route('novedades.index')->with('status', 'Primero debes marcar la novedad como leida.');
        }

        if ($read->archived_at === null) {
            $read->archived_at = now();
            $read->save();
        }

        return redirect()->route('novedades.index')->with('status', 'Novedad archivada.');
    }

    public function unarchive(Novedad $novedad)
    {
        $userId = (int) Auth::id();

        $read = NovedadRead::query()
            ->where('novedad_id', (int) $novedad->id)
            ->where('user_id', $userId)
            ->first();

        if ($read && $read->archived_at !== null) {
            $read->archived_at = null;
            $read->save();
        }

        return redirect()->route('novedades.index')->with('status', 'Novedad desarchivada.');
    }

    public function audit(Novedad $novedad)
    {
        $user = Auth::user();
        abort_unless($this->isAdmin($user), 403);

        $data = $this->buildAuditData($novedad);

        return view('novedades.audit', $data);
    }

    public function auditPdf(Novedad $novedad)
    {
        $user = Auth::user();
        abort_unless($this->isAdmin($user), 403);

        $data = $this->buildAuditData($novedad);
        $pdf = Pdf::loadView('novedades.audit_pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('auditoria_novedad_' . $novedad->id . '.pdf');
    }

    private function buildAuditData(Novedad $novedad): array
    {
        $reads = NovedadRead::query()
            ->where('novedad_id', (int) $novedad->id)
            ->with('user:id,name,email,codigohabilitacion')
            ->orderByDesc('read_at')
            ->get();

        $readUserIds = $reads->pluck('user_id')->unique()->values();

        $notReadUsers = User::query()
            ->when($readUserIds->isNotEmpty(), function ($query) use ($readUserIds) {
                $query->whereNotIn('id', $readUserIds->all());
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'codigohabilitacion']);

        $totalUsers = User::query()->count();
        $totalReads = $reads->count();

        return [
            'novedad' => $novedad,
            'reads' => $reads,
            'notReadUsers' => $notReadUsers,
            'totalUsers' => $totalUsers,
            'totalReads' => $totalReads,
            'totalPending' => max($totalUsers - $totalReads, 0),
        ];
    }

    private function isAdmin($user): bool
    {
        return in_array((string) $user->usertype, ['1', '3'], true);
    }
}

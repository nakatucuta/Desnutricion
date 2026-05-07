<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AccessControlService;
use Illuminate\Console\Command;

class BootstrapModulePermissions extends Command
{
    protected $signature = 'access-control:bootstrap {--dry-run : Solo muestra cambios sin guardar}';
    protected $description = 'Asigna permisos iniciales por modulo segun uso historico y aplica regla estricta para usuarios _ges';

    public function __construct(private readonly AccessControlService $accessControl)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->accessControl->ensureCatalogExists();

        $inference = $this->accessControl->inferPermissionsFromUsage();
        $paiIds = collect($inference['pai'])->map(static fn ($id) => (int) $id)->unique();
        $nutricionalIds = collect($inference['nutricional'])->map(static fn ($id) => (int) $id)->unique();
        $gestantesIds = collect($inference['gestantes'])->map(static fn ($id) => (int) $id)->unique();
        $tamizajesIds = collect($inference['tamizajes'])->map(static fn ($id) => (int) $id)->unique();
        $ciclosvidaIds = collect($inference['ciclosvida'])->map(static fn ($id) => (int) $id)->unique();
        $gesExclusiveIds = collect($inference['ges_exclusive'])->map(static fn ($id) => (int) $id)->unique();

        $affected = 0;
        $grants = 0;
        $revokes = 0;

        $users = User::query()->whereIn(
            'id',
            $paiIds
                ->merge($nutricionalIds)
                ->merge($gestantesIds)
                ->merge($tamizajesIds)
                ->merge($ciclosvidaIds)
                ->merge($gesExclusiveIds)
                ->unique()
                ->all()
        )->get();
        foreach ($users as $user) {
            $desired = collect();

            if ($this->accessControl->isSuperAdmin($user)) {
                $desired = collect(array_map(
                    static fn (array $module) => (string) $module['code'],
                    $this->accessControl->moduleCatalog()
                ));
            } elseif ($gesExclusiveIds->contains((int) $user->id)) {
                $desired->push(AccessControlService::GESTANTES_ACCESS);
            } else {
                if ($paiIds->contains((int) $user->id)) {
                    $desired->push(AccessControlService::PAI_ACCESS);
                }
                if ($nutricionalIds->contains((int) $user->id)) {
                    $desired->push(AccessControlService::NUTRICIONAL_ACCESS);
                }
                if ($gestantesIds->contains((int) $user->id)) {
                    $desired->push(AccessControlService::GESTANTES_ACCESS);
                }
                if ($tamizajesIds->contains((int) $user->id)) {
                    $desired->push(AccessControlService::TAMIZAJES_ACCESS);
                }
                if ($ciclosvidaIds->contains((int) $user->id)) {
                    $desired->push(AccessControlService::CICLOSVIDA_ACCESS);
                }
            }

            $desiredCodes = $desired->unique()->sort()->values()->all();
            $currentCodes = $user->modulePermissions()
                ->with('modulePermission:id,code')
                ->get()
                ->map(static fn ($row) => (string) ($row->modulePermission->code ?? ''))
                ->filter()
                ->sort()
                ->values()
                ->all();

            $needsSync = array_values($currentCodes) !== array_values($desiredCodes)
                || ($gesExclusiveIds->contains((int) $user->id)
                    && (in_array(AccessControlService::PAI_ACCESS, $currentCodes, true)
                        || in_array(AccessControlService::NUTRICIONAL_ACCESS, $currentCodes, true)
                        || in_array(AccessControlService::TAMIZAJES_ACCESS, $currentCodes, true)
                        || in_array(AccessControlService::CICLOSVIDA_ACCESS, $currentCodes, true)));

            if (!$needsSync) {
                continue;
            }

            $affected++;
            $grants += count(array_diff($desiredCodes, $currentCodes));
            $revokes += count(array_diff($currentCodes, $desiredCodes));

            if (!$dryRun) {
                $this->accessControl->syncUserPermissions($user, $desiredCodes, $this->accessControl->superAdminId());
            }
        }

        // Superadmin siempre con gestion completa.
        $superAdmin = User::query()->find($this->accessControl->superAdminId());
        if ($superAdmin && !$dryRun) {
            $allCodes = array_map(static fn (array $module) => (string) $module['code'], $this->accessControl->moduleCatalog());
            $this->accessControl->syncUserPermissions($superAdmin, $allCodes, $this->accessControl->superAdminId());
        }

        $this->info('Permisos de modulos procesados.');
        $this->line('Usuarios evaluados: ' . count($users));
        $this->line('Usuarios ajustados: ' . $affected);
        $this->line('Permisos agregados: ' . $grants);
        $this->line('Permisos retirados: ' . $revokes);
        $this->line('Modo: ' . ($dryRun ? 'SIMULACION (sin cambios)' : 'APLICADO'));

        return self::SUCCESS;
    }
}

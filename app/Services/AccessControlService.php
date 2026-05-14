<?php

namespace App\Services;

use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccessControlService
{
    public const GESTANTES_ACCESS = 'gestantes.access';
    public const PAI_ACCESS = 'pai.access';
    public const NUTRICIONAL_ACCESS = 'nutricional.access';
    public const TAMIZAJES_ACCESS = 'tamizajes.access';
    public const CICLOSVIDA_ACCESS = 'ciclosvida.access';
    public const PERMISSIONS_MANAGE = 'permissions.manage';

    /**
     * @return array<int, array{code:string,name:string,description:?string,assignable:bool}>
     */
    public function moduleCatalog(): array
    {
        return (array) config('access_control.module_permissions', []);
    }

    public function superAdminId(): int
    {
        return (int) config('access_control.super_admin_id', 33);
    }

    public function isSuperAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return (int) $user->id === $this->superAdminId();
    }

    public function isAdministratorUsertype(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return (int) ($user->usertype ?? 0) === 1;
    }

    public function isGesExclusiveUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $name = strtolower(trim((string) $user->name));
        $code = strtolower(trim((string) $user->codigohabilitacion));

        return str_ends_with($name, '_ges') || str_ends_with($code, '_ges');
    }

    public function canAccessPermission(?User $user, string $permissionCode): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->isSuperAdmin($user) || $this->isAdministratorUsertype($user)) {
            return true;
        }

        if ($this->isGesExclusiveUser($user) && $permissionCode !== self::GESTANTES_ACCESS) {
            return false;
        }

        if ($permissionCode === self::GESTANTES_ACCESS && $this->isGesExclusiveUser($user)) {
            return true;
        }

        if (!Schema::hasTable('module_permissions') || !Schema::hasTable('user_module_permissions')) {
            return !$this->isGesExclusiveUser($user);
        }

        return $this->userHasPermission($user, $permissionCode);
    }

    public function userHasPermission(User $user, string $permissionCode): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (!Schema::hasTable('module_permissions') || !Schema::hasTable('user_module_permissions')) {
            return false;
        }

        if ($user->relationLoaded('modulePermissions')) {
            return $user->modulePermissions->contains(
                static fn (UserModulePermission $row) => ($row->modulePermission->code ?? null) === $permissionCode
            );
        }

        return UserModulePermission::query()
            ->where('user_id', (int) $user->id)
            ->whereHas('modulePermission', function ($q) use ($permissionCode) {
                $q->where('code', $permissionCode);
            })
            ->exists();
    }

    public function ensureCatalogExists(): void
    {
        if (!Schema::hasTable('module_permissions')) {
            return;
        }

        foreach ($this->moduleCatalog() as $module) {
            ModulePermission::query()->updateOrCreate(
                ['code' => (string) $module['code']],
                [
                    'name' => (string) $module['name'],
                    'description' => $module['description'] ?? null,
                    'is_assignable' => (bool) ($module['assignable'] ?? true),
                ]
            );
        }
    }

    public function permissionByCode(string $code): ?ModulePermission
    {
        return ModulePermission::query()->where('code', $code)->first();
    }

    public function assignPermission(User $user, string $permissionCode, ?int $grantedByUserId = null, ?string $notes = null): void
    {
        $permission = $this->permissionByCode($permissionCode);
        if (!$permission) {
            return;
        }

        UserModulePermission::query()->updateOrCreate(
            [
                'user_id' => (int) $user->id,
                'module_permission_id' => (int) $permission->id,
            ],
            [
                'granted_by_user_id' => $grantedByUserId,
                'notes' => $notes,
            ]
        );
    }

    public function revokePermission(User $user, string $permissionCode): void
    {
        $permission = $this->permissionByCode($permissionCode);
        if (!$permission) {
            return;
        }

        UserModulePermission::query()
            ->where('user_id', (int) $user->id)
            ->where('module_permission_id', (int) $permission->id)
            ->delete();
    }

    /**
     * @param array<int, string> $permissionCodes
     */
    public function syncUserPermissions(User $user, array $permissionCodes, ?int $grantedByUserId = null): void
    {
        $this->ensureCatalogExists();
        $permissionCodes = array_values(array_unique(array_filter($permissionCodes)));

        if ($this->isSuperAdmin($user)) {
            $permissionCodes = array_map(
                static fn (array $module) => (string) $module['code'],
                $this->moduleCatalog()
            );
        }

        if ($this->isGesExclusiveUser($user)) {
            $permissionCodes = [self::GESTANTES_ACCESS];
        }

        $permissionIds = ModulePermission::query()
            ->whereIn('code', $permissionCodes)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->values()
            ->all();

        DB::transaction(function () use ($user, $permissionIds, $grantedByUserId) {
            UserModulePermission::query()->where('user_id', (int) $user->id)->delete();

            foreach ($permissionIds as $permissionId) {
                DB::table('user_module_permissions')->insert([
                    'user_id' => (int) $user->id,
                    'module_permission_id' => $permissionId,
                    'granted_by_user_id' => $grantedByUserId,
                    'created_at' => DB::raw('GETDATE()'),
                    'updated_at' => DB::raw('GETDATE()'),
                ]);
            }
        });
    }

    public function resolvePermissionForRequest(Request $request): ?string
    {
        $path = trim($request->path(), '/');
        $map = (array) config('access_control.route_permission_map', []);

        foreach ($map as $permissionCode => $patterns) {
            foreach ((array) $patterns as $pattern) {
                if ($request->is((string) $pattern)) {
                    return (string) $permissionCode;
                }
            }
        }

        return null;
    }

    public function gesAllowedRequest(Request $request): bool
    {
        foreach ((array) config('access_control.ges_allowed_patterns', []) as $pattern) {
            if ($request->is((string) $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, ModulePermission>
     */
    public function assignablePermissions(): Collection
    {
        return ModulePermission::query()
            ->where('is_assignable', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array{pai: array<int>, nutricional: array<int>, gestantes: array<int>, tamizajes: array<int>, ciclosvida: array<int>, ges_exclusive: array<int>}
     */
    public function inferPermissionsFromUsage(): array
    {
        $pai = collect()
            ->merge(DB::table('vacunas')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->merge(DB::table('afiliados')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->filter(static fn ($id) => (int) $id > 0)
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $nutricional = collect()
            ->merge(DB::table('sivigilas')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->merge(DB::table('cargue412s')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->merge(DB::table('seguimiento_412s')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->merge(DB::table('seguimientos')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->filter(static fn ($id) => (int) $id > 0)
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $gestantes = collect()
            ->merge(DB::table('ges_tipo1')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->merge(DB::table('ges_tipo3')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->merge(DB::table('preconcepcional_import_batches')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->merge(DB::table('asignaciones_maestrosiv549')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->filter(static fn ($id) => (int) $id > 0)
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $tamizajes = collect()
            ->merge(DB::table('tamizajes')->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->filter(static fn ($id) => (int) $id > 0)
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        // Cursos de vida no guarda user_id directo del consumo de modulo.
        // Se hereda por uso historico del ecosistema PAI.
        $ciclosvida = collect($pai)
            ->merge($tamizajes)
            ->filter(static fn ($id) => (int) $id > 0)
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $gesExclusive = User::query()
            ->whereRaw("LOWER([name]) LIKE '%[_]ges'")
            ->orWhereRaw("LOWER([codigohabilitacion]) LIKE '%[_]ges'")
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->values()
            ->all();

        return [
            'pai' => $pai,
            'nutricional' => $nutricional,
            'gestantes' => $gestantes,
            'tamizajes' => $tamizajes,
            'ciclosvida' => $ciclosvida,
            'ges_exclusive' => $gesExclusive,
        ];
    }
}

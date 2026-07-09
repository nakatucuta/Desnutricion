<?php

namespace App\Services;

use App\Models\ModulePermission;
use App\Models\UserAccessEvent;
use App\Models\User;
use App\Models\UserModulePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AccessControlService
{
    public const GESTANTES_ACCESS = 'gestantes.access';
    public const PAI_ACCESS = 'pai.access';
    public const NUTRICIONAL_ACCESS = 'nutricional.access';
    public const TAMIZAJES_ACCESS = 'tamizajes.access';
    public const CICLOSVIDA_ACCESS = 'ciclosvida.access';
    public const PERMISSIONS_MANAGE = 'permissions.manage';

    private ?bool $accessControlTablesAvailable = null;

    /** @var array<int, array<int, string>> */
    private array $cachedPermissionCodesByUser = [];

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

        if (!$this->hasAccessControlTables()) {
            return !$this->isGesExclusiveUser($user);
        }

        return $this->userHasPermission($user, $permissionCode);
    }

    public function userHasPermission(User $user, string $permissionCode): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (!$this->hasAccessControlTables()) {
            return false;
        }

        return in_array($permissionCode, $this->permissionCodesForUser($user), true);
    }

    public function ensureCatalogExists(): void
    {
        if (!$this->hasAccessControlTables()) {
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

        unset($this->cachedPermissionCodesByUser[(int) $user->id]);
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

        unset($this->cachedPermissionCodesByUser[(int) $user->id]);
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

        unset($this->cachedPermissionCodesByUser[(int) $user->id]);
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

    public function resolveLoginUser(Request $request): ?User
    {
        if ($request->filled('email')) {
            $email = mb_strtolower(trim((string) $request->input('email')));

            if ($email === '') {
                return null;
            }

            return User::query()
                ->whereRaw('LOWER([email]) = ?', [$email])
                ->first();
        }

        $identifier = trim((string) $request->input('codigohabilitacion'));
        if ($identifier === '') {
            return null;
        }

        $users = User::query()
            ->where('codigohabilitacion', $identifier)
            ->orWhere('name', $identifier)
            ->limit(2)
            ->get();

        return $users->count() === 1 ? $users->first() : null;
    }

    public function recordAuthEvent(string $eventType, Request $request, ?User $user = null, array $details = []): void
    {
        if (!Schema::hasTable('user_access_events')) {
            return;
        }

        $resolvedUser = $user ?: $this->resolveLoginUser($request);
        $identifier = $request->filled('email')
            ? mb_strtolower(trim((string) $request->input('email')))
            : trim((string) $request->input('codigohabilitacion'));

        $event = new UserAccessEvent([
            'user_id' => $resolvedUser?->id,
            'event_type' => $eventType,
            'login_identifier' => $identifier !== '' ? $identifier : null,
            'identifier_hash' => $identifier !== '' ? hash('sha256', $identifier) : null,
            'auth_method' => $request->filled('email') ? 'email' : 'codigo',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            'session_id' => $request->session()?->getId(),
            'route_name' => $request->route()?->getName(),
            'details' => $this->formatEventDetails($details),
            'occurred_at' => now(),
        ]);

        try {
            $event->save();
        } catch (Throwable $e) {
            Log::warning('No se pudo registrar el evento de acceso.', [
                'event_type' => $eventType,
                'user_id' => $resolvedUser?->id,
                'route_name' => $request->route()?->getName(),
                'message' => $e->getMessage(),
            ]);

            return;
        }

        if (!$resolvedUser) {
            return;
        }

        if ($eventType === 'login_success') {
            if (!Schema::hasColumn('users', 'login_count') || !Schema::hasColumn('users', 'last_login_at')) {
                return;
            }

            User::query()->whereKey($resolvedUser->id)->update([
                'login_count' => DB::raw('ISNULL([login_count], 0) + 1'),
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_login_user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                'updated_at' => now(),
            ]);
        } elseif ($eventType === 'login_failed') {
            if (!Schema::hasColumn('users', 'failed_login_count')) {
                return;
            }

            User::query()->whereKey($resolvedUser->id)->update([
                'failed_login_count' => DB::raw('ISNULL([failed_login_count], 0) + 1'),
                'updated_at' => now(),
            ]);
        } elseif ($eventType === 'logout') {
            if (!Schema::hasColumn('users', 'logout_count') || !Schema::hasColumn('users', 'last_logout_at')) {
                return;
            }

            User::query()->whereKey($resolvedUser->id)->update([
                'logout_count' => DB::raw('ISNULL([logout_count], 0) + 1'),
                'last_logout_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function formatEventDetails(array $details): ?string
    {
        $clean = collect($details)
            ->filter(static fn ($value) => $value !== null && $value !== '')
            ->map(function ($value, $key) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } elseif (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                return $key . '=' . (string) $value;
            })
            ->implode(' | ');

        return $clean !== '' ? $clean : null;
    }

    public function loginEventStats(): array
    {
        if (!Schema::hasTable('user_access_events')) {
            return [
                'successful_logins' => 0,
                'failed_logins' => 0,
                'logouts' => 0,
                'today_successful_logins' => 0,
                'today_failed_logins' => 0,
                'last_successful_login_at' => null,
                'last_failed_login_at' => null,
                'active_users_today' => 0,
            ];
        }

        $today = now()->toDateString();

        return [
            'successful_logins' => (int) UserAccessEvent::query()->successful()->count(),
            'failed_logins' => (int) UserAccessEvent::query()->failed()->count(),
            'logouts' => (int) UserAccessEvent::query()->logout()->count(),
            'today_successful_logins' => (int) UserAccessEvent::query()
                ->successful()
                ->whereDate('occurred_at', $today)
                ->count(),
            'today_failed_logins' => (int) UserAccessEvent::query()
                ->failed()
                ->whereDate('occurred_at', $today)
                ->count(),
            'last_successful_login_at' => UserAccessEvent::query()->successful()->max('occurred_at'),
            'last_failed_login_at' => UserAccessEvent::query()->failed()->max('occurred_at'),
            'active_users_today' => (int) UserAccessEvent::query()
                ->successful()
                ->whereDate('occurred_at', $today)
                ->whereNotNull('user_id')
                ->distinct()
                ->count('user_id'),
        ];
    }

    public function topLoginUsers(int $limit = 10): Collection
    {
        if (
            !Schema::hasColumn('users', 'login_count')
            || !Schema::hasColumn('users', 'failed_login_count')
            || !Schema::hasColumn('users', 'last_login_at')
            || !Schema::hasColumn('users', 'last_login_ip')
        ) {
            return collect();
        }

        return User::query()
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.codigohabilitacion',
                'users.login_count',
                'users.failed_login_count',
                'users.last_login_at',
                'users.last_login_ip',
            ])
            ->orderByDesc('login_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<int, string>
     */
    private function permissionCodesForUser(User $user): array
    {
        $userId = (int) $user->id;

        if (array_key_exists($userId, $this->cachedPermissionCodesByUser)) {
            return $this->cachedPermissionCodesByUser[$userId];
        }

        $user->loadMissing('modulePermissions.modulePermission');

        $codes = $user->modulePermissions
            ->map(static fn (UserModulePermission $row) => (string) ($row->modulePermission->code ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $this->cachedPermissionCodesByUser[$userId] = $codes;
    }

    private function hasAccessControlTables(): bool
    {
        if ($this->accessControlTablesAvailable !== null) {
            return $this->accessControlTablesAvailable;
        }

        return $this->accessControlTablesAvailable =
            Schema::hasTable('module_permissions') && Schema::hasTable('user_module_permissions');
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

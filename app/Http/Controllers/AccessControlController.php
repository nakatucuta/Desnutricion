<?php

namespace App\Http\Controllers;

use App\Models\ModuleAccessRequest;
use App\Models\ModulePermission;
use App\Models\User;
use App\Services\AccessControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Yajra\DataTables\Facades\DataTables;

class AccessControlController extends Controller
{
    public function __construct(private readonly AccessControlService $accessControl)
    {
    }

    public function index(Request $request)
    {
        $permissions = ModulePermission::query()
            ->where('is_assignable', true)
            ->orderBy('name')
            ->get();

        $pendingRequests = ModuleAccessRequest::query()
            ->with(['user:id,name,email', 'modulePermission:id,code,name'])
            ->where('status', 'pending')
            ->latest('id')
            ->limit(40)
            ->get();

        return view('access_control.index', [
            'permissions' => $permissions,
            'pendingRequests' => $pendingRequests,
            'service' => $this->accessControl,
        ]);
    }

    public function data(Request $request)
    {
        $permissions = ModulePermission::query()
            ->where('is_assignable', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $query = User::query()
            ->select(['id', 'name', 'email', 'codigohabilitacion', 'usertype'])
            ->with([
                'modulePermissions' => function ($q) {
                    $q->select(['id', 'user_id', 'module_permission_id']);
                },
                'modulePermissions.modulePermission' => function ($q) {
                    $q->select(['id', 'code']);
                },
            ]);

        return DataTables::eloquent($query)
            ->filterColumn('user_label', function ($q, $keyword) {
                $q->where('name', 'like', '%' . $keyword . '%');
            })
            ->addColumn('select_html', function (User $user) {
                return '<input type="checkbox" class="ac-user-select" value="' . e((string) $user->id) . '" aria-label="Seleccionar usuario">';
            })
            ->addColumn('user_label', function (User $user) {
                $label = e((string) $user->name);
                if ($this->accessControl->isSuperAdmin($user)) {
                    $label .= ' <span class="badge badge-danger ml-1">Superadmin</span>';
                } elseif ($this->accessControl->isGesExclusiveUser($user)) {
                    $label .= ' <span class="badge badge-info ml-1">_ges</span>';
                }
                return $label;
            })
            ->addColumn('usertype_html', function (User $user) {
                $formId = 'f-user-' . $user->id;
                $labels = [
                    1 => '1 - Administrador',
                    2 => '2 - Prestador',
                    3 => '3 - Nutricionista',
                ];

                $html = '<select name="usertype" form="' . e($formId) . '" class="form-control form-control-sm ac-type-select">';
                foreach ($labels as $value => $label) {
                    $selected = (int) ($user->usertype ?? 0) === (int) $value ? ' selected' : '';
                    $html .= '<option value="' . e((string) $value) . '"' . $selected . '>' . e($label) . '</option>';
                }
                $html .= '</select>';

                return $html;
            })
            ->addColumn('permissions_html', function (User $user) use ($permissions) {
                $currentCodes = $user->modulePermissions
                    ->map(fn ($row) => $row->modulePermission->code ?? null)
                    ->filter()
                    ->values()
                    ->all();

                $isGes = $this->accessControl->isGesExclusiveUser($user);
                $formId = 'f-user-' . $user->id;
                $html = '<form id="' . e($formId) . '" method="POST" action="' . e(route('access-control.users.update', $user)) . '">';
                $html .= csrf_field() . method_field('PUT');

                foreach ($permissions as $permission) {
                    $code = (string) $permission->code;
                    $disabled = $isGes && $code !== AccessControlService::GESTANTES_ACCESS;
                    $checked = in_array($code, $currentCodes, true);
                    $inputId = 'u' . $user->id . '-p' . $permission->id;

                    $html .= '<div class="form-check form-check-inline mr-3">';
                    $html .= '<input type="checkbox" class="form-check-input" name="permissions[]" value="' . e($code) . '" id="' . e($inputId) . '"';
                    $html .= $checked ? ' checked' : '';
                    $html .= $disabled ? ' disabled' : '';
                    $html .= '>';
                    $html .= '<label class="form-check-label" for="' . e($inputId) . '">' . e((string) $permission->name) . '</label>';
                    $html .= '</div>';
                }

                if ($isGes) {
                    $html .= '<small class="text-muted d-block">Usuario _ges: solo puede tener permiso de gestantes.</small>';
                }

                $html .= '</form>';

                return $html;
            })
            ->addColumn('action_html', function (User $user) {
                $formId = 'f-user-' . $user->id;
                $resetPayload = e(json_encode([
                    'id' => (int) $user->id,
                    'name' => (string) ($user->name ?? ('Usuario #' . $user->id)),
                    'url' => route('access-control.users.password.reset', $user),
                ]));

                return '<button type="submit" form="' . e($formId) . '" class="btn btn-sm btn-success btn-block mb-2">Guardar</button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger btn-block js-reset-user-password" data-user="' . $resetPayload . '">'
                    . '<i class="fas fa-key mr-1"></i>Reset</button>';
            })
            ->rawColumns(['select_html', 'user_label', 'usertype_html', 'permissions_html', 'action_html'])
            ->toJson();
    }

    public function updateUserPermissions(Request $request, User $user)
    {
        $codes = ModulePermission::query()
            ->where('is_assignable', true)
            ->pluck('code')
            ->map(static fn ($code) => (string) $code)
            ->values()
            ->all();

        $validated = $request->validate([
            'usertype' => ['required', 'integer', Rule::in([1, 2, 3])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($codes)],
        ]);

        $user->usertype = (int) $validated['usertype'];
        $user->save();

        $requested = (int) $user->usertype === 1
            ? $codes
            : array_values(array_unique((array) ($validated['permissions'] ?? [])));
        $this->accessControl->syncUserPermissions($user, $requested, (int) Auth::id());

        return redirect()
            ->route('access-control.index')
            ->with('success', 'Permisos actualizados para: ' . ($user->name ?: ('Usuario #' . $user->id)));
    }

    public function resetUserPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'temporary_password' => ['required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user->forceFill([
            'password' => Hash::make((string) $validated['temporary_password']),
            'force_password_change' => true,
            'password_reset_at' => now(),
        ])->save();

        return redirect()
            ->route('access-control.index')
            ->with('success', 'Contrasena restablecida para ' . ($user->name ?: ('Usuario #' . $user->id)) . '. Al ingresar debera cambiarla.');
    }

    public function resetSelectedPasswords(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'temporary_password' => ['required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $userIds = array_values(array_unique(array_map('intval', $validated['user_ids'])));
        $updated = User::query()->whereIn('id', $userIds)->update([
            'password' => Hash::make((string) $validated['temporary_password']),
            'force_password_change' => true,
            'password_reset_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('access-control.index')
            ->with('success', 'Contrasena restablecida para ' . $updated . ' usuario(s) seleccionado(s). Al ingresar deberan cambiarla.');
    }

    public function resolveRequest(Request $request, ModuleAccessRequest $accessRequest)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'admin_response' => ['nullable', 'string', 'max:500'],
        ]);

        if ($accessRequest->status !== 'pending') {
            return redirect()
                ->route('access-control.index')
                ->with('warning', 'La solicitud ya fue gestionada.');
        }

        $action = $validated['action'];
        $responseText = trim((string) ($validated['admin_response'] ?? ''));

        if ($action === 'approve') {
            $this->accessControl->assignPermission(
                $accessRequest->user,
                (string) $accessRequest->modulePermission->code,
                (int) Auth::id(),
                'Aprobado por solicitud #' . $accessRequest->id
            );
            $accessRequest->status = 'approved';
        } else {
            $accessRequest->status = 'rejected';
        }

        $accessRequest->admin_response = $responseText !== '' ? $responseText : null;
        $accessRequest->resolved_by_user_id = (int) Auth::id();
        $accessRequest->resolved_at = now();
        $accessRequest->save();

        return redirect()
            ->route('access-control.index')
            ->with('success', 'Solicitud #' . $accessRequest->id . ' gestionada (' . strtoupper($accessRequest->status) . ').');
    }
}

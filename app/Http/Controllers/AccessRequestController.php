<?php

namespace App\Http\Controllers;

use App\Mail\SolicitudMail;
use App\Models\ModuleAccessRequest;
use App\Models\ModulePermission;
use App\Models\User;
use App\Services\AccessControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AccessRequestController extends Controller
{
    public function __construct(private readonly AccessControlService $accessControl)
    {
    }

    public function denied(Request $request)
    {
        $permissionCode = trim((string) $request->query('permission', ''));
        $permission = $permissionCode !== ''
            ? ModulePermission::query()->where('code', $permissionCode)->first()
            : null;

        $from = trim((string) $request->query('from', url()->previous()));
        if ($from === '') {
            $from = route('home');
        }

        return view('access_control.denied', [
            'permission' => $permission,
            'permissionCode' => $permissionCode,
            'from' => $from,
            'service' => $this->accessControl,
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $permissions = ModulePermission::query()
            ->where('is_assignable', true)
            ->orderBy('name')
            ->get()
            ->filter(function (ModulePermission $permission) use ($user) {
                return !$this->accessControl->canAccessPermission($user, (string) $permission->code);
            })
            ->values();

        $pendingByPermission = ModuleAccessRequest::query()
            ->where('user_id', (int) $user->id)
            ->where('status', 'pending')
            ->pluck('id', 'module_permission_id');

        return view('access_control.request', [
            'permissions' => $permissions,
            'pendingByPermission' => $pendingByPermission,
            'service' => $this->accessControl,
            'from' => (string) $request->query('from', route('home')),
        ]);
    }

    public function store(Request $request)
    {
        $codes = ModulePermission::query()->pluck('code')->map(static fn ($code) => (string) $code)->all();

        $validated = $request->validate([
            'permission_code' => ['required', 'string', Rule::in($codes)],
            'requested_reason' => ['nullable', 'string', 'max:500'],
            'from' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        abort_unless($user, 401);

        $permissionCode = (string) $validated['permission_code'];
        $permission = ModulePermission::query()->where('code', $permissionCode)->firstOrFail();

        if ($this->accessControl->canAccessPermission($user, $permissionCode)) {
            return redirect()->to($validated['from'] ?? route('home'))
                ->with('success', 'Ya cuentas con acceso a ese modulo.');
        }

        if ($this->accessControl->isGesExclusiveUser($user) && $permissionCode !== AccessControlService::GESTANTES_ACCESS) {
            return back()->with('error1', 'Los usuarios _ges no pueden solicitar acceso fuera del modulo de gestantes.');
        }

        $existingPending = ModuleAccessRequest::query()
            ->where('user_id', (int) $user->id)
            ->where('module_permission_id', (int) $permission->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if ($existingPending) {
            return back()->with('warning', 'Ya tienes una solicitud pendiente para este modulo.');
        }

        $reason = trim((string) ($validated['requested_reason'] ?? ''));

        $accessRequest = ModuleAccessRequest::query()->create([
            'user_id' => (int) $user->id,
            'module_permission_id' => (int) $permission->id,
            'status' => 'pending',
            'requested_reason' => $reason !== '' ? $reason : null,
        ]);

        $this->notifyAdministrators($user, $permission, $accessRequest, $reason);

        return back()->with('success', 'Solicitud enviada. El superadministrador revisara tu acceso.');
    }

    private function notifyAdministrators(User $user, ModulePermission $permission, ModuleAccessRequest $accessRequest, string $reason): void
    {
        $recipients = collect((array) config('access_control.request_recipients', []))
            ->filter()
            ->values()
            ->all();

        if (empty($recipients)) {
            $superAdmin = User::query()->find($this->accessControl->superAdminId());
            if (!empty($superAdmin?->email)) {
                $recipients[] = (string) $superAdmin->email;
            }
        }

        if (empty($recipients)) {
            return;
        }

        $details = [
            'subject' => 'Nueva solicitud de acceso de modulo',
            'message' => 'Solicitud #' . $accessRequest->id . ' | Usuario: ' . $user->name . ' (' . $user->email . ')' .
                ' | Modulo: ' . $permission->name .
                ($reason !== '' ? (' | Motivo: ' . $reason) : ''),
            'fromEmail' => (string) $user->email,
            'patientName' => $permission->name,
        ];

        Mail::to($recipients)->send(new SolicitudMail($details));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ProfileChangeAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return view('profile.edit', [
            'user' => $user,
            'canViewAudit' => $this->isAdmin($user),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email:rfc|max:150|unique:users,email,' . $user->id,
            'codigohabilitacion' => 'nullable|string|max:80',
        ]);

        $old = [
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'codigohabilitacion' => (string) ($user->codigohabilitacion ?? ''),
        ];

        $user->name = trim((string) $validated['name']);
        $user->email = mb_strtolower(trim((string) $validated['email']));
        $user->codigohabilitacion = trim((string) ($validated['codigohabilitacion'] ?? ''));

        if (!$user->isDirty(['name', 'email', 'codigohabilitacion'])) {
            return back()->with('status', 'No se detectaron cambios en tu perfil.');
        }

        $changedFields = array_keys($user->getDirty());
        $new = [
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'codigohabilitacion' => (string) ($user->codigohabilitacion ?? ''),
        ];

        $user->save();

        $this->createAudit($user->id, $changedFields, $old, $new, $request);
        $this->sendProfileChangeMail($user->id, $old['email'], $new['email'], 'Actualizacion de datos del perfil', $changedFields);

        return back()->with('status', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required',
                'confirmed',
                Password::min(10)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        if (!Hash::check((string) $request->input('current_password'), (string) $user->password)) {
            return back()->withErrors(['current_password' => 'La contrasena actual no es correcta.']);
        }

        if (Hash::check((string) $request->input('password'), (string) $user->password)) {
            return back()->withErrors(['password' => 'La nueva contrasena debe ser diferente a la actual.']);
        }

        $user->password = Hash::make((string) $request->input('password'));
        $user->save();

        $this->createAudit($user->id, ['password'], ['password' => '***'], ['password' => '***actualizada***'], $request);
        $this->sendProfileChangeMail($user->id, (string) $user->email, (string) $user->email, 'Cambio de contrasena del perfil', ['password']);

        return back()->with('status', 'Contrasena actualizada de forma segura.');
    }

    public function auditIndex()
    {
        $current = Auth::user();
        abort_unless($this->isAdmin($current), 403);

        $audits = ProfileChangeAudit::with(['user:id,name,email', 'changedBy:id,name,email'])
            ->orderByDesc('id')
            ->paginate(25);

        return view('profile.audit', compact('audits'));
    }

    private function createAudit(int $userId, array $fields, array $old, array $new, Request $request): void
    {
        ProfileChangeAudit::create([
            'user_id' => $userId,
            'changed_by_id' => Auth::id(),
            'changed_fields' => array_values($fields),
            'old_values' => $old,
            'new_values' => $new,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'changed_at' => now(),
        ]);
    }

    private function sendProfileChangeMail(int $userId, string $oldEmail, string $newEmail, string $action, array $fields): void
    {
        $recipients = array_filter(array_unique([
            'jsuarez@epsianaswayuu.com',
            $oldEmail,
            $newEmail,
        ]));

        foreach ($recipients as $recipient) {
            try {
                Mail::send('emails.profile_changed', [
                    'userId' => $userId,
                    'action' => $action,
                    'fields' => $fields,
                    'changedAt' => now()->format('Y-m-d H:i:s'),
                ], function ($message) use ($recipient, $action) {
                    $message->to($recipient)->subject('[ANAS WAYUU] ' . $action);
                });
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    private function isAdmin($user): bool
    {
        return in_array((string) $user->usertype, ['1', '3'], true);
    }
}

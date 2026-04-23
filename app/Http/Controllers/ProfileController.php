<?php

namespace App\Http\Controllers;

use App\Models\ProfileChangeAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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

        $normalizedName = trim((string) $request->input('name', ''));
        $normalizedEmail = mb_strtolower(trim((string) $request->input('email', '')));
        $normalizedCodigo = trim((string) $request->input('codigohabilitacion', ''));

        $request->merge([
            'name' => $normalizedName,
            'email' => $normalizedEmail,
            'codigohabilitacion' => $normalizedCodigo,
        ]);

        $emailValidation = extension_loaded('intl')
            ? 'email:rfc,dns,spoof,filter'
            : 'email:rfc,dns,filter';

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => [
                'bail',
                'required',
                $emailValidation,
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $email = (string) $value;

                    if (preg_match('/\s/', $email)) {
                        $fail('El correo no puede contener espacios.');
                        return;
                    }

                    if (substr_count($email, '@') !== 1) {
                        $fail('El correo debe contener un solo simbolo @.');
                        return;
                    }

                    [$local, $domain] = explode('@', $email, 2);

                    if ($local === '' || $domain === '') {
                        $fail('El correo debe tener el formato usuario@dominio.com.');
                        return;
                    }

                    if (str_contains($local, '..') || str_contains($domain, '..')) {
                        $fail('El correo no puede tener puntos consecutivos.');
                        return;
                    }

                    if (str_starts_with($local, '.') || str_ends_with($local, '.')) {
                        $fail('La parte antes de @ no puede iniciar ni terminar con punto.');
                        return;
                    }

                    if (!str_contains($domain, '.')) {
                        $fail('El dominio del correo debe incluir una extension valida, por ejemplo .com.');
                        return;
                    }

                    if (preg_match('/[^a-z0-9.-]/i', $domain)) {
                        $fail('El dominio del correo tiene caracteres no permitidos.');
                        return;
                    }

                    $suggestedDomain = $this->suggestEmailDomainTypo($domain);
                    if ($suggestedDomain !== null) {
                        $fail('Parece que el dominio del correo esta mal escrito. Quiza quisiste escribir ' . $suggestedDomain . '.');
                    }
                },
            ],
            'codigohabilitacion' => 'nullable|string|max:80',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Correo invalido. Usa el formato usuario@dominio.com y verifica que el dominio exista.',
            'email.max' => 'El correo no puede superar 150 caracteres.',
            'email.unique' => 'Este correo ya esta siendo usado por otro usuario.',
        ]);

        $old = [
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'codigohabilitacion' => (string) ($user->codigohabilitacion ?? ''),
            'profile_photo_path' => (string) ($user->profile_photo_path ?? ''),
        ];

        $user->name = (string) $validated['name'];
        $user->email = (string) $validated['email'];
        $user->codigohabilitacion = (string) ($validated['codigohabilitacion'] ?? '');

        if ($request->hasFile('profile_photo')) {
            $previousPhoto = (string) ($user->profile_photo_path ?? '');
            $user->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'public');

            if ($previousPhoto !== '' && $previousPhoto !== $user->profile_photo_path) {
                Storage::disk('public')->delete($previousPhoto);
            }
        }

        if (!$user->isDirty(['name', 'email', 'codigohabilitacion', 'profile_photo_path'])) {
            return back()->with('status', 'No se detectaron cambios en tu perfil.');
        }

        $changedFields = array_keys($user->getDirty());
        $new = [
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'codigohabilitacion' => (string) ($user->codigohabilitacion ?? ''),
            'profile_photo_path' => (string) ($user->profile_photo_path ?? ''),
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

    private function suggestEmailDomainTypo(string $domain): ?string
    {
        $domain = mb_strtolower(trim($domain));
        if ($domain === '') {
            return null;
        }

        $knownDomains = [
            'gmail.com',
            'hotmail.com',
            'outlook.com',
            'yahoo.com',
            'icloud.com',
            'live.com',
            'epsianaswayuu.com',
        ];

        if (in_array($domain, $knownDomains, true)) {
            return null;
        }

        $closest = null;
        $closestDistance = 99;

        foreach ($knownDomains as $knownDomain) {
            $distance = levenshtein($domain, $knownDomain);
            if ($distance < $closestDistance) {
                $closestDistance = $distance;
                $closest = $knownDomain;
            }
        }

        return $closestDistance <= 2 ? $closest : null;
    }
}

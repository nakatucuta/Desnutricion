<?php

namespace App\Http\Controllers;

use App\Models\ProfileChangeAudit;
use App\Models\ProfileEmailChange;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $recentAudits = ProfileChangeAudit::where('user_id', (int) $user->id)
            ->orderByDesc('id')
            ->limit(8)
            ->get();
        $pendingEmailChange = $this->getActivePendingEmailChange((int) $user->id);

        return view('profile.edit', [
            'user' => $user,
            'canViewAudit' => $this->isAdmin($user),
            'recentAudits' => $recentAudits,
            'pendingEmailChange' => $pendingEmailChange,
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

        $currentEmail = mb_strtolower(trim((string) ($user->email ?? '')));
        $newEmail = mb_strtolower(trim((string) $validated['email']));

        $old = [
            'name' => (string) $user->name,
            'email' => $currentEmail,
            'codigohabilitacion' => (string) ($user->codigohabilitacion ?? ''),
            'profile_photo_path' => (string) ($user->profile_photo_path ?? ''),
        ];

        $user->name = (string) $validated['name'];
        $user->codigohabilitacion = (string) ($validated['codigohabilitacion'] ?? '');
        $emailChanged = $newEmail !== $currentEmail;

        if ($request->hasFile('profile_photo')) {
            $previousPhoto = (string) ($user->profile_photo_path ?? '');
            $user->profile_photo_path = $this->storeOptimizedProfilePhoto($request->file('profile_photo'));

            if ($previousPhoto !== '' && $previousPhoto !== $user->profile_photo_path) {
                Storage::disk('public')->delete($previousPhoto);
            }
        }

        $hasDataChanges = $user->isDirty(['name', 'codigohabilitacion', 'profile_photo_path']);
        if (!$hasDataChanges && !$emailChanged) {
            return back()->with('status', 'No se detectaron cambios en tu perfil.');
        }

        $statusMessages = [];

        if ($hasDataChanges) {
            $changedFields = array_keys($user->getDirty());
            $new = [
                'name' => (string) $user->name,
                'email' => $currentEmail,
                'codigohabilitacion' => (string) ($user->codigohabilitacion ?? ''),
                'profile_photo_path' => (string) ($user->profile_photo_path ?? ''),
            ];

            $user->save();
            $this->createAudit((int) $user->id, $changedFields, $old, $new, $request);
            $this->sendProfileChangeMail((int) $user->id, $old['email'], $old['email'], 'Actualizacion de datos del perfil', $changedFields);
            $statusMessages[] = 'Perfil actualizado correctamente.';
        }

        if ($emailChanged) {
            $emailChangeThrottleKey = $this->emailChangeThrottleKey((int) $user->id, (string) $request->ip());
            if (RateLimiter::tooManyAttempts($emailChangeThrottleKey, 5)) {
                $retryIn = RateLimiter::availableIn($emailChangeThrottleKey);

                return back()->withErrors([
                    'email' => 'Has superado el limite de solicitudes de cambio de correo. Intenta de nuevo en ' . $retryIn . ' segundos.',
                ])->withInput();
            }

            [$pendingChange, $rawToken] = $this->createEmailChangeRequest($user, $newEmail, $request);
            $this->sendEmailChangeConfirmationMail($user, $newEmail, $rawToken, $pendingChange->expires_at?->format('Y-m-d H:i:s'));
            RateLimiter::hit($emailChangeThrottleKey, 3600);

            $this->createAudit((int) $user->id, ['email_pending_confirmation'], [
                'email' => $currentEmail,
            ], [
                'email' => $newEmail,
                'status' => 'pendiente_confirmacion',
            ], $request);

            $statusMessages[] = 'Te enviamos un enlace al nuevo correo para confirmar el cambio de correo.';
        }

        return back()->with('status', implode(' ', $statusMessages));
    }

    public function resendEmailChangeConfirmation(Request $request)
    {
        $user = Auth::user();
        $pending = $this->getActivePendingEmailChange((int) $user->id);
        if (!$pending) {
            return back()->withErrors(['email' => 'No tienes una solicitud pendiente para reenviar.']);
        }

        $throttleKey = 'profile-email-change-resend:' . (int) $user->id . ':' . (string) $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $retryIn = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => 'Ya reenviaste demasiadas veces. Intenta de nuevo en ' . $retryIn . ' segundos.',
            ]);
        }

        $rawToken = Str::random(64);
        $pending->token_hash = hash('sha256', $rawToken);
        $pending->requested_at = now();
        $pending->expires_at = now()->addHours(24);
        $pending->requested_ip = (string) ($request->ip() ?? '');
        $pending->requested_user_agent = substr((string) $request->userAgent(), 0, 255);
        $pending->save();

        $this->sendEmailChangeConfirmationMail(
            $user,
            (string) $pending->new_email,
            $rawToken,
            $pending->expires_at?->format('Y-m-d H:i:s')
        );
        RateLimiter::hit($throttleKey, 3600);

        return back()->with('status', 'Enlace de confirmacion reenviado correctamente.');
    }

    public function cancelEmailChangeRequest(Request $request)
    {
        $user = Auth::user();
        $pending = $this->getActivePendingEmailChange((int) $user->id);
        if (!$pending) {
            return back()->withErrors(['email' => 'No tienes una solicitud pendiente para cancelar.']);
        }

        $newEmail = (string) $pending->new_email;
        $pending->delete();
        RateLimiter::clear($this->emailChangeThrottleKey((int) $user->id, (string) $request->ip()));

        $this->createAudit((int) $user->id, ['email_change_cancelled'], [
            'email' => (string) $user->email,
        ], [
            'cancelled_new_email' => $newEmail,
        ], $request);

        return back()->with('status', 'Solicitud de cambio de correo cancelada.');
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
        $user->force_password_change = false;
        $user->save();
        try {
            Auth::logoutOtherDevices((string) $request->input('password'));
        } catch (\Throwable $e) {
            report($e);
        }

        $this->createAudit($user->id, ['password'], ['password' => '***'], ['password' => '***actualizada***'], $request);
        $this->sendProfileChangeMail($user->id, (string) $user->email, (string) $user->email, 'Cambio de contrasena del perfil', ['password']);

        return back()->with('status', 'Contrasena actualizada de forma segura. Se cerraron otras sesiones activas.');
    }

    public function confirmEmailChange(Request $request, string $token)
    {
        $tokenHash = hash('sha256', $token);

        $pending = ProfileEmailChange::where('token_hash', $tokenHash)
            ->whereNull('confirmed_at')
            ->orderByDesc('id')
            ->first();

        if (!$pending) {
            return redirect()->route('profile.edit')
                ->withErrors(['email' => 'El enlace de confirmacion no es valido o ya fue usado.']);
        }

        if ((int) Auth::id() !== (int) $pending->user_id) {
            abort(403);
        }

        if ($pending->expires_at !== null && now()->greaterThan($pending->expires_at)) {
            $pending->delete();

            return redirect()->route('profile.edit')
                ->withErrors(['email' => 'El enlace de confirmacion ya vencio. Solicita el cambio de correo nuevamente.']);
        }

        $user = User::find((int) $pending->user_id);
        if (!$user) {
            return redirect()->route('profile.edit')
                ->withErrors(['email' => 'No fue posible completar la confirmacion de correo.']);
        }

        $newEmail = mb_strtolower(trim((string) $pending->new_email));

        $emailTaken = User::where('email', $newEmail)
            ->where('id', '!=', (int) $user->id)
            ->exists();

        if ($emailTaken) {
            $pending->delete();

            return redirect()->route('profile.edit')
                ->withErrors(['email' => 'No se pudo confirmar el correo porque ya esta en uso por otro usuario.']);
        }

        $oldEmail = (string) $user->email;
        $user->email = $newEmail;
        $user->email_verified_at = null;
        $user->save();

        $pending->confirmed_at = now();
        $pending->save();

        $this->createAudit((int) $user->id, ['email'], [
            'email' => $oldEmail,
        ], [
            'email' => $newEmail,
            'confirmed_from_ip' => (string) ($request->ip() ?? ''),
        ], $request);

        $this->sendProfileChangeMail((int) $user->id, $oldEmail, $newEmail, 'Confirmacion de cambio de correo del perfil', ['email']);
        RateLimiter::clear($this->emailChangeThrottleKey((int) $user->id, (string) $request->ip()));

        return redirect()->route('profile.edit')
            ->with('status', 'Correo actualizado y confirmado correctamente.');
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

    private function createEmailChangeRequest(User $user, string $newEmail, Request $request): array
    {
        ProfileEmailChange::where('user_id', (int) $user->id)
            ->whereNull('confirmed_at')
            ->delete();

        $rawToken = Str::random(64);
        $pending = ProfileEmailChange::create([
            'user_id' => (int) $user->id,
            'new_email' => $newEmail,
            'token_hash' => hash('sha256', $rawToken),
            'requested_at' => now(),
            'expires_at' => now()->addHours(24),
            'requested_ip' => (string) ($request->ip() ?? ''),
            'requested_user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return [$pending, $rawToken];
    }

    private function sendEmailChangeConfirmationMail(User $user, string $newEmail, string $rawToken, ?string $expiresAt): void
    {
        try {
            Mail::send('emails.profile_email_confirm', [
                'userName' => (string) $user->name,
                'newEmail' => $newEmail,
                'confirmUrl' => route('profile.email.confirm', ['token' => $rawToken]),
                'expiresAt' => $expiresAt,
            ], function ($message) use ($newEmail) {
                $message->to($newEmail)->subject('[ANAS WAYUU] Confirma el cambio de correo');
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function getActivePendingEmailChange(int $userId): ?ProfileEmailChange
    {
        $pending = ProfileEmailChange::activeForUser($userId)
            ->orderByDesc('id')
            ->first();

        if ($pending && $pending->expires_at !== null && now()->greaterThan($pending->expires_at)) {
            $pending->delete();
            return null;
        }

        return $pending;
    }

    private function emailChangeThrottleKey(int $userId, string $ip): string
    {
        return 'profile-email-change:' . $userId . ':' . $ip;
    }

    private function storeOptimizedProfilePhoto(UploadedFile $file): string
    {
        if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
            return $file->store('profile-photos', 'public');
        }

        try {
            $raw = @file_get_contents($file->getRealPath());
            if ($raw === false) {
                return $file->store('profile-photos', 'public');
            }

            $source = @imagecreatefromstring($raw);
            if ($source === false) {
                return $file->store('profile-photos', 'public');
            }

            $width = imagesx($source);
            $height = imagesy($source);
            if ($width < 1 || $height < 1) {
                imagedestroy($source);
                return $file->store('profile-photos', 'public');
            }

            $cropSize = min($width, $height);
            $srcX = (int) floor(($width - $cropSize) / 2);
            $srcY = (int) floor(($height - $cropSize) / 2);

            $targetSize = 512;
            $canvas = imagecreatetruecolor($targetSize, $targetSize);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);

            $copied = imagecopyresampled(
                $canvas,
                $source,
                0,
                0,
                $srcX,
                $srcY,
                $targetSize,
                $targetSize,
                $cropSize,
                $cropSize
            );

            if (!$copied) {
                imagedestroy($source);
                imagedestroy($canvas);
                return $file->store('profile-photos', 'public');
            }

            ob_start();
            $extension = 'webp';
            $ok = function_exists('imagewebp')
                ? imagewebp($canvas, null, 82)
                : imagejpeg($canvas, null, 85);

            if (!function_exists('imagewebp')) {
                $extension = 'jpg';
            }

            $binary = ob_get_clean();
            imagedestroy($source);
            imagedestroy($canvas);

            if (!$ok || !is_string($binary) || $binary === '') {
                return $file->store('profile-photos', 'public');
            }

            $path = 'profile-photos/' . Str::uuid() . '.' . $extension;
            Storage::disk('public')->put($path, $binary);

            return $path;
        } catch (\Throwable $e) {
            report($e);
            return $file->store('profile-photos', 'public');
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

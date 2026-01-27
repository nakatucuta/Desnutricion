<?php

namespace App\Services;

use App\Models\GestanteAlerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResultadoPositivoAlertMail;
use Illuminate\Support\Facades\DB;

class GestantesAlertService
{
    /**
     * Valores que disparan alerta (ajústalos si quieres).
     */
    public static function isPositive(string $campo, string $label, ?string $valor): bool
    {
        if (!$valor) return false;

        $v = Str::upper(trim($valor));
        $k = Str::lower($campo . ' ' . $label);

        // Reactivo
        if ($v === 'REACTIVO') return true;

        // IgM positivo
        if ($v === 'IGM POSITIVO') return true;

        // Positivo general
        if ($v === 'POSITIVO') return true;

        // Citología: tratar estas como "alerta" (anormal)
        if (Str::contains($k, 'citologia')) {
            return in_array($v, ['ASC-US', 'LSIL', 'HSIL', 'OTRO'], true);
        }

        // Urocultivo / malaria / estreptococo etc: POSITIVO ya cubre.

        return false;
    }

    public static function severity(string $campo, string $label, ?string $valor): string
    {
        if (!$valor) return 'baja';
        $v = Str::upper(trim($valor));
        $k = Str::lower($campo . ' ' . $label);

        if ($v === 'HSIL') return 'alta';
        if ($v === 'REACTIVO') return 'alta';
        if ($v === 'IGM POSITIVO') return 'alta';
        if ($v === 'POSITIVO') return 'media';

        if (Str::contains($k, 'citologia') && in_array($v, ['ASC-US','LSIL'], true)) return 'media';

        return 'media';
    }

    /**
     * Recorre los campos *_resultado_desc del request y genera alertas cuando sean positivas.
     *
     * @param array $labels Mapa campo_base => label (para mostrar bonito)
     */
    public static function scanAndCreateFromSeguimiento(
        Request $request,
        int $gesTipo1Id,
        int $seguimientoId,
        ?int $userId,
        array $labels = []
    ): void
    {
        foreach ($request->all() as $key => $value) {
            // buscamos *_resultado_desc
            if (!Str::endsWith($key, '_resultado_desc')) continue;

            $baseCampo = Str::replaceLast('_desc', '', $key); // ej: vih_tamiz1_resultado
            $label = $labels[$baseCampo] ?? $baseCampo;

            if (!self::isPositive($baseCampo, $label, (string)$value)) {
                continue;
            }

            // pdf path está en el campo base (hidden) o lo puso tu controller cuando subió pdf
            $pdfPath = $request->input($baseCampo);

            $hash = hash('sha256', implode('|', [
                'ges_tipo1',
                $gesTipo1Id,
                $seguimientoId,
                $baseCampo,
                strtoupper((string)$value),
                (string)$pdfPath,
            ]));

            // evitar duplicados
            if (GestanteAlerta::where('hash', $hash)->exists()) {
                continue;
            }

            $sev = self::severity($baseCampo, $label, (string)$value);

                        $now = now()->format('Y-m-d H:i:s');

          $alert = GestanteAlerta::create([
    'user_id'        => $userId,
    'ges_tipo1_id'   => $gesTipo1Id,
    'seguimiento_id' => $seguimientoId,
    'modulo'         => 'ges_tipo1',
    'campo'          => $baseCampo,
    'examen'         => $label,
    'resultado'      => strtoupper((string)$value),
    'severidad'      => $sev,
    'pdf_path'       => $pdfPath,
    'hash'           => $hash,
]);



            // enviar correo
            Mail::to(['rutamp@epsianaswayuu.com', 'jsuarez@epsianaswayuu.com'])->send(new ResultadoPositivoAlertMail($alert));
        }
    }

    /**
     * Convierte storage path -> URL pública.
     */
    public static function publicPdfUrl(?string $pdfPath): ?string
    {
        if (!$pdfPath) return null;

        if (Str::startsWith($pdfPath, ['http://','https://'])) {
            return $pdfPath;
        }

        $path = ltrim($pdfPath, '/');
        return asset('storage/' . $path);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncGestanteUsers extends Command
{
    protected $signature = 'users:sync-gestante
        {--file= : Ruta a archivo JSON con registros}
        {--default-password=Gestante2026* : Contrasena temporal para usuarios nuevos}
        {--dry-run : Simula sin escribir cambios}';

    protected $description = 'Crea o actualiza usuarios del modulo gestante (usertype=2, name terminado en _ges).';

    public function handle(): int
    {
        $rows = $this->loadRows();
        if (empty($rows)) {
            $this->warn('No hay registros para procesar.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $defaultPassword = (string) $this->option('default-password');

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $emails = $this->normalizeEmails($row['emails'] ?? $row['email'] ?? '');
            if (empty($emails)) {
                $skipped++;
                $this->warn('Fila omitida sin email: ' . json_encode($row));
                continue;
            }

            foreach ($emails as $email) {
                $baseName = trim((string) ($row['name'] ?? $row['ips'] ?? Str::before($email, '@')));
                $name = $this->ensureGesSuffix($baseName);

                $codigo = trim((string) ($row['codigohabilitacion'] ?? $row['codigo_habilitacion'] ?? ''));
                if ($codigo === '') {
                    $codigo = 'PEND_' . strtoupper(substr(md5($email), 0, 10));
                }

                $existing = User::where('email', $email)->first();

                if ($existing) {
                    $existing->name = $this->ensureGesSuffix((string) $existing->name);
                    $existing->usertype = 2;

                    // Si el codigo actual es vacio o ya era pendiente, lo reemplaza.
                    $currentCode = trim((string) $existing->codigohabilitacion);
                    if ($currentCode === '' || Str::startsWith($currentCode, 'PEND_')) {
                        $existing->codigohabilitacion = $codigo;
                    }

                    if (!$dryRun) {
                        $existing->save();
                    }

                    $updated++;
                    $this->line("ACTUALIZADO: {$email} -> {$existing->name} ({$existing->codigohabilitacion})");
                    continue;
                }

                $payload = [
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($defaultPassword),
                    'usertype' => 2,
                    'codigohabilitacion' => $codigo,
                ];

                if (!$dryRun) {
                    User::create($payload);
                }

                $created++;
                $this->line("CREADO: {$email} -> {$name} ({$codigo})");
            }
        }

        $this->newLine();
        $this->info("Proceso terminado. Creados: {$created}, Actualizados: {$updated}, Omitidos: {$skipped}");
        $this->warn('Importante: revisa los codigos PEND_* y cambialos por codigos de habilitacion reales.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function loadRows(): array
    {
        $file = $this->option('file');
        if (!empty($file)) {
            if (!is_file($file)) {
                $this->error("Archivo no encontrado: {$file}");
                return [];
            }

            $json = file_get_contents($file);
            $data = json_decode((string) $json, true);
            if (!is_array($data)) {
                $this->error('El archivo debe ser un JSON valido (arreglo de objetos).');
                return [];
            }

            return $data;
        }

        // Base inicial desde listado compartido (editable).
        return [
            ['ips' => 'EZEQ - SALUD IPSI', 'emails' => 'ezeq-saludipsi@hotmail.com;ipsiezegmantenimientosalud@gmail.com'],
            ['ips' => 'I.P.S.I ANIMAJAA WAYUU-01', 'emails' => 'coordinadora.rias@ipsianimajaawayuu.com;insiainmajaawayuu@gmail.com;medicina@insiaimmajaawayuu.com'],
            ['ips' => 'I.P.S.I ANENU-JIA', 'emails' => 'anenu-jjayp@hotmail.com;coordinacion_pym@ipsianenujia.com'],
            ['ips' => 'ASOCABILDOS RIOHACHA', 'emails' => 'serviciosambulatoriosriohacha@asocabildosipsi.com;gerencia@asocabildosipsi.com;asistencial@asocabildosipsi.com'],
            ['ips' => 'IPSI WALE KERU', 'emails' => 'walekeruipsi@hotmail.com'],
            ['ips' => 'MEDIGROUP', 'emails' => 'marioquintero1130@gmail.com;marioquintero@ipsmedigroup.com;ipsmedigroupmaicao@gmail.com;promocionymantenimiento@ipsmedigroup.com'],
            ['ips' => 'ESE REMEDIOS', 'emails' => 'gerencia@esehospitalnsr.gov.co;coordinacionpym@esehospitalnsr.gov.co;coordinacionpym@esehospitalnsr.gov.co'],
            ['ips' => 'IPSI ANOUTA VAKUAIPA', 'emails' => 'pyriohacha.anoutaips@gmail.com;coordinmedicariohacha.anoutaips@gmail.com;coordimedicamanaure.anoutaips@gmail.com;mperinatalmanaure.anoutaips@gmail.com'],
            ['ips' => 'ESE HOSPITAL ARMANDO PABON', 'emails' => 'coordpym@esehospitalapl-manaure-laguajira.gov.co;gerencia@esehospitalapl-manaure-laguajira.gov.co;coordpym@esehospitalapl-manaure-laguajira.gov.co'],
            ['ips' => 'HOSPITAL NUESTRA SENORA DEL PILAR', 'emails' => 'pyphnsp2@gmail.com;pyphnsp@gmail.com;pymhnspilar1@gmail.com;pym@hospitalbarrancas.gov.co'],
            ['ips' => 'IPSI OUTTAJIAPULEE', 'emails' => 'gerencia@outtajiapuleeipsi.com;riasouttajiapulee@gmail.com;rutasmaternocronicos2023@gmail.com'],
            ['ips' => 'IPSI SUPULA WAYUU', 'emails' => 'supulawayuuipsi@gmail.com;gerencia@supulawayuuipsi.com;saludpublicasupulawayuu@gmail.com;ipsisupulawayuu@hotmail.com'],
            ['ips' => 'ASOCABILDOS URIBIA', 'emails' => 'serviciosambulatoriosuribia@asocabildosipsi.com;asistencial@asocabildosipsi.com;gerencia@asocabildosipsi.com'],
            ['ips' => 'H. PERPETUO SOCORRO', 'emails' => 'gerencia@esehnsps.gov.co;programacionriahnsps@gmail.com;rutamaternohnsps@gmail.com'],
            ['ips' => 'ESE NAZARETH', 'emails' => 'coormedic.nazareth@gmail.com;pyphnazareth@gmail.com;hospitalnazareth@hotmail.com'],
            ['ips' => 'IPSI KARAQUITA', 'emails' => 'ipsikaraquita@hotmail.com;calidadipsikaraquita@gmail.com;enfermeriaipsikaraquita@gmail.com'],
            ['ips' => 'AYUULEEPALA IPSI', 'emails' => 'gerencia@ipsiayuuleepala.org;ipsiayuuleepala_pyp@hotmail.com;ipsiayuuleepala_dirmedica@hotmail.com'],
            ['ips' => 'IPSI ERREJERIA WAYUU', 'emails' => 'coordpypereejeria@gmail.com;coordinacionPyM@ipsierejeeriawayuu.org.co'],
            ['ips' => 'SUMUYWJAT IPSI', 'emails' => 'casistencialsumuywajat@hotmail.com;lpsindigenasumuywajat@hotmail.com'],
            ['ips' => 'ASOCABILDOS MAICAO', 'emails' => 'saludpublica@asocabildosipsi.com;detecciontemprana@asocabildosipsi.com;coordmedica@asocabildosipsi.com'],
            ['ips' => 'ASOCABILDOS ALBANIA', 'emails' => 'serviciosambulatoriosalbania@asocabildosipsi.com'],
            ['ips' => 'HOSPITAL SANTA RITA DE CASSIA', 'emails' => 'pypsantarita@hotmail.com;esehospitalsantarita@hotmail.com;pym@hospitalsantaritadecassia.co'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function normalizeEmails(string $emails): array
    {
        $parts = preg_split('/[;,\s]+/', trim($emails)) ?: [];

        $valid = [];
        foreach ($parts as $email) {
            $email = mb_strtolower(trim($email));
            if ($email === '') {
                continue;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $valid[] = $email;
        }

        return array_values(array_unique($valid));
    }

    private function ensureGesSuffix(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            $name = 'gestante';
        }

        if (Str::endsWith(Str::lower($name), '_ges')) {
            return $name;
        }

        return $name . '_ges';
    }
}

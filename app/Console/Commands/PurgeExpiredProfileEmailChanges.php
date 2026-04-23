<?php

namespace App\Console\Commands;

use App\Models\ProfileEmailChange;
use Illuminate\Console\Command;

class PurgeExpiredProfileEmailChanges extends Command
{
    protected $signature = 'profile:purge-email-changes';

    protected $description = 'Elimina solicitudes de cambio de correo vencidas y no confirmadas.';

    public function handle(): int
    {
        $expiredIds = ProfileEmailChange::query()
            ->whereNull('confirmed_at')
            ->whereNotNull('expires_at')
            ->get(['id', 'expires_at'])
            ->filter(fn (ProfileEmailChange $item) => now()->greaterThan($item->expires_at))
            ->pluck('id')
            ->all();

        if (empty($expiredIds)) {
            $this->info('No hay solicitudes vencidas para eliminar.');
            return self::SUCCESS;
        }

        $deleted = ProfileEmailChange::query()
            ->whereIn('id', $expiredIds)
            ->delete();

        $this->info('Solicitudes vencidas eliminadas: ' . $deleted);

        return self::SUCCESS;
    }
}


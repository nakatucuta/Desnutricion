# Redis Migration Runbook (Production Safe)

This project includes a guarded Redis migration script with automatic rollback.

## Commands

- Status:
`powershell -ExecutionPolicy Bypass -File scripts\redis_migration_safe.ps1 -Action status`

- Preflight checks only:
`powershell -ExecutionPolicy Bypass -File scripts\redis_migration_safe.ps1 -Action preflight`

- Enable Redis for cache + session:
`powershell -ExecutionPolicy Bypass -File scripts\redis_migration_safe.ps1 -Action enable`

- One-command rollback:
`powershell -ExecutionPolicy Bypass -File scripts\redis_migration_safe.ps1 -Action rollback`

Also available as shortcuts:

- `scripts\redis_enable_safe.bat`
- `scripts\redis_rollback_safe.bat`

## What the script does

1. Reads `.env` and current Redis settings.
2. Validates Redis TCP reachability (`REDIS_HOST:REDIS_PORT`).
3. Validates Redis client availability (`ext-redis` or `predis/predis`).
4. Creates `.env` backup under `storage/backups/env/.env.pre-redis.<timestamp>`.
5. Switches:
   - `CACHE_DRIVER=redis`
   - `SESSION_DRIVER=redis`
   - `SESSION_CONNECTION=default`
   - `SESSION_STORE=redis`
   - `REDIS_CLIENT=phpredis|predis`
6. Runs post-switch tasks:
   - `php artisan optimize:clear`
   - `php artisan config:cache`
   - `php artisan queue:restart`
7. Runs cache and HTTP probes.
8. If any step fails, restores backup and re-runs post-switch tasks automatically.

## Important notes

- The script does not modify queue driver. `QUEUE_CONNECTION` remains unchanged.
- If Redis is unreachable or no client is installed, enable is aborted with no changes.
- Rollback uses `storage/app/redis-migration-state.json` backup path first, then latest backup file.

## Mini Monitoring (Redis Health + Alert)

Laravel command added:

- `php artisan redis:health-check`

Behavior:

1. Validates `cache.default=redis` and `session.driver=redis`.
2. Executes Redis `PING`.
3. Executes cache write/read probe.
4. Writes health traces in `storage/logs/redis-health.log`.
5. Sends email alert when it detects failure.
6. Sends recovery email when Redis returns healthy.
7. If still failing, re-sends reminder each `REDIS_HEALTH_ALERT_REMINDER_MINUTES` minutes.

Schedule:

- Added to scheduler every 5 minutes in `app/Console/Kernel.php`.

Optional `.env` keys:

- `REDIS_HEALTH_ALERT_TO=ops@dominio.com,soporte@dominio.com`
- `REDIS_HEALTH_ALERT_CC=devops@dominio.com`
- `REDIS_HEALTH_ALERT_REMINDER_MINUTES=30`

Safe test commands:

- Dry run:
  `php artisan redis:health-check --dry-run`
- Forced test email:
  `php artisan redis:health-check --force-email`

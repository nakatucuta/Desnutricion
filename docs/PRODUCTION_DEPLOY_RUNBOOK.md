# Production Deploy Runbook (Safe)

This script helps apply changes from test/staging to production with the cleanup and optimization commands you requested.

## Files

- `scripts/deploy_production_safe.ps1`
- `scripts/deploy_production_safe.bat`

## Default behavior

When you run the script, it:

1. Optionally pauses scheduler (`schedule:pause`) when available.
2. Optionally pulls from git branch (`--GitPull --Branch ...`).
3. Runs Composer install optimized for production.
4. Runs cleanup commands:
   - `php artisan optimize:clear`
   - `php artisan config:clear`
   - `php artisan cache:clear`
   - `php artisan route:clear`
   - `php artisan view:clear`
   - `php artisan event:clear`
5. Runs migrations with `--force` (unless skipped).
6. Optionally builds frontend assets (`npm ci && npm run prod`).
7. Rebuilds caches:
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
   - `php artisan event:cache`
8. Restarts queue workers (`php artisan queue:restart`).
9. Interrupts in-progress scheduler minute (`php artisan schedule:interrupt`) when available.
10. Runs Redis health check in dry-run (`php artisan redis:health-check --dry-run`) when available.
11. Optionally restarts Apache (`Restart-Service Apache2.4 -Force`).
12. Resumes scheduler (`schedule:continue`) if it was paused.
13. Leaves maintenance mode (`artisan up`) if enabled.
14. Final step: closes active `php artisan queue:work` processes and relaunches workers using:
   - `start_queue_workers_produccion.bat`

## Recommended usage

### A) Normal deploy (no git pull, no Apache restart)

```powershell
powershell -ExecutionPolicy Bypass -File scripts\deploy_production_safe.ps1
```

### B) Full deploy with git pull + frontend + Apache restart

```powershell
powershell -ExecutionPolicy Bypass -File scripts\deploy_production_safe.ps1 -GitPull -Branch main -BuildFrontend -RestartApache
```

### C) Deploy with maintenance mode (short downtime)

```powershell
powershell -ExecutionPolicy Bypass -File scripts\deploy_production_safe.ps1 -MaintenanceMode -RestartApache
```

### D) Dry run (no changes, only prints steps)

```powershell
powershell -ExecutionPolicy Bypass -File scripts\deploy_production_safe.ps1 -DryRun
```

## Auto-run on `git pull`

If `core.hooksPath` points to `.githooks`, the `post-merge` / `post-rewrite` hooks execute deploy automatically.

Smart behavior:

- If `composer.json` and `composer.lock` did not change, it adds `-SkipComposer`.
- If `database/migrations/*` did not change, it adds `-SkipMigrate`.

## Useful flags

- `-GitPull` / `-Branch main`
- `-MaintenanceMode`
- `-BuildFrontend`
- `-RestartApache`
- `-SkipComposer`
- `-SkipMigrate`
- `-SkipRedisHealthCheck`
- `-DryRun`

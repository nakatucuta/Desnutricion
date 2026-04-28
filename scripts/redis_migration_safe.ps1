param(
    [ValidateSet("status", "preflight", "enable", "rollback")]
    [string]$Action = "status"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Write-Step {
    param([string]$Message)
    Write-Host "[redis-migration] $Message"
}

function Get-ProjectRoot {
    $scriptDir = Split-Path -Parent $PSCommandPath
    return (Resolve-Path (Join-Path $scriptDir "..")).Path
}

function Get-EnvMap {
    param([string]$EnvPath)

    $map = @{}
    if (-not (Test-Path -LiteralPath $EnvPath)) {
        return $map
    }

    foreach ($line in Get-Content -LiteralPath $EnvPath) {
        if ($line -match '^\s*([A-Za-z_][A-Za-z0-9_]*)=(.*)$') {
            $key = $Matches[1]
            $value = $Matches[2]
            $map[$key] = $value
        }
    }
    return $map
}

function Set-EnvKeys {
    param(
        [string]$EnvPath,
        [hashtable]$Changes
    )

    $lines = @()
    if (Test-Path -LiteralPath $EnvPath) {
        $lines = [System.Collections.ArrayList]@(Get-Content -LiteralPath $EnvPath)
    }

    foreach ($key in $Changes.Keys) {
        $newValue = "$key=$($Changes[$key])"
        $firstIndex = $null

        for ($i = 0; $i -lt $lines.Count; $i++) {
            if ($lines[$i] -match "^\s*${key}=") {
                if ($null -eq $firstIndex) {
                    $firstIndex = $i
                    $lines[$i] = $newValue
                } else {
                    $lines[$i] = $null
                }
            }
        }

        if ($null -eq $firstIndex) {
            [void]$lines.Add($newValue)
        }
    }

    $filtered = @()
    foreach ($item in $lines) {
        if ($null -ne $item) {
            $filtered += [string]$item
        }
    }

    [System.IO.File]::WriteAllLines($EnvPath, $filtered, [System.Text.UTF8Encoding]::new($false))
}

function Run-Php {
    param(
        [string[]]$Args,
        [string]$WorkDir
    )
    Push-Location $WorkDir
    try {
        & php @Args
        if ($LASTEXITCODE -ne 0) {
            throw "php $($Args -join ' ') failed with exit code $LASTEXITCODE"
        }
    } finally {
        Pop-Location
    }
}

function Get-PhpModules {
    param([string]$WorkDir)
    Push-Location $WorkDir
    try {
        $modules = & php -m
        if ($LASTEXITCODE -ne 0) {
            throw "Unable to read PHP modules."
        }
        return $modules
    } finally {
        Pop-Location
    }
}

function Test-RedisConnectivity {
    param(
        [string]$RedisHost,
        [int]$Port
    )
    $result = Test-NetConnection -ComputerName $RedisHost -Port $Port -WarningAction SilentlyContinue
    return [bool]$result.TcpTestSucceeded
}

function Ensure-Backup {
    param(
        [string]$EnvPath,
        [string]$BackupDir
    )

    if (-not (Test-Path -LiteralPath $BackupDir)) {
        New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
    }

    $ts = Get-Date -Format "yyyyMMddHHmmss"
    $backupPath = Join-Path $BackupDir ".env.pre-redis.$ts"
    Copy-Item -LiteralPath $EnvPath -Destination $backupPath -Force
    return $backupPath
}

function Save-State {
    param(
        [string]$StatePath,
        [string]$BackupPath
    )

    $stateDir = Split-Path -Parent $StatePath
    if (-not (Test-Path -LiteralPath $stateDir)) {
        New-Item -ItemType Directory -Path $stateDir -Force | Out-Null
    }

    $payload = @{
        migrated_at = (Get-Date).ToString("s")
        backup_path = $BackupPath
    } | ConvertTo-Json -Depth 4

    [System.IO.File]::WriteAllText($StatePath, $payload, [System.Text.UTF8Encoding]::new($false))
}

function Get-RollbackBackup {
    param(
        [string]$StatePath,
        [string]$BackupDir
    )

    if (Test-Path -LiteralPath $StatePath) {
        $state = Get-Content -LiteralPath $StatePath -Raw | ConvertFrom-Json
        if ($state.backup_path -and (Test-Path -LiteralPath $state.backup_path)) {
            return $state.backup_path
        }
    }

    if (Test-Path -LiteralPath $BackupDir) {
        $latest = Get-ChildItem -LiteralPath $BackupDir -Filter ".env.pre-redis.*" | Sort-Object LastWriteTime -Descending | Select-Object -First 1
        if ($latest) {
            return $latest.FullName
        }
    }

    return $null
}

function Run-PostSwitchTasks {
    param([string]$ProjectRoot)

    Run-Php -Args @("artisan", "optimize:clear") -WorkDir $ProjectRoot
    Run-Php -Args @("artisan", "config:cache") -WorkDir $ProjectRoot
    Run-Php -Args @("artisan", "queue:restart") -WorkDir $ProjectRoot
}

function Run-CacheProbe {
    param([string]$ProjectRoot)

    $probeCode = @"
require 'vendor/autoload.php';
`$app=require 'bootstrap/app.php';
`$kernel=`$app->make(Illuminate\Contracts\Console\Kernel::class);
`$kernel->bootstrap();
Illuminate\Support\Facades\Cache::put('redis_migration_probe', 'ok', 60);
echo Illuminate\Support\Facades\Cache::get('redis_migration_probe');
"@
    Run-Php -Args @("-r", $probeCode) -WorkDir $ProjectRoot
}

function Run-HttpProbe {
    try {
        $resp = Invoke-WebRequest -Uri "https://app.epsianaswayuu.com/rutasintegrales/login" -Method GET -MaximumRedirection 0 -ErrorAction Stop
        if ([int]$resp.StatusCode -lt 200 -or [int]$resp.StatusCode -ge 400) {
            throw "Unexpected status code: $($resp.StatusCode)"
        }
    } catch {
        $response = $null
        if ($_.Exception -and $_.Exception.PSObject.Properties.Name -contains "Response") {
            $response = $_.Exception.Response
        } elseif ($_.Exception -and $_.Exception.InnerException -and $_.Exception.InnerException.PSObject.Properties.Name -contains "Response") {
            $response = $_.Exception.InnerException.Response
        }

        if ($response) {
            $code = $null
            try { $code = [int]$response.StatusCode } catch {}

            if ($null -ne $code) {
                if ($code -ge 400) {
                    throw "HTTP probe failed with status code $code"
                }
                return
            }
        }

        throw "HTTP probe failed: $($_.Exception.Message)"
    }
}

$projectRoot = Get-ProjectRoot
$envPath = Join-Path $projectRoot ".env"
$backupDir = Join-Path $projectRoot "storage\backups\env"
$statePath = Join-Path $projectRoot "storage\app\redis-migration-state.json"

if (-not (Test-Path -LiteralPath $envPath)) {
    throw ".env not found at $envPath"
}

$envMap = Get-EnvMap -EnvPath $envPath
$redisHost = if ($envMap.ContainsKey("REDIS_HOST")) { $envMap["REDIS_HOST"] } else { "127.0.0.1" }
$redisPortRaw = if ($envMap.ContainsKey("REDIS_PORT")) { $envMap["REDIS_PORT"] } else { "6379" }
$redisPort = 6379
[void][int]::TryParse($redisPortRaw, [ref]$redisPort)

$phpModules = Get-PhpModules -WorkDir $projectRoot
$hasPhpRedis = $phpModules -match '^redis$'
$hasPredis = Test-Path -LiteralPath (Join-Path $projectRoot "vendor\predis\predis\src\Client.php")
$redisReachable = Test-RedisConnectivity -RedisHost $redisHost -Port $redisPort

switch ($Action) {
    "status" {
        Write-Step "Status"
        Write-Host "CACHE_DRIVER=$($envMap['CACHE_DRIVER'])"
        Write-Host "SESSION_DRIVER=$($envMap['SESSION_DRIVER'])"
        Write-Host "QUEUE_CONNECTION=$($envMap['QUEUE_CONNECTION'])"
        Write-Host "REDIS_CLIENT=$($envMap['REDIS_CLIENT'])"
        Write-Host "REDIS_HOST=$redisHost"
        Write-Host "REDIS_PORT=$redisPort"
        Write-Host "phpredis_loaded=$([bool]$hasPhpRedis)"
        Write-Host "predis_present=$([bool]$hasPredis)"
        Write-Host "redis_reachable=$([bool]$redisReachable)"
        Write-Host "rollback_command=powershell -ExecutionPolicy Bypass -File scripts\redis_migration_safe.ps1 -Action rollback"
        break
    }
    "preflight" {
        Write-Step "Preflight"
        if (-not $redisReachable) {
            throw "Redis is not reachable at ${redisHost}:$redisPort"
        }
        if (-not $hasPhpRedis -and -not $hasPredis) {
            throw "No Redis client available. Install ext-redis or predis/predis."
        }
        Write-Step "Preflight OK"
        break
    }
    "enable" {
        Write-Step "Enable requested"

        if (-not $redisReachable) {
            throw "Redis is not reachable at ${redisHost}:$redisPort. Aborting with no changes."
        }
        if (-not $hasPhpRedis -and -not $hasPredis) {
            throw "No Redis client available. Install ext-redis or predis/predis before enabling."
        }

        $redisClient = if ($hasPhpRedis) { "phpredis" } else { "predis" }
        $backupPath = Ensure-Backup -EnvPath $envPath -BackupDir $backupDir
        Write-Step "Backup created: $backupPath"

        $changes = @{
            "CACHE_DRIVER" = "redis"
            "SESSION_DRIVER" = "redis"
            "SESSION_CONNECTION" = "default"
            "SESSION_STORE" = "redis"
            "REDIS_CLIENT" = $redisClient
        }

        try {
            Set-EnvKeys -EnvPath $envPath -Changes $changes
            Run-PostSwitchTasks -ProjectRoot $projectRoot
            Run-CacheProbe -ProjectRoot $projectRoot
            Run-HttpProbe
            Save-State -StatePath $statePath -BackupPath $backupPath
            Write-Step "Redis enabled safely."
            Write-Host "rollback_command=powershell -ExecutionPolicy Bypass -File scripts\redis_migration_safe.ps1 -Action rollback"
        } catch {
            Write-Step "Enable failed. Rolling back automatically..."
            Copy-Item -LiteralPath $backupPath -Destination $envPath -Force
            Run-PostSwitchTasks -ProjectRoot $projectRoot
            throw "Rollback completed after failure: $($_.Exception.Message)"
        }
        break
    }
    "rollback" {
        Write-Step "Rollback requested"
        $backupPath = Get-RollbackBackup -StatePath $statePath -BackupDir $backupDir
        if (-not $backupPath) {
            throw "No rollback backup found."
        }

        Copy-Item -LiteralPath $backupPath -Destination $envPath -Force
        Run-PostSwitchTasks -ProjectRoot $projectRoot
        Run-HttpProbe
        Write-Step "Rollback completed using backup: $backupPath"
        break
    }
}

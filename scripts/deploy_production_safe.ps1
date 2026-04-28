param(
    [switch]$GitPull,
    [string]$Branch = "main",
    [switch]$MaintenanceMode,
    [switch]$BuildFrontend,
    [switch]$RestartApache,
    [switch]$SkipComposer,
    [switch]$SkipMigrate,
    [switch]$SkipRedisHealthCheck,
    [switch]$DryRun
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Write-Step {
    param([string]$Message)
    $ts = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Host "[deploy][$ts] $Message"
}

function Invoke-CommandChecked {
    param(
        [string]$Exe,
        [Alias("Args")]
        [string[]]$CommandArgs
    )

    $display = "$Exe $($CommandArgs -join ' ')".Trim()
    if ($DryRun) {
        Write-Step "DRY-RUN: $display"
        return
    }

    Write-Step "RUN: $display"
    & $Exe @CommandArgs
    if ($LASTEXITCODE -ne 0) {
        throw "Command failed (exit $LASTEXITCODE): $display"
    }
}

function Test-ArtisanCommand {
    param([string]$CommandName)
    try {
        $list = (& php artisan list --raw) -join "`n"
        return $list -match ("(?m)^" + [regex]::Escape($CommandName) + "(\s|$)")
    } catch {
        return $false
    }
}

function Stop-QueueWorkers {
    if ($DryRun) {
        Write-Step "DRY-RUN: stop php artisan queue:work processes"
        return
    }

    $workerProcesses = Get-CimInstance Win32_Process | Where-Object {
        $_.Name -ieq "php.exe" -and
        $_.CommandLine -and
        $_.CommandLine -match "artisan(\.php)?\s+queue:work"
    }

    if (-not $workerProcesses -or $workerProcesses.Count -eq 0) {
        Write-Step "No queue:work workers detected."
        return
    }

    foreach ($proc in $workerProcesses) {
        Write-Step "Stopping worker PID=$($proc.ProcessId)"
        if (-not $DryRun) {
            Stop-Process -Id $proc.ProcessId -Force -ErrorAction SilentlyContinue
        }
    }

    if (-not $DryRun) {
        Start-Sleep -Seconds 2
    }
}

$scriptDir = Split-Path -Parent $PSCommandPath
$projectRoot = (Resolve-Path (Join-Path $scriptDir "..")).Path

$maintenanceEnabled = $false
$schedulerPaused = $false

Push-Location $projectRoot
try {
    Write-Step "Project root: $projectRoot"

    if ($MaintenanceMode) {
        Invoke-CommandChecked -Exe "php" -Args @("artisan", "down", "--refresh=15", "--retry=60")
        $maintenanceEnabled = $true
    }

    if (Test-ArtisanCommand "schedule:pause") {
        Invoke-CommandChecked -Exe "php" -Args @("artisan", "schedule:pause")
        $schedulerPaused = $true
    }

    if ($GitPull) {
        Invoke-CommandChecked -Exe "git" -Args @("fetch", "--all", "--prune")
        Invoke-CommandChecked -Exe "git" -Args @("checkout", $Branch)
        Invoke-CommandChecked -Exe "git" -Args @("pull", "origin", $Branch)
    }

    if (-not $SkipComposer) {
        Invoke-CommandChecked -Exe "composer" -Args @("install", "--no-interaction", "--no-dev", "--prefer-dist", "--optimize-autoloader")
    }

    # Clear everything explicitly (your requested sequence) plus optimize:clear.
    Invoke-CommandChecked -Exe "php" -Args @("artisan", "optimize:clear")
    Invoke-CommandChecked -Exe "php" -Args @("artisan", "config:clear")
    Invoke-CommandChecked -Exe "php" -Args @("artisan", "cache:clear")
    Invoke-CommandChecked -Exe "php" -Args @("artisan", "route:clear")
    Invoke-CommandChecked -Exe "php" -Args @("artisan", "view:clear")
    Invoke-CommandChecked -Exe "php" -Args @("artisan", "event:clear")

    if (-not $SkipMigrate) {
        Invoke-CommandChecked -Exe "php" -Args @("artisan", "migrate", "--force")
    }

    if ($BuildFrontend) {
        if (Test-Path -LiteralPath "package-lock.json") {
            Invoke-CommandChecked -Exe "npm" -Args @("ci")
        }
        Invoke-CommandChecked -Exe "npm" -Args @("run", "prod")
    }

    # Rebuild caches for production performance.
    Invoke-CommandChecked -Exe "php" -Args @("artisan", "config:cache")
    Invoke-CommandChecked -Exe "php" -Args @("artisan", "route:cache")
    Invoke-CommandChecked -Exe "php" -Args @("artisan", "view:cache")
    Invoke-CommandChecked -Exe "php" -Args @("artisan", "event:cache")

    Invoke-CommandChecked -Exe "php" -Args @("artisan", "queue:restart")

    if (Test-ArtisanCommand "schedule:interrupt") {
        Invoke-CommandChecked -Exe "php" -Args @("artisan", "schedule:interrupt")
    }

    if (-not $SkipRedisHealthCheck -and (Test-ArtisanCommand "redis:health-check")) {
        Invoke-CommandChecked -Exe "php" -Args @("artisan", "redis:health-check", "--dry-run")
    }

    if ($RestartApache) {
        if ($DryRun) {
            Write-Step "DRY-RUN: Restart-Service Apache2.4 -Force"
        } else {
            Write-Step "RUN: Restart-Service Apache2.4 -Force"
            Restart-Service -Name "Apache2.4" -Force -ErrorAction Stop
            Start-Sleep -Seconds 2
            $svc = Get-Service -Name "Apache2.4" -ErrorAction Stop
            Write-Step "Apache status: $($svc.Status)"
        }
    }
}
catch {
    Write-Step "ERROR: $($_.Exception.Message)"
    throw
}
finally {
    if ($schedulerPaused) {
        try {
            Invoke-CommandChecked -Exe "php" -Args @("artisan", "schedule:continue")
        } catch {
            Write-Step "WARN: could not resume scheduler: $($_.Exception.Message)"
        }
    }

    if ($maintenanceEnabled) {
        try {
            Invoke-CommandChecked -Exe "php" -Args @("artisan", "up")
        } catch {
            Write-Step "WARN: could not disable maintenance mode: $($_.Exception.Message)"
        }
    }

    try {
        $workerStarterBat = Join-Path $projectRoot "start_queue_workers_produccion.bat"
        if (Test-Path -LiteralPath $workerStarterBat) {
            Write-Step "Restarting queue workers with start_queue_workers_produccion.bat (final step)..."
            Stop-QueueWorkers
            Invoke-CommandChecked -Exe "cmd.exe" -Args @("/c", $workerStarterBat)
        } else {
            Write-Step "WARN: start_queue_workers_produccion.bat not found, skipping worker relaunch."
        }
    } catch {
        Write-Step "WARN: worker restart step failed: $($_.Exception.Message)"
    }

    Write-Step "Deployment workflow finished."
    Pop-Location
}

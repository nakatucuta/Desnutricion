@echo off
setlocal
cd /d "%~dp0\.."
powershell -ExecutionPolicy Bypass -File "scripts\redis_migration_safe.ps1" -Action enable
endlocal


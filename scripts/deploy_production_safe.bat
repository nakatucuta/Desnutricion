@echo off
powershell -ExecutionPolicy Bypass -File scripts\deploy_production_safe.ps1 %*

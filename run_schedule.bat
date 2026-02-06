@echo off
setlocal

set "PROJECT=C:\Apache24\htdocs\Desnutricion"
set "LOG=%PROJECT%\storage\logs\alertas.log"
set "PHP=C:\php\php.exe"
set "LOCK=%PROJECT%\storage\logs\seguimientos_enviar_alertas.lock"

if exist "%LOCK%" (
  echo === %date% %time% - SKIP (lock exists) ===>> "%LOG%"
  exit /b 0
)

echo LOCK>%LOCK%
echo === %date% %time% - seguimientos:enviar-alertas ===>> "%LOG%"

cd /d "%PROJECT%"
"%PHP%" artisan seguimientos:enviar-alertas >> "%LOG%" 2>&1

del "%LOCK%" >nul 2>&1
endlocal

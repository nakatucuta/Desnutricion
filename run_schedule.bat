@echo off
setlocal

set "PROJECT=C:\Apache24\htdocs\Desnutricion"
set "LOG=%PROJECT%\storage\logs\alertas.log"
set "PHP=C:\php\php.exe"

echo === %date% %time% - seguimientos:enviar-alertas ===>> "%LOG%"
cd /d "%PROJECT%"
"%PHP%" artisan seguimientos:enviar-alertas >> "%LOG%" 2>&1

endlocal

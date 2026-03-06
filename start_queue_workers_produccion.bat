@echo off
setlocal

set "APP_DIR=C:\Apache24\htdocs\Desnutricion"
set "WORKER_LOG_DIR=%APP_DIR%\storage\logs\workers"

echo Iniciando workers de PRODUCCION...
echo Proyecto: %APP_DIR%

cd /d "%APP_DIR%"
if not exist "%WORKER_LOG_DIR%" mkdir "%WORKER_LOG_DIR%"
php artisan optimize:clear
php artisan config:cache

start "QUEUE imports_pai #1" cmd /k "cd /d %APP_DIR% && echo [%%date%% %%time%%] Iniciando imports_pai #1>>%WORKER_LOG_DIR%\imports_pai_1.log && php artisan queue:work database --queue=imports_pai --sleep=1 --tries=1 --timeout=3600 --verbose >> %WORKER_LOG_DIR%\imports_pai_1.log 2>&1"
start "QUEUE imports_pai #2" cmd /k "cd /d %APP_DIR% && echo [%%date%% %%time%%] Iniciando imports_pai #2>>%WORKER_LOG_DIR%\imports_pai_2.log && php artisan queue:work database --queue=imports_pai --sleep=1 --tries=1 --timeout=3600 --verbose >> %WORKER_LOG_DIR%\imports_pai_2.log 2>&1"
start "QUEUE gestantes_t1" cmd /k "cd /d %APP_DIR% && echo [%%date%% %%time%%] Iniciando imports_gestantes_t1>>%WORKER_LOG_DIR%\imports_gestantes_t1.log && php artisan queue:work database --queue=imports_gestantes_t1 --sleep=1 --tries=1 --timeout=3600 --verbose >> %WORKER_LOG_DIR%\imports_gestantes_t1.log 2>&1"
start "QUEUE gestantes_t3" cmd /k "cd /d %APP_DIR% && echo [%%date%% %%time%%] Iniciando imports_gestantes_t3>>%WORKER_LOG_DIR%\imports_gestantes_t3.log && php artisan queue:work database --queue=imports_gestantes_t3 --sleep=1 --tries=1 --timeout=3600 --verbose >> %WORKER_LOG_DIR%\imports_gestantes_t3.log 2>&1"
start "QUEUE preconcepcional" cmd /k "cd /d %APP_DIR% && echo [%%date%% %%time%%] Iniciando imports_preconcepcional>>%WORKER_LOG_DIR%\imports_preconcepcional.log && php artisan queue:work database --queue=imports_preconcepcional --sleep=1 --tries=1 --timeout=3600 --verbose >> %WORKER_LOG_DIR%\imports_preconcepcional.log 2>&1"
start "QUEUE default" cmd /k "cd /d %APP_DIR% && echo [%%date%% %%time%%] Iniciando default>>%WORKER_LOG_DIR%\default.log && php artisan queue:work database --queue=default --sleep=1 --tries=3 --timeout=120 --verbose >> %WORKER_LOG_DIR%\default.log 2>&1"

echo.
echo Listo. Se abrieron 6 ventanas de workers.
echo Logs dedicados: %WORKER_LOG_DIR%
echo Cierra cada ventana para detener su worker.

endlocal

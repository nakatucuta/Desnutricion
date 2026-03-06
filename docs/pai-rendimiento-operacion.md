# PAI - Operacion de rendimiento (pruebas/produccion)

## Variables de entorno

Agregar en `.env`:

```env
PAI_LOAD_STATE_CACHE_SECONDS=8
PAI_BUSY_RUNNING_THRESHOLD=2
PAI_BUSY_QUEUE_THRESHOLD=3
PAI_BUSY_LONG_RUNNING_MINUTES=8
PAI_LOAD_SUMMARY_CACHE_SECONDS=45
```

Luego ejecutar:

```bash
php artisan config:clear
php artisan optimize:clear
```

## Workers recomendados (aislamiento real)

- `imports_pai`: 1 proceso
- `imports_gestantes_t1`: 1 proceso
- `imports_gestantes_t3`: 1 proceso
- `imports_preconcepcional`: 1 proceso
- `default`: 1 o 2 procesos

Ya existe script:

- `start_queue_workers.bat` (pruebas)
- `start_queue_workers_produccion.bat` (produccion)

## Cambios funcionales aplicados

- Estado de carga PAI por endpoint: `GET /afiliado/load-state`.
- Modo proteccion en vista PAI:
  - Si hay alta carga, muestra aviso y difiere DataTable.
  - Reintenta automaticamente cada 12 segundos.
- Guard rapido en backend para evitar saturacion:
  - `dataTable()` y `getVacunas()` responden `503` con mensaje controlado cuando el modulo esta ocupado.
- Cache corto de informe de cargue (`loadSummary`) para reducir consultas repetitivas.

## Verificacion rapida

1. Entrar a `/afiliado` durante import pesado.
2. Confirmar que aparece alerta de alta demanda y no se queda cargando indefinidamente.
3. Al terminar la carga, confirmar que la tabla vuelve a cargar sola.
4. Probar reporte de cargue dos veces seguidas y validar respuesta mas rapida en la segunda.

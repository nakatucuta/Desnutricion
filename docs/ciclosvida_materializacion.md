# Materializacion de ciclos de vida

## Problema actual

Los reportes de `ciclos de vida` para Primera Infancia hoy consultan SQL Server en vivo y repiten:

- joins pesados entre `ripsAC`, `ripsnAC`, `ripsAP`, `ripsnAP`
- normalizacion de fecha de nacimiento
- calculo de edad y rango de edad
- filtros por CUPS, diagnosticos y finalidad
- agrupaciones para KPIs y alertas

Eso hace que la UI dependa de consultas costosas justo en el momento de la navegacion.

## Nueva estrategia

La estrategia propuesta es separar el modulo en dos capas:

1. `Extraccion`
   Lee los SQL base con los que se construyo el modulo y trae los resultados desde `sqlsrv_1`.

2. `Materializacion`
   Guarda los resultados normalizados en tablas locales:
   - `ciclo_vida_cache_records`
   - `ciclo_vida_cache_summaries`
   - `ciclo_vida_cache_runs`

Con esto, las pantallas dejan de leer RIPS en vivo y pasan a leer tablas locales indexadas.

## Modulos ya configurados

La configuracion vive en `config/ciclosvida.php` y toma los scripts fuente desde:

- `MEDICINA2.sql`
- `ENFERMERIA2.sql`
- `ODONTOLOGIA2.sql`
- `FLUOR2.sql`
- `PLACA2.sql`
- `SELLANTE2.sql`
- `HEMOGLOBINA2.sql`
- `LACTANCIA MATERNA.sql`
- `VITAMINA A.sql`
- `HIERRO.sql`
- `NO_TIENEN ATENCION 3.sql`
- `TODOS.sql`

## Comando de refresco

```bash
php artisan ciclosvida:cache-refresh primera_infancia
php artisan ciclosvida:cache-refresh primera_infancia --module=medica --module=enfermeria
php artisan ciclosvida:cache-refresh primera_infancia --from=2025-01-01 --to=2025-12-31
```

## Migracion funcional recomendada

Orden recomendado para mover las pantallas actuales:

1. `medica`
2. `enfermeria`
3. `odontologia_general`
4. `fluor`
5. `placa`
6. `sellantes`
7. `hemoglobina`
8. `lactancia`
9. `vitamina_a`
10. `hierro`
11. `alertas`
12. `datos generales` leyendo `ciclo_vida_cache_summaries`

## Reglas importantes

- Los SQL fuente siguen siendo la verdad funcional del negocio.
- La UI no deberia ejecutar esos SQL directamente.
- El cache debe refrescarse por ventana de fechas.
- Los KPIs deben salir del cache local, no del origen transaccional.
- `lactancia`, `vitamina_a` y `hierro` ya deben refrescarse como modulos materializados independientes.

## Siguiente paso recomendado

Cambiar cada endpoint `data` de `CicloVidaController` para leer desde `ciclo_vida_cache_records` y cada KPI/resumen para leer desde `ciclo_vida_cache_summaries`.

# Ciclos de Vida en SQL Server

## Objetivo
Mover el cargue historico de ciclos de vida al motor SQL Server para evitar pasar grandes volumenes por PHP.

## Archivo base
- [ciclosvida_sqlserver_pipeline.sql](/C:/xampp/htdocs/Desnutricion/database/sql/ciclosvida_sqlserver_pipeline.sql)

## Que crea
- `dbo.cv_sql_module_config`
- `dbo.fn_cv_rango_edad`
- `dbo.sp_cv_refresh_module_window`
- `dbo.sp_cv_backfill_course`

## Como funciona
1. Cada modulo debe exponerse en SQL Server como una fuente estandarizada.
2. Esa fuente puede ser:
   - una `VIEW`
   - o un `PROC`
3. La fuente devuelve columnas normalizadas.
4. `sp_cv_refresh_module_window`:
   - registra la corrida
   - borra la ventana actual
   - inserta en `ciclo_vida_cache_records`
   - genera `ciclo_vida_cache_summaries`
   - actualiza `ciclo_vida_cache_runs`
5. `sp_cv_backfill_course` recorre ventanas de tiempo y llama al refresh por modulo.

## Recomendacion para historico
- Historico grande: `quarter`
- Historico muy pesado: `month`
- Operacion diaria: seguir con Laravel o luego mover tambien a SQL Server

## Orden recomendado
1. Crear el framework.
2. Empezar por `primera_infancia`:
   - `medica`
   - `enfermeria`
   - `odontologia_general`
3. Validar que escribe bien en las tablas cache.
4. Luego migrar el resto de modulos y cursos.

## Prueba minima
```sql
EXEC dbo.sp_cv_refresh_module_window
    @course_key = 'primera_infancia',
    @module_key = 'medica',
    @from_date = '2025-01-01',
    @to_date = '2025-02-01';
```

## Backfill ejemplo
```sql
EXEC dbo.sp_cv_backfill_course
    @course_key = 'primera_infancia',
    @from_date = '2020-01-01',
    @to_date = '2026-03-27',
    @chunk = 'quarter',
    @step = 1,
    @resume = 1;
```

## Punto importante
Este framework ya es util, pero no reemplaza automaticamente tus `.sql` actuales.
Todavia hace falta convertir cada modulo a:
- `VIEW` estandarizada
- o `PROC` estandarizado

Ese es el siguiente paso para dejar el proceso 100% del lado de SQL Server.

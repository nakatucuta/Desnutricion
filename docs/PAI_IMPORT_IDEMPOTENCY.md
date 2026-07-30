# Idempotencia de importacion PAI

## Huellas activas

La reclamacion usa dos huellas SHA-256 junto con `format_version`:

- `file_sha256`: bytes completos del archivo. Evita repetir el mismo archivo fisico aunque cambie el nombre.
- `content_sha256`: contenido semantico de la hoja activa. Evita repetir un Excel que fue abierto, guardado o descargado de nuevo con las mismas celdas, aunque cambien metadatos internos del `.xlsx`.

El nombre original no participa en la identidad, pero se conserva para trazabilidad.

La exclusion concurrente combina:

1. `sp_getapplock` transaccional por huella semantica cuando existe; si no se puede calcular, usa la huella binaria.
2. Indice unico sobre `file_sha256 + format_version`.
3. Indice unico filtrado sobre `content_sha256 + format_version`, solo cuando `content_sha256 IS NOT NULL`.

Por eso dos solicitudes simultaneas del mismo contenido no deben crear dos procesos.

## Reintentos

Si el archivo ya esta en cola o procesandose, se retorna el proceso existente.
Si ya finalizo, se informa el batch anterior.
Si fallo y quedo en estado seguro, se permite un reintento controlado con confirmacion del usuario.

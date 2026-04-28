<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Resumen Cargue Gestantes Tipo 3</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <h2 style="margin:0 0 12px 0;">Resumen de Cargue Gestantes Tipo 3</h2>

    <p style="margin:0 0 6px 0;"><strong>Usuario:</strong> <?php echo e($usuario); ?></p>
    <p style="margin:0 0 6px 0;"><strong>Estado:</strong> <?php echo e($estado); ?></p>
    <p style="margin:0 0 6px 0;"><strong>Lote:</strong> #<?php echo e($batchId); ?></p>
    <p style="margin:0 0 6px 0;"><strong>Archivo:</strong> <?php echo e($originalFilename); ?></p>
    <p style="margin:0 0 16px 0;"><strong>Mensaje:</strong> <?php echo e($resumen); ?></p>

    <table cellpadding="6" cellspacing="0" border="1" style="border-collapse:collapse; font-size:13px;">
        <tr><td><strong>Filas leidas</strong></td><td><?php echo e((int)($counters['rows_total'] ?? 0)); ?></td></tr>
        <tr><td><strong>Filas creadas</strong></td><td><?php echo e((int)($counters['rows_created'] ?? 0)); ?></td></tr>
        <tr><td><strong>Duplicados detectados</strong></td><td><?php echo e((int)($counters['rows_duplicated'] ?? 0)); ?></td></tr>
        <tr><td><strong>Filas omitidas</strong></td><td><?php echo e((int)($counters['rows_skipped'] ?? 0)); ?></td></tr>
        <tr><td><strong>Filas invalidas</strong></td><td><?php echo e((int)($counters['rows_invalid'] ?? 0)); ?></td></tr>
        <tr><td><strong>Errores detectados</strong></td><td><?php echo e((int)($counters['errors_count'] ?? 0)); ?></td></tr>
        <tr><td><strong>Duracion (seg)</strong></td><td><?php echo e($counters['duration_seconds'] ?? '-'); ?></td></tr>
    </table>

    <?php if(!empty($errors)): ?>
        <h3 style="margin:16px 0 8px 0;">Errores detectados (muestra):</h3>
        <ul style="padding-left:18px; margin:0;">
            <?php $__currentLoopData = $errors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\Apache24\htdocs\Desnutricion\resources\views/mail/ges_tipo3_import_resumen.blade.php ENDPATH**/ ?>
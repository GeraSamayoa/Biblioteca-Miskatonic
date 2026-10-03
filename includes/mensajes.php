<?php
 // Muestra el resultado de la última operación (éxito o error).
$mensaje = obtenerMensaje();
if ($mensaje):
    $iconos = [
        'success' => 'bi-check-circle',
        'danger'  => 'bi-x-circle',
        'warning' => 'bi-exclamation-triangle',
        'info'    => 'bi-info-circle',
    ];
?>
    <div class="alert alert-<?= e($mensaje['tipo']) ?> alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="bi <?= $iconos[$mensaje['tipo']] ?? 'bi-info-circle' ?>"></i>
        <div><?= e($mensaje['texto']) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

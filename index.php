<?php
// Página de inicio: resumen general de la biblioteca y accesos rápidos.

require_once __DIR__ . '/config.php';

$libros    = obtenerLibros();
$prestamos = obtenerPrestamos();

// Estadísticas
$totalTitulos     = count($libros);
$totalEjemplares  = array_sum(array_column($libros, 'ejemplares'));
$totalDisponibles = array_sum(array_column($libros, 'disponibles'));
$activos  = count(array_filter($prestamos, fn($p) => $p['estado_actual'] === 'Activo'));
$vencidos = array_values(array_filter($prestamos, fn($p) => $p['estado_actual'] === 'Vencido'));

$titulo = 'Inicio';
$paginaActiva = 'inicio';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Bienvenido</h1>
    <p class="text-secondary mb-0">Gestiona los libros, préstamos y devoluciones de la biblioteca.</p>
</div>

<!-- Estadísticas -->
<div class="row g-3 mb-4">
    <?php
    $stats = [
        ['Títulos registrados', $totalTitulos, 'bi-book'],
        ['Ejemplares disponibles', "$totalDisponibles / $totalEjemplares", 'bi-stack'],
        ['Préstamos activos', $activos, 'bi-journal-arrow-up'],
        ['Préstamos vencidos', count($vencidos), 'bi-alarm'],
    ];
    foreach ($stats as [$etiqueta, $valor, $icono]): ?>
        <div class="col-6 col-lg-3">
            <div class="card p-3 h-100">
                <span class="icono d-inline-flex align-items-center justify-content-center rounded-3 fs-5 bg-primary-subtle text-primary mb-3"><i class="bi <?= $icono ?>"></i></span>
                <div class="fs-3 fw-bold lh-sm"><?= e($valor) ?></div>
                <div class="small text-secondary"><?= $etiqueta ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- Accesos rápidos -->
    <div class="col-lg-5">
        <h2 class="h6 fw-semibold mb-3">Accesos rápidos</h2>
        <div class="list-group">
            <a class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3" href="libro_form.php">
                <span class="icono d-inline-flex align-items-center justify-content-center rounded-3 fs-5 bg-primary-subtle text-primary"><i class="bi bi-plus-lg"></i></span>
                <div><div class="fw-semibold">Registrar libro</div><small class="text-muted">Agrega un nuevo título al catálogo</small></div>
                <i class="bi bi-arrow-right ms-auto text-primary"></i>
            </a>
            <a class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3" href="prestamos.php">
                <span class="icono d-inline-flex align-items-center justify-content-center rounded-3 fs-5 bg-primary-subtle text-primary"><i class="bi bi-journal-arrow-up"></i></span>
                <div><div class="fw-semibold">Nuevo préstamo</div><small class="text-muted">Presta un ejemplar a un estudiante</small></div>
                <i class="bi bi-arrow-right ms-auto text-primary"></i>
            </a>
            <a class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3" href="devoluciones.php">
                <span class="icono d-inline-flex align-items-center justify-content-center rounded-3 fs-5 bg-primary-subtle text-primary"><i class="bi bi-journal-arrow-down"></i></span>
                <div><div class="fw-semibold">Registrar devolución</div><small class="text-muted">Marca un préstamo como devuelto</small></div>
                <i class="bi bi-arrow-right ms-auto text-primary"></i>
            </a>
        </div>
    </div>

    <!-- Préstamos vencidos -->
    <div class="col-lg-7">
        <h2 class="h6 fw-semibold mb-3">Préstamos vencidos</h2>
        <div class="card">
            <?php if (empty($vencidos)): ?>
                <div class="text-center text-secondary py-5"><i class="bi bi-emoji-smile fs-1 d-block mb-2 text-primary opacity-25"></i>No hay préstamos vencidos.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase"><tr><th>Préstamo</th><th>Estudiante</th><th>Libro</th><th>Debía devolver</th></tr></thead>
                        <tbody>
                        <?php foreach ($vencidos as $p): ?>
                            <tr>
                                <td class="font-monospace text-primary fw-semibold small text-nowrap"><?= e($p['codigo']) ?></td>
                                <td><?= e($p['nombre']) ?><br><small class="text-muted"><?= e($p['carne']) ?></small></td>
                                <td class="font-monospace text-primary fw-semibold small text-nowrap"><?= e($p['codigo_libro']) ?></td>
                                <td><span class="badge rounded-pill fw-medium bg-danger-subtle text-danger-emphasis"><?= formatoFecha($p['fecha_devolucion_prevista']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

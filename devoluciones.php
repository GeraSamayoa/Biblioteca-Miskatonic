<?php
// Registro de devoluciones: muestra los préstamos pendientes (activos y vencidos) y permite marcarlos como devueltos

require_once __DIR__ . '/config.php';

// Registrar devolución
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = post('codigo');
    if ($codigo === '') {
        setMensaje('warning', 'Ingresa el código del préstamo.');
    } else {
        $error = registrarDevolucion($codigo);
        if ($error) {
            setMensaje('danger', $error);
        } else {
            setMensaje('success', 'Devolución registrada correctamente. El ejemplar vuelve a estar disponible.');
        }
    }
    redirigir('devoluciones.php');
}

// Búsqueda opcional por carné o código de libro
$buscar = trim($_GET['buscar'] ?? '');

$pendientes = array_filter(obtenerPrestamos(), function ($p) use ($buscar) {
    if ($p['estado_actual'] === 'Devuelto') return false;
    if ($buscar === '') return true;
    return mb_stripos($p['carne'], $buscar) !== false
        || mb_stripos($p['codigo'], $buscar) !== false
        || mb_stripos($p['codigo_libro'], $buscar) !== false
        || mb_stripos($p['nombre'], $buscar) !== false;
});

// Títulos de libros para mostrarlos en la tabla
$titulos = array_column(obtenerLibros(), 'titulo', 'codigo');

$titulo = 'Devoluciones';
$paginaActiva = 'devoluciones';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Devoluciones</h1>
    <p class="text-secondary mb-0">Registra la devolución de un libro prestado.</p>
</div>

<div class="row g-4">
    <!-- Devolución por código -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white fw-semibold py-3"><i class="bi bi-upc-scan text-primary me-2"></i>Devolución por código</div>
            <div class="card-body p-4">
                <form method="post" data-confirmar="¿Confirmas la devolución de este préstamo?">
                    <label for="codigoDev" class="form-label requerido">Código del préstamo</label>
                    <input type="text" class="form-control" id="codigoDev" name="codigo" placeholder="P0001" required>
                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-journal-arrow-down me-1"></i>Registrar devolución
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Préstamos pendientes -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white fw-semibold py-3">
                <form method="get" class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <span>Préstamos pendientes de devolución</span>
                    <div class="d-flex gap-2">
                        <input type="search" name="buscar" class="form-control form-control-sm"
                               value="<?= e($buscar) ?>" placeholder="Carné, nombre o código" aria-label="Buscar préstamo">
                        <button class="btn btn-sm btn-outline-secondary" type="submit" aria-label="Buscar"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
            <?php if (empty($pendientes)): ?>
                <div class="text-center text-secondary py-5"><i class="bi bi-check2-circle fs-1 d-block mb-2 text-primary opacity-25"></i>No hay préstamos pendientes<?= $buscar ? ' con ese criterio' : '' ?>.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase"><tr><th>Préstamo</th><th>Libro</th><th>Estudiante</th><th>Devolver</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($pendientes as $p): ?>
                            <tr>
                                <td class="font-monospace text-primary fw-semibold small text-nowrap"><?= e($p['codigo']) ?></td>
                                <td>
                                    <span class="font-monospace text-primary fw-semibold small text-nowrap"><?= e($p['codigo_libro']) ?></span><br>
                                    <small class="text-muted"><?= e($titulos[$p['codigo_libro']] ?? '') ?></small>
                                </td>
                                <td><?= e($p['nombre']) ?><br><small class="text-muted"><?= e($p['carne']) ?></small></td>
                                <td class="text-nowrap">
                                    <?= formatoFecha($p['fecha_devolucion_prevista']) ?><br>
                                    <span class="badge rounded-pill fw-medium <?= claseEstado($p['estado_actual']) ?>"><?= e($p['estado_actual']) ?></span>
                                </td>
                                <td class="text-end">
                                    <form method="post" class="d-inline" data-confirmar="¿Registrar la devolución del préstamo <?= e($p['codigo']) ?>?">
                                        <input type="hidden" name="codigo" value="<?= e($p['codigo']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">
                                            <i class="bi bi-check2 me-1"></i>Devolver
                                        </button>
                                    </form>
                                </td>
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

<?php
// Consulta de préstamos diferenciados por estado: Activo, Devuelto y Vencido
// Permite filtrar también por carné y código de libro.

require_once __DIR__ . '/config.php';

$filtros = [
    'estado'       => trim($_GET['estado'] ?? ''),
    'carne'        => trim($_GET['carne'] ?? ''),
    'codigo_libro' => trim($_GET['codigo_libro'] ?? ''),
];
if (!in_array($filtros['estado'], ['', 'Activo', 'Devuelto', 'Vencido'], true)) {
    $filtros['estado'] = '';
}

$todos = obtenerPrestamos();
$prestamos = array_reverse(filtrarPrestamos($filtros));

// Conteo por estado para las pestañas
$conteo = ['' => count($todos), 'Activo' => 0, 'Vencido' => 0, 'Devuelto' => 0];
foreach ($todos as $p) {
    $conteo[$p['estado_actual']]++;
}

$titulos = array_column(obtenerLibros(), 'titulo', 'codigo');

// Construye la URL de una pestaña conservando los otros filtros
function urlEstado(string $estado, array $filtros): string
{
    return 'consultas.php?' . http_build_query(array_filter(['estado' => $estado] + $filtros));
}

$titulo = 'Consultas';
$paginaActiva = 'consultas';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Consultas</h1>
    <p class="text-secondary mb-0">Consulta los préstamos por estado, estudiante o libro.</p>
</div>

<!-- Pestañas por estado -->
<ul class="nav nav-pills gap-1 mb-3">
    <?php foreach (['' => 'Todos', 'Activo' => 'Activos', 'Vencido' => 'Vencidos', 'Devuelto' => 'Devueltos'] as $est => $texto): ?>
        <li class="nav-item">
            <a class="nav-link <?= $filtros['estado'] === $est ? 'active' : '' ?>" href="<?= e(urlEstado($est, ['carne' => $filtros['carne'], 'codigo_libro' => $filtros['codigo_libro']])) ?>">
                <?= $texto ?> <span class="ms-1 opacity-75"><?= $conteo[$est] ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<!-- Filtros adicionales -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <input type="hidden" name="estado" value="<?= e($filtros['estado']) ?>">
            <div class="col-sm-5">
                <label for="carne" class="form-label">Carné del estudiante</label>
                <input type="text" class="form-control" id="carne" name="carne" value="<?= e($filtros['carne']) ?>" placeholder="0900-23-12345">
            </div>
            <div class="col-sm-4">
                <label for="codigo_libro" class="form-label">Código del libro</label>
                <input type="text" class="form-control" id="codigo_libro" name="codigo_libro" value="<?= e($filtros['codigo_libro']) ?>" placeholder="LIB-001">
            </div>
            <div class="col-sm-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="consultas.php" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Resultados -->
<div class="card">
    <div class="card-header bg-white fw-semibold py-3 d-flex justify-content-between">
        <span>Préstamos <?= $filtros['estado'] ? '— ' . e($filtros['estado']) . 's' : '' ?></span>
        <span class="text-muted fw-normal"><?= count($prestamos) ?> registro(s)</span>
    </div>
    <?php if (empty($prestamos)): ?>
        <div class="text-center text-secondary py-5"><i class="bi bi-search fs-1 d-block mb-2 text-primary opacity-25"></i>No hay préstamos que coincidan.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                <tr><th>Préstamo</th><th>Libro</th><th>Estudiante</th><th>Prestado</th><th>Prevista</th><th>Devuelto el</th><th>Estado</th></tr>
                </thead>
                <tbody>
                <?php foreach ($prestamos as $p): ?>
                    <tr>
                        <td class="font-monospace text-primary fw-semibold small text-nowrap"><?= e($p['codigo']) ?></td>
                        <td>
                            <span class="font-monospace text-primary fw-semibold small text-nowrap"><?= e($p['codigo_libro']) ?></span><br>
                            <small class="text-muted"><?= e($titulos[$p['codigo_libro']] ?? '(libro eliminado)') ?></small>
                        </td>
                        <td><?= e($p['nombre']) ?><br><small class="text-muted"><?= e($p['carne']) ?></small></td>
                        <td class="text-nowrap"><?= formatoFecha($p['fecha_prestamo']) ?></td>
                        <td class="text-nowrap"><?= formatoFecha($p['fecha_devolucion_prevista']) ?></td>
                        <td class="text-nowrap"><?= formatoFecha($p['fecha_devolucion_real']) ?></td>
                        <td><span class="badge rounded-pill fw-medium <?= claseEstado($p['estado_actual']) ?>"><?= e($p['estado_actual']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

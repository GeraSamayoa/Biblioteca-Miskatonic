<?php
// Registro de préstamos y listado de los préstamos realizados.

require_once __DIR__ . '/config.php';

$errores = [];
$datos = [
    'codigo'                    => siguienteCodigoPrestamo(),
    'codigo_libro'              => trim($_GET['libro'] ?? ''),
    'carne'                     => '',
    'nombre'                    => '',
    'fecha_prestamo'            => hoy(),
    'fecha_devolucion_prevista' => date('Y-m-d', strtotime('+7 days')),
];

// Registrar préstamo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($datos) as $campo) {
        $datos[$campo] = post($campo);
    }
    $errores = validarPrestamo($datos);

    if (empty($errores)) {
        registrarPrestamo($datos);
        setMensaje('success', 'Préstamo registrado correctamente.');
        redirigir('prestamos.php');
    }
}

// Libros disponibles para la lista de sugerencias
$librosDisponibles = array_filter(obtenerLibros(), fn($l) => $l['estado'] === 'Activo' && $l['disponibles'] > 0);

// Préstamos más recientes primero
$prestamos = array_reverse(obtenerPrestamos());

$titulo = 'Préstamos';
$paginaActiva = 'prestamos';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Préstamos</h1>
    <p class="text-secondary mb-0">Registra el préstamo de un ejemplar a un estudiante.</p>
</div>

<div class="row g-4">
    <!-- Formulario -->
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header bg-white fw-semibold py-3"><i class="bi bi-journal-plus text-primary me-2"></i>Nuevo préstamo</div>
            <div class="card-body p-4">
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger py-2 small"><i class="bi bi-x-circle me-1"></i>
                        <?= isset($errores['codigo_libro']) ? e($errores['codigo_libro']) : 'Revisa los campos marcados.' ?>
                    </div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="codigo" class="form-label requerido">Código del préstamo</label>
                            <input type="text" class="form-control <?= isset($errores['codigo']) ? 'is-invalid' : '' ?>"
                                   id="codigo" name="codigo" value="<?= e($datos['codigo']) ?>" maxlength="15" required>
                            <div class="invalid-feedback"><?= e($errores['codigo'] ?? '') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <label for="codigo_libro" class="form-label requerido">Código del libro</label>
                            <input type="text" class="form-control <?= isset($errores['codigo_libro']) ? 'is-invalid' : '' ?>"
                                   id="codigo_libro" name="codigo_libro" list="listaLibros"
                                   value="<?= e($datos['codigo_libro']) ?>" placeholder="LIB-001" required>
                            <datalist id="listaLibros">
                                <?php foreach ($librosDisponibles as $l): ?>
                                    <option value="<?= e($l['codigo']) ?>"><?= e($l['titulo']) ?> (<?= $l['disponibles'] ?> disp.)</option>
                                <?php endforeach; ?>
                            </datalist>
                            <div class="invalid-feedback"><?= e($errores['codigo_libro'] ?? '') ?></div>
                        </div>
                        <div class="col-sm-5">
                            <label for="carne" class="form-label requerido">Carné</label>
                            <input type="text" class="form-control <?= isset($errores['carne']) ? 'is-invalid' : '' ?>"
                                   id="carne" name="carne" value="<?= e($datos['carne']) ?>"
                                   placeholder="0900-23-12345" maxlength="20" required>
                            <div class="invalid-feedback"><?= e($errores['carne'] ?? '') ?></div>
                        </div>
                        <div class="col-sm-7">
                            <label for="nombre" class="form-label requerido">Nombre del estudiante</label>
                            <input type="text" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>"
                                   id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" maxlength="100" required>
                            <div class="invalid-feedback"><?= e($errores['nombre'] ?? '') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <label for="fecha_prestamo" class="form-label requerido">Fecha del préstamo</label>
                            <input type="date" class="form-control <?= isset($errores['fecha_prestamo']) ? 'is-invalid' : '' ?>"
                                   id="fecha_prestamo" name="fecha_prestamo" value="<?= e($datos['fecha_prestamo']) ?>" required>
                            <div class="invalid-feedback"><?= e($errores['fecha_prestamo'] ?? '') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <label for="fecha_devolucion_prevista" class="form-label requerido">Devolución prevista</label>
                            <input type="date" class="form-control <?= isset($errores['fecha_devolucion_prevista']) ? 'is-invalid' : '' ?>"
                                   id="fecha_devolucion_prevista" name="fecha_devolucion_prevista"
                                   value="<?= e($datos['fecha_devolucion_prevista']) ?>" required>
                            <div class="invalid-feedback"><?= e($errores['fecha_devolucion_prevista'] ?? '') ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Estado del préstamo</label>
                            <input type="text" class="form-control bg-primary-subtle" value="Activo" readonly>
                            <div class="form-text">Todo préstamo nuevo inicia como Activo.</div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-4">
                        <i class="bi bi-check-lg me-1"></i>Registrar préstamo
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Listado -->
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header bg-white fw-semibold py-3 d-flex justify-content-between">
                <span>Préstamos registrados</span>
                <a href="consultas.php" class="small text-decoration-none fw-normal">Ver consultas <i class="bi bi-arrow-right"></i></a>
            </div>
            <?php if (empty($prestamos)): ?>
                <div class="text-center text-secondary py-5"><i class="bi bi-journal fs-1 d-block mb-2 text-primary opacity-25"></i>Aún no hay préstamos registrados.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase"><tr><th>Código</th><th>Libro</th><th>Estudiante</th><th>Fechas</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php foreach ($prestamos as $p): ?>
                            <tr>
                                <td class="font-monospace text-primary fw-semibold small text-nowrap"><?= e($p['codigo']) ?></td>
                                <td class="font-monospace text-primary fw-semibold small text-nowrap"><?= e($p['codigo_libro']) ?></td>
                                <td><?= e($p['nombre']) ?><br><small class="text-muted"><?= e($p['carne']) ?></small></td>
                                <td class="text-nowrap small">
                                    <span class="text-muted">Prestado:</span> <?= formatoFecha($p['fecha_prestamo']) ?><br>
                                    <span class="text-muted">Devolver:</span> <?= formatoFecha($p['fecha_devolucion_prevista']) ?>
                                </td>
                                <td><span class="badge rounded-pill fw-medium <?= claseEstado($p['estado_actual']) ?>"><?= e($p['estado_actual']) ?></span></td>
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

<?php
// Listado de libros con búsqueda por código, título, autor y categoría.
// También procesa la eliminación (con confirmación previa en un modal).

require_once __DIR__ . '/config.php';

// Eliminar libro (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('accion') === 'eliminar') {
    $error = eliminarLibro(post('codigo'));
    if ($error) {
        setMensaje('danger', $error);
    } else {
        setMensaje('success', 'Libro eliminado correctamente.');
    }
    redirigir('libros.php');
}

// Búsqueda (GET)
$criterios = [
    'codigo'    => trim($_GET['codigo'] ?? ''),
    'titulo'    => trim($_GET['titulo'] ?? ''),
    'autor'     => trim($_GET['autor'] ?? ''),
    'categoria' => trim($_GET['categoria'] ?? ''),
];
$hayFiltro = implode('', $criterios) !== '';
$libros = filtrarLibros($criterios);

$titulo = 'Libros';
$paginaActiva = 'libros';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-4 d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Libros</h1>
        <p class="text-secondary mb-0">Catálogo de libros registrados en la biblioteca.</p>
    </div>
    <a href="libro_form.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Registrar libro</a>
</div>

<!-- Formulario de búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-sm-6 col-lg-2">
                <label for="codigo" class="form-label">Código</label>
                <input type="text" class="form-control" id="codigo" name="codigo" value="<?= e($criterios['codigo']) ?>" placeholder="LIB-001">
            </div>
            <div class="col-sm-6 col-lg-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="<?= e($criterios['titulo']) ?>" placeholder="Buscar por título">
            </div>
            <div class="col-sm-6 col-lg-3">
                <label for="autor" class="form-label">Autor</label>
                <input type="text" class="form-control" id="autor" name="autor" value="<?= e($criterios['autor']) ?>" placeholder="Buscar por autor">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="categoria" class="form-label">Categoría</label>
                <select class="form-select" id="categoria" name="categoria">
                    <option value="">Todas</option>
                    <?php foreach (CATEGORIAS as $cat): ?>
                        <option value="<?= e($cat) ?>" <?= $criterios['categoria'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Buscar</button>
                <?php if ($hayFiltro): ?>
                    <a href="libros.php" class="btn btn-outline-secondary" title="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de libros -->
<div class="card">
    <div class="card-header bg-white fw-semibold py-3 d-flex justify-content-between">
        <span><?= $hayFiltro ? 'Resultados de la búsqueda' : 'Todos los libros' ?></span>
        <span class="text-muted fw-normal"><?= count($libros) ?> registro(s)</span>
    </div>
    <?php if (empty($libros)): ?>
        <div class="text-center text-secondary py-5">
            <i class="bi bi-book fs-1 d-block mb-2 text-primary opacity-25"></i>
            <?= $hayFiltro ? 'No se encontraron libros con esos criterios.' : 'Aún no hay libros registrados.' ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                <tr>
                    <th>Código</th><th>Título</th><th>Autor</th><th>Categoría</th>
                    <th class="text-center">Año</th><th class="text-center">Disponibles</th>
                    <th>Estado</th><th class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($libros as $l): ?>
                    <tr>
                        <td class="font-monospace text-primary fw-semibold small text-nowrap"><?= e($l['codigo']) ?></td>
                        <td class="fw-medium"><?= e($l['titulo']) ?></td>
                        <td><?= e($l['autor']) ?></td>
                        <td><?= e($l['categoria']) ?></td>
                        <td class="text-center"><?= e($l['anio']) ?></td>
                        <td class="text-center">
                            <span class="badge rounded-pill fw-medium <?= $l['disponibles'] > 0 ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis' ?>">
                                <?= e($l['disponibles']) ?> / <?= e($l['ejemplares']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge rounded-pill fw-medium <?= $l['estado'] === 'Activo' ? 'bg-primary-subtle text-primary-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>"><?= e($l['estado']) ?></span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="libro_form.php?codigo=<?= urlencode($l['codigo']) ?>" class="btn btn-sm btn-outline-secondary" title="Modificar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Eliminar"
                                    data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                    data-codigo="<?= e($l['codigo']) ?>" data-titulo="<?= e($l['titulo']) ?>">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
            <div class="modal-body p-4 text-center">
                <span class="icono d-inline-flex align-items-center justify-content-center rounded-3 fs-5 bg-danger-subtle text-danger mb-3"><i class="bi bi-trash3"></i></span>
                <h2 class="h5 fw-semibold" id="modalEliminarLabel">¿Eliminar libro?</h2>
                <p class="text-muted mb-0">Se eliminará <strong id="eliminarTitulo"></strong>. Esta acción no se puede deshacer.</p>
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="codigo" id="eliminarCodigo">
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Sí, eliminar</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

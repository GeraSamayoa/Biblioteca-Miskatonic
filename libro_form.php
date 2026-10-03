<?php
// Formulario para registrar un libro nuevo o modificar uno existente.

require_once __DIR__ . '/config.php';

$codigoEditar = trim($_GET['codigo'] ?? '');
$esEdicion = $codigoEditar !== '';
$errores = [];

// Valores iniciales del formulario
$datos = [
    'codigo' => '', 'titulo' => '', 'autor' => '', 'categoria' => '',
    'anio' => '', 'ejemplares' => '1', 'estado' => 'Activo',
];

// Si es edición, cargar el libro
if ($esEdicion) {
    $libro = buscarLibroPorCodigo($codigoEditar);
    if ($libro === null) {
        setMensaje('danger', 'El libro que intentas modificar no existe.');
        redirigir('libros.php');
    }
    $datos = array_map('strval', $libro);
}

// Procesar envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($datos) as $campo) {
        if ($campo === 'disponibles') continue;
        $datos[$campo] = post($campo);
    }
    // En edición el código no se puede cambiar
    if ($esEdicion) {
        $datos['codigo'] = $libro['codigo'];
    }

    $errores = validarLibro($datos, $esEdicion);

    if (empty($errores)) {
        if ($esEdicion) {
            $error = actualizarLibro($libro['codigo'], $datos);
            if ($error) {
                $errores['ejemplares'] = $error;
            } else {
                setMensaje('success', 'Libro actualizado correctamente.');
                redirigir('libros.php');
            }
        } else {
            registrarLibro($datos);
            setMensaje('success', 'Libro registrado correctamente.');
            redirigir('libros.php');
        }
    }
}

// Devuelve la clase is-invalid si el campo tiene error
function claseError(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? 'is-invalid' : '';
}

$titulo = $esEdicion ? 'Modificar libro' : 'Registrar libro';
$paginaActiva = 'libros';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <a href="libros.php" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Volver a libros</a>
    <h1 class="h3 fw-bold mt-2 mb-1"><?= $titulo ?></h1>
    <p class="text-secondary mb-0"><?= $esEdicion ? 'Actualiza la información del libro.' : 'Completa los datos para agregar un libro al catálogo.' ?></p>
</div>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i>Revisa los campos marcados.</div>
<?php endif; ?>

<div class="card col-xl-8 px-0">
    <div class="card-body p-4">
        <form method="post" novalidate>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="codigo" class="form-label requerido">Código del libro</label>
                    <input type="text" class="form-control <?= claseError($errores, 'codigo') ?>" id="codigo" name="codigo"
                           value="<?= e($datos['codigo']) ?>" maxlength="15" placeholder="LIB-001"
                           pattern="[A-Za-z0-9-]{3,15}" required <?= $esEdicion ? 'readonly' : '' ?>>
                    <div class="invalid-feedback"><?= e($errores['codigo'] ?? '') ?></div>
                    <?php if ($esEdicion): ?><div class="form-text">El código no se puede modificar.</div><?php endif; ?>
                </div>
                <div class="col-md-8">
                    <label for="titulo" class="form-label requerido">Título</label>
                    <input type="text" class="form-control <?= claseError($errores, 'titulo') ?>" id="titulo" name="titulo"
                           value="<?= e($datos['titulo']) ?>" maxlength="150" required>
                    <div class="invalid-feedback"><?= e($errores['titulo'] ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <label for="autor" class="form-label requerido">Autor</label>
                    <input type="text" class="form-control <?= claseError($errores, 'autor') ?>" id="autor" name="autor"
                           value="<?= e($datos['autor']) ?>" maxlength="100" required>
                    <div class="invalid-feedback"><?= e($errores['autor'] ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <label for="categoria" class="form-label requerido">Categoría</label>
                    <select class="form-select <?= claseError($errores, 'categoria') ?>" id="categoria" name="categoria" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach (CATEGORIAS as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= $datos['categoria'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"><?= e($errores['categoria'] ?? '') ?></div>
                </div>
                <div class="col-md-4">
                    <label for="anio" class="form-label requerido">Año de publicación</label>
                    <input type="number" class="form-control <?= claseError($errores, 'anio') ?>" id="anio" name="anio"
                           value="<?= e($datos['anio']) ?>" min="1000" max="<?= date('Y') ?>" step="1" required>
                    <div class="invalid-feedback"><?= e($errores['anio'] ?? '') ?></div>
                </div>
                <div class="col-md-4">
                    <label for="ejemplares" class="form-label requerido">Cantidad de ejemplares</label>
                    <input type="number" class="form-control <?= claseError($errores, 'ejemplares') ?>" id="ejemplares" name="ejemplares"
                           value="<?= e($datos['ejemplares']) ?>" min="1" max="999" step="1" required>
                    <div class="invalid-feedback"><?= e($errores['ejemplares'] ?? '') ?></div>
                    <?php if ($esEdicion): ?>
                        <div class="form-text">Disponibles actualmente: <?= e($libro['disponibles']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="estado" class="form-label requerido">Estado</label>
                    <select class="form-select <?= claseError($errores, 'estado') ?>" id="estado" name="estado" required>
                        <?php foreach (ESTADOS_LIBRO as $est): ?>
                            <option value="<?= $est ?>" <?= $datos['estado'] === $est ? 'selected' : '' ?>><?= $est ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"><?= e($errores['estado'] ?? '') ?></div>
                    <div class="form-text">Los libros inactivos no se pueden prestar.</div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4 pt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i><?= $esEdicion ? 'Guardar cambios' : 'Registrar libro' ?>
                </button>
                <a href="libros.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

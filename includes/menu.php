<?php
 // Menú de navegación reutilizable. Resalta la página activa.
$paginaActiva = $paginaActiva ?? '';

$opciones = [
    'inicio'       => ['url' => 'index.php',        'texto' => 'Inicio',       'icono' => 'bi-house'],
    'libros'       => ['url' => 'libros.php',       'texto' => 'Libros',       'icono' => 'bi-book'],
    'prestamos'    => ['url' => 'prestamos.php',    'texto' => 'Préstamos',    'icono' => 'bi-journal-arrow-up'],
    'devoluciones' => ['url' => 'devoluciones.php', 'texto' => 'Devoluciones', 'icono' => 'bi-journal-arrow-down'],
    'consultas'    => ['url' => 'consultas.php',    'texto' => 'Consultas',    'icono' => 'bi-search'],
];
?>
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <span class="icono d-inline-flex align-items-center justify-content-center rounded-3 fs-5 bg-primary text-white"><i class="bi bi-book-half"></i></span>
            <span class="fw-semibold"><?= APP_NOMBRE ?></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal" aria-controls="menuPrincipal"
                aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav nav-pills ms-auto gap-lg-1">
                <?php foreach ($opciones as $clave => $op): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3 <?= $paginaActiva === $clave ? 'active' : '' ?>"
                           href="<?= $op['url'] ?>"
                           <?= $paginaActiva === $clave ? 'aria-current="page"' : '' ?>>
                            <i class="bi <?= $op['icono'] ?> me-1"></i><?= $op['texto'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>

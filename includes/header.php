<?php
// Encabezado común: etiqueta <head>, estilos y apertura del <body>.
// Variables esperadas: $titulo (título de la página), $paginaActiva (para el menú).
$titulo = $titulo ?? APP_NOMBRE;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · <?= APP_NOMBRE ?></title>

    <!-- Tipografía -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 e iconos (copias locales, funcionan sin internet) -->
    <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Estilos propios (blanco y morado) -->
    <link href="assets/css/estilos.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<?php require __DIR__ . '/menu.php'; ?>
<main class="container py-4 py-lg-5 flex-grow-1">
<?php require __DIR__ . '/mensajes.php'; ?>

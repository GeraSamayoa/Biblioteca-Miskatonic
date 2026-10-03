<?php

// Configuración general de la aplicación.
// Se incluye al inicio de cada página.

// Zona horaria de Guatemala para que las fechas de préstamo sean correctas
date_default_timezone_set('America/Guatemala');

// Iniciar sesión (se usa para los mensajes de resultado de las operaciones)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rutas base
define('BASE_PATH', __DIR__);
define('DATA_PATH', BASE_PATH . '/data');

// Archivos de persistencia (archivos de texto .txt separados por |)
define('ARCHIVO_LIBROS', DATA_PATH . '/libros.txt');
define('ARCHIVO_PRESTAMOS', DATA_PATH . '/prestamos.txt');

// Nombre de la aplicación
define('APP_NOMBRE', 'Biblioteca Miskatonic');

// Catálogos usados en los formularios
const CATEGORIAS = [
    'Ciencia', 'Tecnología', 'Literatura', 'Historia', 'Filosofía',
    'Matemáticas', 'Arte', 'Ocultismo', 'Referencia', 'Otro'
];
const ESTADOS_LIBRO = ['Activo', 'Inactivo'];

// Carga de la lógica de la aplicación
require_once BASE_PATH . '/lib/archivo.php';
require_once BASE_PATH . '/lib/utilidades.php';
require_once BASE_PATH . '/lib/libros.php';
require_once BASE_PATH . '/lib/prestamos.php';

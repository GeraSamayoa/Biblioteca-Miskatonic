<?php
// Funciones de apoyo: escape de HTML, mensajes flash, redirecciones y formato de fechas
    
// Escapa texto para mostrarlo de forma segura en HTML.
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// Guarda un mensaje para mostrarlo en la siguiente página. Tipos: success, danger, warning, info
function setMensaje(string $tipo, string $texto): void
{
    $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
}

// Obtiene y elimina el mensaje pendiente
function obtenerMensaje(): ?array
{
    if (!isset($_SESSION['mensaje'])) {
        return null;
    }
    $m = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
    return $m;
}

// Redirige a otra página y detiene la ejecución
function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// Devuelve un valor de $_POST limpio (sin espacios a los lados)
function post(string $campo): string
{
    return trim((string) ($_POST[$campo] ?? ''));
}

// Convierte una fecha AAAA-MM-DD a DD/MM/AAAA
function formatoFecha(?string $fecha): string
{
    if (empty($fecha)) {
        return '—';
    }
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d ? $d->format('d/m/Y') : e($fecha);
}

// Valida que una cadena sea una fecha real con formato AAAA-MM-DD
function esFechaValida(string $fecha): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

// Fecha de hoy en formato AAAA-MM-DD
function hoy(): string
{
    return date('Y-m-d');
}


// Respaldo por si la extensión mbstring no está habilitada en el php.ini
if (!function_exists('mb_stripos')) {
    function mb_stripos(string $texto, string $buscar): int|false
    {
        return stripos($texto, $buscar);
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $texto): int
    {
        return preg_match_all('/./us', $texto);
    }
}

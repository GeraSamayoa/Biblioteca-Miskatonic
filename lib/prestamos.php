<?php

// Lógica de negocio de préstamos y devoluciones.
// Estados guardados: Activo y Devuelto
// El estado Vencido se calcula: préstamo Activo cuya fecha prevista ya pasó

// Devuelve todos los préstamos con su estado actual calculado
function obtenerPrestamos(): array
{
    $prestamos = leerArchivo(ARCHIVO_PRESTAMOS);
    foreach ($prestamos as &$p) {
        $p['estado_actual'] = estadoActualPrestamo($p);
    }
    unset($p);
    return $prestamos;
}

// Calcula el estado real del préstamo (Activo, Devuelto o Vencido)
function estadoActualPrestamo(array $p): string
{
    if ($p['estado'] === 'Devuelto') {
        return 'Devuelto';
    }
    return ($p['fecha_devolucion_prevista'] < hoy()) ? 'Vencido' : 'Activo';
}

// Busca un préstamo por código
function buscarPrestamo(string $codigo): ?array
{
    foreach (obtenerPrestamos() as $p) {
        if (strcasecmp($p['codigo'], $codigo) === 0) {
            return $p;
        }
    }
    return null;
}

// Cuenta los préstamos sin devolver (activos o vencidos) de un libro
function contarPrestamosActivosDeLibro(string $codigoLibro): int
{
    $total = 0;
    foreach (leerArchivo(ARCHIVO_PRESTAMOS) as $p) {
        if (strcasecmp($p['codigo_libro'], $codigoLibro) === 0 && $p['estado'] === 'Activo') {
            $total++;
        }
    }
    return $total;
}

// Genera el siguiente código de préstamo sugerido: P0001, P0002, etc.
function siguienteCodigoPrestamo(): string
{
    $max = 0;
    foreach (leerArchivo(ARCHIVO_PRESTAMOS) as $p) {
        if (preg_match('/^P(\d+)$/i', $p['codigo'], $m)) {
            $max = max($max, (int) $m[1]);
        }
    }
    return 'P' . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
}

// Valida los datos de un préstamo nuevo, devuelve arreglo de errores
function validarPrestamo(array $d): array
{
    $errores = [];
    $obligatorios = [
        'codigo' => 'código del préstamo', 'codigo_libro' => 'código del libro',
        'carne' => 'carné', 'nombre' => 'nombre del estudiante',
        'fecha_prestamo' => 'fecha del préstamo', 'fecha_devolucion_prevista' => 'fecha prevista de devolución',
    ];
    foreach ($obligatorios as $campo => $nombre) {
        if (($d[$campo] ?? '') === '') {
            $errores[$campo] = "El campo $nombre es obligatorio.";
        }
    }

    // Código del préstamo
    if (!isset($errores['codigo'])) {
        if (!preg_match('/^[A-Za-z0-9-]{3,15}$/', $d['codigo'])) {
            $errores['codigo'] = 'El código debe tener de 3 a 15 caracteres (letras, números o guiones).';
        } elseif (buscarPrestamo($d['codigo']) !== null) {
            $errores['codigo'] = 'Ya existe un préstamo con ese código.';
        }
    }

    // Carné: formato UMG común (ej. 0900-23-12345) o solo números
    if (!isset($errores['carne']) && !preg_match('/^[0-9-]{5,20}$/', $d['carne'])) {
        $errores['carne'] = 'El carné solo puede contener números y guiones (ej. 0900-23-12345).';
    }

    if (!isset($errores['nombre']) && !preg_match('/^[\p{L} .\'-]{3,100}$/u', $d['nombre'])) {
        $errores['nombre'] = 'El nombre solo puede contener letras y espacios (mínimo 3 caracteres).';
    }

    // Fechas válidas y en orden lógico
    if (!isset($errores['fecha_prestamo']) && !esFechaValida($d['fecha_prestamo'])) {
        $errores['fecha_prestamo'] = 'La fecha del préstamo no es válida.';
    }
    if (!isset($errores['fecha_devolucion_prevista']) && !esFechaValida($d['fecha_devolucion_prevista'])) {
        $errores['fecha_devolucion_prevista'] = 'La fecha prevista no es válida.';
    }
    if (!isset($errores['fecha_prestamo']) && !isset($errores['fecha_devolucion_prevista'])
        && $d['fecha_devolucion_prevista'] < $d['fecha_prestamo']) {
        $errores['fecha_devolucion_prevista'] = 'La fecha prevista no puede ser anterior a la fecha del préstamo.';
    }

    // El libro debe existir, estar activo y tener ejemplares disponibles
    if (!isset($errores['codigo_libro'])) {
        $libro = buscarLibroPorCodigo($d['codigo_libro']);
        if ($libro === null) {
            $errores['codigo_libro'] = 'El libro indicado no existe.';
        } elseif ($libro['estado'] !== 'Activo') {
            $errores['codigo_libro'] = 'El libro está inactivo y no puede prestarse.';
        } elseif ($libro['disponibles'] < 1) {
            $errores['codigo_libro'] = 'No existen ejemplares disponibles.';
        }
    }

    return $errores;
}

// Registra un préstamo y descuenta un ejemplar disponible del libro
function registrarPrestamo(array $d): void
{
    $prestamos = leerArchivo(ARCHIVO_PRESTAMOS);
    $libro = buscarLibroPorCodigo($d['codigo_libro']);

    $prestamos[] = [
        'codigo'                    => strtoupper($d['codigo']),
        'codigo_libro'              => $libro['codigo'],
        'carne'                     => $d['carne'],
        'nombre'                    => $d['nombre'],
        'fecha_prestamo'            => $d['fecha_prestamo'],
        'fecha_devolucion_prevista' => $d['fecha_devolucion_prevista'],
        'fecha_devolucion_real'     => null,
        'estado'                    => 'Activo',
    ];
    guardarArchivo(ARCHIVO_PRESTAMOS, $prestamos);
    ajustarDisponibles($libro['codigo'], -1);
}

// Registra la devolución de un préstamo activo
// Devuelve un mensaje de error o null si se registró correctamente
function registrarDevolucion(string $codigo): ?string
{
    $prestamos = leerArchivo(ARCHIVO_PRESTAMOS);

    foreach ($prestamos as &$p) {
        if (strcasecmp($p['codigo'], $codigo) === 0) {
            if ($p['estado'] === 'Devuelto') {
                return 'Este préstamo ya fue devuelto.';
            }
            $p['estado'] = 'Devuelto';
            $p['fecha_devolucion_real'] = hoy();
            $codigoLibro = $p['codigo_libro'];
            unset($p);
            guardarArchivo(ARCHIVO_PRESTAMOS, $prestamos);
            ajustarDisponibles($codigoLibro, +1);
            return null;
        }
    }
    unset($p);
    return 'El préstamo no existe.';
}

// Filtra préstamos por estado, carné o código de libro
function filtrarPrestamos(array $f): array
{
    return array_values(array_filter(obtenerPrestamos(), function ($p) use ($f) {
        if (!empty($f['estado']) && $p['estado_actual'] !== $f['estado']) return false;
        if (!empty($f['carne']) && mb_stripos($p['carne'], $f['carne']) === false) return false;
        if (!empty($f['codigo_libro']) && mb_stripos($p['codigo_libro'], $f['codigo_libro']) === false) return false;
        return true;
    }));
}

// Clase CSS según el estado del préstamo
function claseEstado(string $estado): string
{
    return match ($estado) {
        'Activo'   => 'bg-primary-subtle text-primary-emphasis',
        'Devuelto' => 'bg-success-subtle text-success-emphasis',
        'Vencido'  => 'bg-danger-subtle text-danger-emphasis',
        default    => 'bg-success-subtle text-success-emphasis',
    };
}

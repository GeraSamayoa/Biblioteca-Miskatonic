<?php
// Lógica de negocio de los libros: registrar, buscar, modificar y eliminar

// Devuelve todos los libros registrados
function obtenerLibros(): array
{
    return leerArchivo(ARCHIVO_LIBROS);
}

// Busca un libro por su código (sin distinguir mayúsculas), devuelve null si no existe
function buscarLibroPorCodigo(string $codigo): ?array
{
    foreach (obtenerLibros() as $libro) {
        if (strcasecmp($libro['codigo'], $codigo) === 0) {
            return $libro;
        }
    }
    return null;
}


// Filtra los libros según uno o varios criterios
// Todos los criterios son opcionales y la búsqueda es parcial
function filtrarLibros(array $criterios): array
{
    $libros = obtenerLibros();

    return array_values(array_filter($libros, function ($libro) use ($criterios) {
        foreach (['codigo', 'titulo', 'autor'] as $campo) {
            $valor = trim($criterios[$campo] ?? '');
            if ($valor !== '' && mb_stripos($libro[$campo], $valor) === false) {
                return false;
            }
        }
        $categoria = trim($criterios['categoria'] ?? '');
        if ($categoria !== '' && $libro['categoria'] !== $categoria) {
            return false;
        }
        return true;
    }));
}


// Valida los datos de un libro, devuelve un arreglo de errores (vacío si todo está bien)
// $esEdicion indica si se está modificando un libro existente
function validarLibro(array $d, bool $esEdicion = false): array
{
    $errores = [];
    $anioActual = (int) date('Y');

    // Campos obligatorios
    $obligatorios = [
        'codigo' => 'código', 'titulo' => 'título', 'autor' => 'autor',
        'categoria' => 'categoría', 'anio' => 'año de publicación',
        'ejemplares' => 'cantidad de ejemplares', 'estado' => 'estado',
    ];
    foreach ($obligatorios as $campo => $nombre) {
        if (($d[$campo] ?? '') === '') {
            $errores[$campo] = "El campo $nombre es obligatorio.";
        }
    }

    // Código: letras, números y guiones (3 a 15 caracteres)
    if (!isset($errores['codigo'])) {
        if (!preg_match('/^[A-Za-z0-9-]{3,15}$/', $d['codigo'])) {
            $errores['codigo'] = 'El código debe tener de 3 a 15 caracteres (letras, números o guiones).';
        } elseif (!$esEdicion && buscarLibroPorCodigo($d['codigo']) !== null) {
            $errores['codigo'] = 'Ya existe un libro con ese código.';
        }
    }

    // Año: número entero entre 1000 y el año actual
    if (!isset($errores['anio'])) {
        if (!ctype_digit($d['anio']) || (int) $d['anio'] < 1000 || (int) $d['anio'] > $anioActual) {
            $errores['anio'] = "El año debe ser un número entre 1000 y $anioActual.";
        }
    }

    // Ejemplares: entero entre 1 y 999
    if (!isset($errores['ejemplares'])) {
        if (!ctype_digit($d['ejemplares']) || (int) $d['ejemplares'] < 1 || (int) $d['ejemplares'] > 999) {
            $errores['ejemplares'] = 'La cantidad de ejemplares debe ser un número entero entre 1 y 999.';
        }
    }

    if (!isset($errores['categoria']) && !in_array($d['categoria'], CATEGORIAS, true)) {
        $errores['categoria'] = 'Selecciona una categoría válida.';
    }
    if (!isset($errores['estado']) && !in_array($d['estado'], ESTADOS_LIBRO, true)) {
        $errores['estado'] = 'Selecciona un estado válido.';
    }
    if (!isset($errores['titulo']) && mb_strlen($d['titulo']) > 150) {
        $errores['titulo'] = 'El título no puede superar 150 caracteres.';
    }
    if (!isset($errores['autor']) && mb_strlen($d['autor']) > 100) {
        $errores['autor'] = 'El autor no puede superar 100 caracteres.';
    }

    return $errores;
}

// Registra un libro nuevo, al inicio todos los ejemplares están disponibles
function registrarLibro(array $d): void
{
    $libros = obtenerLibros();
    $libros[] = [
        'codigo'      => strtoupper($d['codigo']),
        'titulo'      => $d['titulo'],
        'autor'       => $d['autor'],
        'categoria'   => $d['categoria'],
        'anio'        => (int) $d['anio'],
        'ejemplares'  => (int) $d['ejemplares'],
        'disponibles' => (int) $d['ejemplares'],
        'estado'      => $d['estado'],
    ];
    guardarArchivo(ARCHIVO_LIBROS, $libros);
}


// Actualiza un libro existente
// Los disponibles se recalculan: total de ejemplares - préstamos activos
// Devuelve un mensaje de error o null si todo salió bien
function actualizarLibro(string $codigo, array $d): ?string
{
    $libros = obtenerLibros();
    $prestados = contarPrestamosActivosDeLibro($codigo);

    foreach ($libros as &$libro) {
        if (strcasecmp($libro['codigo'], $codigo) === 0) {
            if ((int) $d['ejemplares'] < $prestados) {
                return "No puedes dejar menos de $prestados ejemplares: hay $prestados prestados actualmente.";
            }
            $libro['titulo']      = $d['titulo'];
            $libro['autor']       = $d['autor'];
            $libro['categoria']   = $d['categoria'];
            $libro['anio']        = (int) $d['anio'];
            $libro['ejemplares']  = (int) $d['ejemplares'];
            $libro['disponibles'] = (int) $d['ejemplares'] - $prestados;
            $libro['estado']      = $d['estado'];
            guardarArchivo(ARCHIVO_LIBROS, $libros);
            return null;
        }
    }
    unset($libro);
    return 'El libro no existe.';
}

// Elimina un libro, no se permite si tiene préstamos activos
// Devuelve un mensaje de error o null si se eliminó
function eliminarLibro(string $codigo): ?string
{
    if (buscarLibroPorCodigo($codigo) === null) {
        return 'El libro no existe.';
    }
    if (contarPrestamosActivosDeLibro($codigo) > 0) {
        return 'El libro no puede eliminarse porque tiene un préstamo activo.';
    }

    $libros = array_filter(obtenerLibros(), fn($l) => strcasecmp($l['codigo'], $codigo) !== 0);
    guardarArchivo(ARCHIVO_LIBROS, $libros);
    return null;
}

// Suma (o resta, con valor negativo) ejemplares disponibles de un libro
// Se usa al prestar (-1) y al devolver (+1) para mantener la consistencia
function ajustarDisponibles(string $codigo, int $cambio): void
{
    $libros = obtenerLibros();
    foreach ($libros as &$libro) {
        if (strcasecmp($libro['codigo'], $codigo) === 0) {
            $nuevo = $libro['disponibles'] + $cambio;
            // Nunca menos de 0 ni más del total de ejemplares
            $libro['disponibles'] = max(0, min($libro['ejemplares'], $nuevo));
            break;
        }
    }
    unset($libro);
    guardarArchivo(ARCHIVO_LIBROS, $libros);
}

<?php
/**
 * lib/archivo.php
 * Funciones de persistencia: leen y escriben los datos en archivos de texto (.txt).
 * No se utiliza ningún gestor de base de datos.
 *
 * Formato de los archivos:
 *   - Una línea por registro.
 *   - Los campos se separan con el carácter "|".
 *   - La primera línea es el encabezado con el nombre de cada campo.
 *
 * Ejemplo (data/libros.txt):
 *   codigo|titulo|autor|categoria|anio|ejemplares|disponibles|estado
 *   LIB-001|El llamado de Cthulhu|H. P. Lovecraft|Literatura|1928|3|2|Activo
 */

// Separador de campos
const SEPARADOR = '|';

// Campos que se guardan en cada archivo (en este orden)
const COLUMNAS = [
    'libros.txt' => ['codigo', 'titulo', 'autor', 'categoria', 'anio', 'ejemplares', 'disponibles', 'estado'],
    'prestamos.txt' => ['codigo', 'codigo_libro', 'carne', 'nombre', 'fecha_prestamo',
                        'fecha_devolucion_prevista', 'fecha_devolucion_real', 'estado'],
];

// Campos numéricos, al leerlos se convierten a entero
const CAMPOS_ENTEROS = ['anio', 'ejemplares', 'disponibles'];

// Devuelve la lista de columnas que corresponde a un archivo
function columnasDe(string $ruta): array
{
    return COLUMNAS[basename($ruta)] ?? [];
}

// Limpia un valor antes de guardarlo, quita el separador y los saltos de línea para que no rompan la estructura del archivo
function limpiarValor($valor): string
{
    $valor = (string) ($valor ?? '');
    return str_replace([SEPARADOR, "\r", "\n"], ['/', ' ', ' '], $valor);
}

// Lee un archivo de texto y devuelve sus registros como arreglo de arreglos asociativos.
// Si el archivo no existe, lo crea solo con el encabezado
function leerArchivo(string $ruta): array
{
    if (!file_exists($ruta)) {
        guardarArchivo($ruta, []);
        return [];
    }

    $fp = fopen($ruta, 'r');
    if ($fp === false) {
        return [];
    }

    // Bloqueo compartido para leer de forma segura
    flock($fp, LOCK_SH);
    $lineas = [];
    while (($linea = fgets($fp)) !== false) {
        $linea = rtrim($linea, "\r\n");
        if ($linea !== '') {
            $lineas[] = $linea;
        }
    }
    flock($fp, LOCK_UN);
    fclose($fp);

    if (empty($lineas)) {
        return [];
    }

    // La primera línea es el encabezado
    $encabezado = explode(SEPARADOR, array_shift($lineas));
    $registros = [];

    foreach ($lineas as $linea) {
        $valores = explode(SEPARADOR, $linea);
        // Si a la línea le faltan campos, se completan vacíos
        $valores = array_pad($valores, count($encabezado), '');
        $registro = array_combine($encabezado, array_slice($valores, 0, count($encabezado)));

        foreach ($registro as $campo => $valor) {
            if (in_array($campo, CAMPOS_ENTEROS, true)) {
                $registro[$campo] = (int) $valor;
            } elseif ($valor === '') {
                $registro[$campo] = null;
            }
        }
        $registros[] = $registro;
    }

    return $registros;
}

/**
 * Guarda los registros en el archivo de texto (reescribe el archivo completo).
 * Usa bloqueo exclusivo para evitar que dos escrituras se mezclen.
 */
function guardarArchivo(string $ruta, array $datos): bool
{
    $dir = dirname($ruta);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $columnas = columnasDe($ruta);

    // Encabezado + una línea por registro
    $contenido = implode(SEPARADOR, $columnas) . PHP_EOL;
    foreach ($datos as $registro) {
        $valores = [];
        foreach ($columnas as $col) {
            $valores[] = limpiarValor($registro[$col] ?? '');
        }
        $contenido .= implode(SEPARADOR, $valores) . PHP_EOL;
    }

    $fp = fopen($ruta, 'c');
    if ($fp === false) {
        return false;
    }

    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    fwrite($fp, $contenido);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return true;
}

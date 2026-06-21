<?php
require_once __DIR__ . '/../../../model/cir_conexion.php';

/**
 * Obtiene el id_estado de la tabla estado según la entidad y el código.
 * Convierte los parámetros a mayúsculas automáticamente.
 *
 * @param string $entidad
 * @param string $codigo
 * @return int|null
 */
function id_estado($entidad, $codigo): ?int {
    static $cache = [];

    $entidad = strtoupper(trim($entidad));
    $codigo = strtoupper(trim($codigo));
    $key = $entidad . '|' . $codigo;

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $pdo = conexionCircuitoQuirurgico();
        $stmt = $pdo->prepare("SELECT id_estado FROM estado WHERE entidad = :entidad AND codigo = :codigo LIMIT 1");
        $stmt->execute([
            ':entidad' => $entidad,
            ':codigo' => $codigo
        ]);
        $id = $stmt->fetch(PDO::FETCH_COLUMN);
        $cache[$key] = $id ? (int)$id : null;
        return $cache[$key];
    } catch (Exception $e) {
        $cache[$key] = null;
        return null;
    }
}

/**
 * Mínimo de caracteres para disparar sugerencias (1 si es numérico en todo/dni).
 */
function cq_minimo_caracteres_sugerencia(string $filtro, string $term): int
{
    $term = trim($term);
    if ($term === '') {
        return 2;
    }
    if ($filtro === 'dni' || ($filtro === 'todo' && preg_match('/^\d+$/', $term))) {
        return 1;
    }
    if (in_array($filtro, ['cirugia', 'cirujano', 'especialidad', 'subespecialidad'], true)) {
        return 1;
    }
    return 2;
}

/**
 * Convierte filas de persona en sugerencias para autocompletado del buscador CQ.
 */
function cq_mapear_sugerencias_persona(array $filas, string $filtro, string $term): array
{
    $sugerencias = [];
    $term = trim($term);
    $termDni = preg_replace('/\D/', '', $term);

    foreach ($filas as $fila) {
        $dni = trim((string)($fila['dni'] ?? ''));
        $nombre = trim((string)($fila['nombre'] ?? ''));
        $apellido = trim((string)($fila['apellido'] ?? ''));
        $email = trim((string)($fila['email'] ?? ''));
        $telefono = trim((string)($fila['telefono'] ?? ''));

        $etiqueta = trim($apellido . ', ' . $nombre);
        if ($filtro === 'todo' && $dni !== '') {
            $etiqueta .= ' — DNI ' . $dni;
        }

        if ($filtro === 'dni') {
            $valor = $dni;
            $meta = $etiqueta !== ',' ? trim($apellido . ', ' . $nombre) : '';
        } elseif ($filtro === 'apellido') {
            $valor = $apellido;
            $meta = $dni !== '' ? 'DNI ' . $dni : '';
        } elseif ($filtro === 'nombre') {
            $valor = $nombre;
            $meta = $dni !== '' ? 'DNI ' . $dni : '';
        } elseif ($filtro === 'email') {
            $valor = $email;
            $meta = $etiqueta !== ',' ? trim($apellido . ', ' . $nombre) : '';
        } elseif ($filtro === 'telefono') {
            $valor = $telefono;
            $meta = $etiqueta !== ',' ? trim($apellido . ', ' . $nombre) : '';
        } else {
            if ($termDni !== '' && $dni !== '' && strpos($dni, $termDni) !== false) {
                $valor = $dni;
            } elseif ($apellido !== '' && mb_stripos($apellido, $term) !== false) {
                $valor = $apellido;
            } elseif ($nombre !== '' && mb_stripos($nombre, $term) !== false) {
                $valor = $nombre;
            } elseif ($telefono !== '' && $termDni !== '' && strpos($telefono, $termDni) !== false) {
                $valor = $telefono;
            } else {
                $valor = $nombre !== '' ? $nombre : ($apellido !== '' ? $apellido : $dni);
            }
            $meta = $dni !== '' ? 'DNI ' . $dni : '';
        }

        if ($valor === '') {
            continue;
        }

        $sugerencias[] = [
            'valor'    => $valor,
            'etiqueta' => $etiqueta,
            'dni'      => $dni,
            'nombre'   => $nombre,
            'apellido' => $apellido,
            'meta'     => $meta,
        ];
    }

    return $sugerencias;
}

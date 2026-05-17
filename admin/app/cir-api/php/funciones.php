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
    $entidad = strtoupper(trim($entidad));
    $codigo = strtoupper(trim($codigo));

    try {
        $pdo = conexionCircuitoQuirurgico();
        $stmt = $pdo->prepare("SELECT id_estado FROM estado WHERE entidad = :entidad AND codigo = :codigo LIMIT 1");
        $stmt->execute([
            ':entidad' => $entidad,
            ':codigo' => $codigo
        ]);
        $id = $stmt->fetch(PDO::FETCH_COLUMN);
        return $id ? (int)$id : null;
    } catch (Exception $e) {
        return null;
    }
}

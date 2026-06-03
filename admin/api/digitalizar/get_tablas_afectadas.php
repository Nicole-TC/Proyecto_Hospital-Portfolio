<?php

require_once '../../model/conexion_dig.php';

header('Content-Type: application/json');

try {

    $tablas_afectadas = [];

    $stmt = $bd_dig->prepare("
        SELECT DISTINCT tabla_afectada
        FROM auditoria_logs
        ORDER BY tabla_afectada ASC
    ");

    if ($stmt) {
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tablas_afectadas = array_map(fn($r) => [
            "value" => $r['tabla_afectada'],
            "label" => $r['tabla_afectada']
        ], $rows);
    }

    http_response_code(200);

    echo json_encode([
        "status" => "success",
        "data" => [
            "tablas_afectadas" => $tablas_afectadas
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "status"  => "error",
        "message" => "Error al obtener tablas afectadas: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

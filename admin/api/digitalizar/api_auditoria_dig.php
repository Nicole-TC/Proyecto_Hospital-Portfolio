<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "No autorizado. Por favor inicie sesión."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../../model/conexion_dig.php';

if (!isset($bd_dig)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de conexión a la base de datos."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Use GET."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$tipo = $_GET['tipo'] ?? '';

$accion_map = [
    'subidos' => 'INSERT',
    'eliminados' => 'DELETE',
    'actualizados' => 'UPDATE'
];

if (!isset($accion_map[$tipo])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Parámetro 'tipo' inválido. Use: subidos, eliminados, actualizados"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $accion_detalle = $accion_map[$tipo];
    $params = [':accion' => $accion_detalle];
    $condiciones = [];

    if ($tipo === 'eliminados') {
        $condiciones[] = "(a.detalle = :accion OR (a.detalle = 'UPDATE' AND al.valor_anterior::jsonb @> '{\"estado\":true}'::jsonb AND al.valor_nuevo::jsonb @> '{\"estado\":false}'::jsonb))";
    } else {
        $condiciones[] = "a.detalle = :accion";
    }

    $tabla_afectada = $_GET['tabla_afectada'] ?? '';
    if ($tabla_afectada !== '') {
        $condiciones[] = "al.tabla_afectada = :tabla_afectada";
        $params[':tabla_afectada'] = $tabla_afectada;
    }

    $fecha_desde = $_GET['fecha_desde'] ?? '';
    if ($fecha_desde !== '') {
        $condiciones[] = "al.fecha_accion >= :fecha_desde::date";
        $params[':fecha_desde'] = $fecha_desde;
    }

    $fecha_hasta = $_GET['fecha_hasta'] ?? '';
    if ($fecha_hasta !== '') {
        $condiciones[] = "al.fecha_accion < (:fecha_hasta::date + INTERVAL '1 day')";
        $params[':fecha_hasta'] = $fecha_hasta;
    }

    $where = "WHERE " . implode(" AND ", $condiciones);

    $from = "
        FROM auditoria_logs al
        LEFT JOIN acciones a ON a.id = al.id_accion
        LEFT JOIN admin ad ON ad.id = al.id_usuario
        LEFT JOIN personal p ON p.id = ad.id_personal
        LEFT JOIN documentos d ON d.id = al.id_documento
        LEFT JOIN tipos_documentos td ON td.id = d.id_tipo_documento
    ";

    // Count
    $stmt_count = $bd_dig->prepare("SELECT COUNT(*) $from $where");
    $stmt_count->execute($params);
    $total_registros = (int) $stmt_count->fetchColumn();

    // Data
    $select = "
        SELECT
            al.id,
            al.tabla_afectada,
            CASE
                WHEN al.tabla_afectada = 'tipos_documentos' THEN COALESCE((SELECT nombre FROM tipos_documentos WHERE id = al.id_documento), '-')
                WHEN al.tabla_afectada = 'documentos' THEN COALESCE(td.nombre, '-')
                ELSE '-'
            END AS tipo_documento,
            COALESCE(a.detalle, '-') AS accion,
            COALESCE(
                NULLIF(CONCAT(ad.apellido, ', ', ad.nombre), ', '),
                '-'
            ) AS usuario,
            al.valor_anterior,
            al.valor_nuevo,
            COALESCE(al.ip_origen, '-') AS ip_origen,
            al.fecha_accion
    ";

    $order = "ORDER BY al.fecha_accion DESC";

    $stmt_data = $bd_dig->prepare("$select $from $where $order");
    $stmt_data->execute($params);
    $auditoria = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => [
            "total_registros" => $total_registros,
            "auditoria" => $auditoria
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (PDOException $e) {
    error_log("Error DB en api_auditoria_dig: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error en la base de datos.",
        "error_code" => "DB_ERROR"
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error interno del servidor.",
        "error_code" => "SERVER_ERROR"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

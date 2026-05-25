<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../model/conexion_dig.php';

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

$accion_detalle = $accion_map[$tipo];
$pagina = max(1, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
$limite = max(1, min(100, filter_var($_GET['limite'] ?? 50, FILTER_VALIDATE_INT) ?: 50));

try {
    if ($tipo === 'eliminados') {
        $where = "WHERE (a.detalle = :accion OR (a.detalle = 'UPDATE' AND al.valor_anterior::jsonb @> '{\"estado\":true}'::jsonb AND al.valor_nuevo::jsonb @> '{\"estado\":false}'::jsonb))";
    } else {
        $where = "WHERE a.detalle = :accion";
    }
    $params = [':accion' => $accion_detalle];

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
    $total_paginas = max(1, ceil($total_registros / $limite));
    $pagina = min($pagina, $total_paginas);
    $offset = ($pagina - 1) * $limite;

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

    $stmt_data = $bd_dig->prepare("$select $from $where $order LIMIT :limite OFFSET :offset");
    $stmt_data->execute($params + [':limite' => (int)$limite, ':offset' => (int)$offset]);
    $auditoria = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    foreach ($auditoria as &$row) {
        if (!in_array($row['tabla_afectada'], ['tipos_documentos', 'documentos'])) {
            unset($row['tipo_documento']);
        }
    }
    unset($row);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => [
            "paginacion" => [
                "total_registros" => $total_registros,
                "total_paginas" => $total_paginas,
                "pagina_actual" => $pagina,
                "limite" => $limite
            ],
            "auditoria" => $auditoria
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Error DB en api_auditoria_dig: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error en la base de datos.",
        "error_code" => "DB_ERROR"
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error interno del servidor.",
        "error_code" => "SERVER_ERROR"
    ], JSON_UNESCAPED_UNICODE);
}

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

// Incluir conexión a la base de datos
require_once __DIR__ . '/../../model/conexion_dig.php';

// Validar que la petición sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Utilice GET."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Recibir y sanear parámetros básicos
    $dni_paciente = trim($_GET['dni'] ?? '');
    $id_tipo_documento = filter_var($_GET['id_tipo_documento'] ?? '', FILTER_VALIDATE_INT);
    $fecha_inicio = trim($_GET['fecha_inicio'] ?? '');
    $fecha_fin = trim($_GET['fecha_fin'] ?? '');
    $etiquetas = isset($_GET['etiquetas']) && is_array($_GET['etiquetas']) ? $_GET['etiquetas'] : [];
    $etiquetas = array_filter(array_map('intval', $etiquetas));

    // Consulta SQL base
    $query = "
        SELECT 
            d.id, d.codigo_unico, d.ruta_archivo, d.comentario, d.fecha_subido, 
            d.prioridad, d.estado_documento,
            p.dni, p.apellidoynombre AS paciente,
            td.nombre AS tipo_documento
        FROM documentos d
        JOIN personal p ON d.id_persona = p.id
        LEFT JOIN tipos_documentos td ON d.id_tipo_documento = td.id
        WHERE d.estado = true
    ";

    $params = [];

    // Agregar filtros dinámicos
    if (!empty($dni_paciente)) {
        $query .= " AND p.dni = :dni";
        $params[':dni'] = $dni_paciente;
    }

    if ($id_tipo_documento) {
        $query .= " AND d.id_tipo_documento = :id_tipo_documento";
        $params[':id_tipo_documento'] = $id_tipo_documento;
    }

    // Filtros por rango de fechas
    $tiene_inicio = !empty($fecha_inicio) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio);
    $tiene_fin = !empty($fecha_fin) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin);

    if ($tiene_inicio && $tiene_fin) {
        $query .= " AND d.fecha_subido::DATE BETWEEN :fecha_inicio AND :fecha_fin";
        $params[':fecha_inicio'] = $fecha_inicio;
        $params[':fecha_fin'] = $fecha_fin;
    } elseif ($tiene_inicio) {
        $query .= " AND d.fecha_subido::DATE >= :fecha_inicio";
        $params[':fecha_inicio'] = $fecha_inicio;
    } elseif ($tiene_fin) {
        $query .= " AND d.fecha_subido::DATE <= :fecha_fin";
        $params[':fecha_fin'] = $fecha_fin;
    }

    // Filtro por etiquetas (Tags)
    if (!empty($etiquetas)) {
        $tag_params = [];
        foreach ($etiquetas as $i => $tag_id) {
            $param_name = ":tag_$i";
            $tag_params[] = $param_name;
            $params[$param_name] = $tag_id;
        }
        $in_clause = implode(', ', $tag_params);
        $query .= " AND EXISTS (
            SELECT 1 FROM documento_etiquetas de 
            WHERE de.id_documento = d.id AND de.id_etiqueta IN ($in_clause)
        )";
    }

    // Ordenamiento por defecto (más recientes primero)
    $query .= " ORDER BY d.fecha_subido DESC";

    // Preparar y ejecutar la consulta
    $stmt = $bd_dig->prepare($query);
    $stmt->execute($params);

    // Obtener los resultados
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Respuesta JSON
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Búsqueda completada",
        "data" => [
            "total" => count($documentos),
            "documentos" => $documentos
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error interno del servidor: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

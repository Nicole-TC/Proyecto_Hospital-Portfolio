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
    // Recibir y procesar etiquetas (puede venir como array, string separado por comas o valor único)
    $etiquetas = [];
    if (isset($_GET['etiquetas'])) {
        if (is_array($_GET['etiquetas'])) {
            $etiquetas = $_GET['etiquetas'];
        } else {
            $etiquetas = explode(',', $_GET['etiquetas']);
        }
    } elseif (isset($_GET['tag_id']) && $_GET['tag_id'] !== '') {
        $etiquetas = [$_GET['tag_id']];
    }
    $etiquetas = array_filter(array_map('intval', $etiquetas));

    $pagina = max(1, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
    $limite = max(1, min(100, filter_var($_GET['limite'] ?? 50, FILTER_VALIDATE_INT) ?: 50));

    // Consulta SQL base
    $query = "
        SELECT 
            d.id, d.codigo_unico, d.ruta_archivo, d.comentario, d.fecha_subido, 
            d.prioridad, d.estado_documento,
            p.dni, p.apellidoynombre AS paciente,
            td.nombre AS tipo_documento,
            (
                SELECT STRING_AGG(e.nombre_etiqueta, ', ')
                FROM documento_etiquetas de
                JOIN etiquetas e ON de.id_etiqueta = e.id
                WHERE de.id_documento = d.id
            ) AS etiquetas,
            COUNT(*) OVER() AS total_count
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

    // Paginación
    $offset = ($pagina - 1) * $limite;
    $query .= " LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

    // Preparar y ejecutar la consulta
    $stmt = $bd_dig->prepare($query);
    $stmt->execute($params);

    // Obtener los resultados
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_registros = count($documentos) > 0 ? (int)$documentos[0]['total_count'] : 0;
    $total_paginas = ceil($total_registros / $limite);

    // Remover 'total_count' de los resultados para limpiar la respuesta
    foreach ($documentos as &$doc) {
        unset($doc['total_count']);
    }

    // Respuesta JSON
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Búsqueda completada",
        "data" => [
            "paginacion" => [
                "total_registros" => $total_registros,
                "total_paginas" => $total_paginas,
                "pagina_actual" => $pagina,
                "limite" => $limite
            ],
            "documentos" => $documentos
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Error DB en buscar_documentos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error en la base de datos. Por favor, intente nuevamente.",
        "error_code" => "DB_ERROR"
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($code);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
        "error_code" => "SERVER_ERROR"
    ], JSON_UNESCAPED_UNICODE);
}

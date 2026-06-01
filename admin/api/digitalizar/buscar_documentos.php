<?php
/**
 * CONTRATO DE:
 * - API: Búsqueda de documentos digitalizados.
 *
 * Método: GET
 * Ruta: admin/api/digitalizar/buscar_documentos.php
 * Auth: requiere sesión ($_SESSION['id']), si no → 401.
 *
 * Parámetros GET (actuales):
 * - dni (string): filtra por personal_identificaciones.numero_identificacion (EXISTS, igualdad exacta).
 * - id_tipo_identificacion (int, opcional): filtra por tipo de identificación junto a dni.
 * - id_tipo_documento (int): filtra por d.id_tipo_documento.
 * - fecha_inicio (YYYY-MM-DD): filtra d.fecha_subido (desde / entre).
 * - fecha_fin (YYYY-MM-DD): filtra d.fecha_subido (hasta / entre).
 * - etiquetas (int[]|string): IDs separados por coma o array; EXISTS en documento_etiquetas.
 * - tag_id (int): alias de una sola etiqueta.
 * - pagina (int, default 1): número de página.
 * - limite (int, default 50, max 100): registros por página.
 *
 * SQL base (JOINs):
 * - documentos d
 * - JOIN personal p ON d.id_persona = p.id
 * - LEFT JOIN tipos_documentos td ON d.id_tipo_documento = td.id
 * - LEFT JOIN servicios s ON d.id_servicio = s.id
 * - EXISTS subconsulta sobre personal_identificaciones para filtro dni.
 * - WHERE d.estado = true
 * - Subconsulta: etiquetas agregadas por documento (STRING_AGG).
 *
 * Respuesta 200 (JSON):
 * {
 *   "status": "success",
 *   "message": "Búsqueda completada",
 *   "data": {
 *     "paginacion": { "total_registros", "total_paginas", "pagina_actual", "limite" },
 *     "documentos": [ { "id", "codigo_unico", "ruta_archivo", "comentario", "fecha_subido",
 *       "prioridad", "estado_documento", "dni", "paciente", "tipo_documento", "servicio", "etiquetas" } ]
 *   }
 * }
 *
 * Errores: 400 (sin filtros), 401 (sin sesión), 405 (no GET), 500 (BD).
 */

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
require_once __DIR__ . '/helpers_dig.php';

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
    [
        'dni_paciente'           => $dni_paciente,
        'id_tipo_documento'      => $id_tipo_documento,
        'id_tipo_identificacion' => $id_tipo_identificacion,
        'fecha_inicio'           => $fecha_inicio,
        'fecha_fin'              => $fecha_fin,
        'etiquetas'              => $etiquetas,
        'pagina'                 => $pagina,
        'limite'                 => $limite,
    ] = sanitizar_params_busqueda($_GET);

    // Consulta SQL base
    $query = "
        SELECT 
            d.id, d.codigo_unico, d.ruta_archivo, d.comentario, d.fecha_subido, 
            d.prioridad, d.estado_documento,
            (
                SELECT pi.numero_identificacion
                FROM personal_identificaciones pi
                WHERE pi.id_personal = p.id
                AND pi.id_tipo_identificacion = 1
                LIMIT 1
            ) AS dni,
            p.apellidoynombre AS paciente,
            td.nombre AS tipo_documento,
            s.nombre AS servicio,
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
        LEFT JOIN servicios s ON d.id_servicio = s.id
        WHERE d.estado = true
    ";

    $params = [];

    // Agregar filtros dinámicos
    if (!empty($dni_paciente)) {
        $query .= " AND EXISTS (
            SELECT 1 FROM personal_identificaciones pi
            WHERE pi.id_personal = p.id
            AND pi.numero_identificacion = :dni";

        if ($id_tipo_identificacion) {
            $query .= " AND pi.id_tipo_identificacion = :id_tipo_identificacion";
            $params[':id_tipo_identificacion'] = $id_tipo_identificacion;
        }

        $query .= ")";
        $params[':dni'] = $dni_paciente;
    }

    if ($id_tipo_documento) {
        $query .= " AND d.id_tipo_documento = :id_tipo_documento";
        $params[':id_tipo_documento'] = $id_tipo_documento;
    }

    // Filtros por rango de fechas
    $tiene_dni       = $dni_paciente !== '';
    $tiene_tipo_doc  = $id_tipo_documento !== false && $id_tipo_documento !== null;
    $tiene_inicio    = !empty($fecha_inicio) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio);
    $tiene_fin       = !empty($fecha_fin)    && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin);
    $tiene_etiquetas = !empty($etiquetas);

    if (!$tiene_dni && !$tiene_tipo_doc && !$tiene_inicio && !$tiene_fin && !$tiene_etiquetas) {
        http_response_code(400);
        echo json_encode([
            "status"     => "error",
            "message"    => "Debe ingresar al menos un criterio de búsqueda (dni, tipo de documento, fecha o etiqueta).",
            "error_code" => "NO_FILTER"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

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

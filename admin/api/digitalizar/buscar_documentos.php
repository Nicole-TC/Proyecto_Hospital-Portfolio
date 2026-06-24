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
 * - dni (string): filtra por personal.dni (igualdad exacta) OR personal_identificaciones.numero_identificacion (EXISTS).
 *                Si se envía id_tipo_identificacion, se aplica solo sobre personal_identificaciones.
 * - id_tipo_identificacion (int, opcional): filtra por tipo de identificación junto a dni.
 * - id_tipo_documento (int): filtra por d.id_tipo_documento; compatible con LEFT JOIN tipos_documentos td.
 * - codigo_unico (string, max 100 chars): filtra por d.codigo_unico (búsqueda exacta, código completo, único resultado preciso); ignorado si vacío tras trim.
 * - codigo_unico_like (string, max 100 chars): búsqueda parcial ILIKE sobre d.codigo_unico (búsqueda parcial, útil cuando no sabés el código completo.); ignorado si vacío tras trim.
 * - q (string, max 200 chars): búsqueda textual ILIKE sobre comentario, codigo_unico y apellidoynombre del paciente; ignorado si vacío tras trim.
 * - id_servicio (int): filtra por d.id_servicio.
 * - fecha_inicio (YYYY-MM-DD): filtra d.fecha_subido (desde / entre); responde 400 si formato inválido.
 * - fecha_fin (YYYY-MM-DD): filtra d.fecha_subido (hasta / entre); responde 400 si formato inválido.
 * - etiquetas (int[]|string): IDs separados por coma o array; EXISTS en documento_etiquetas.
 * - tag_id (int): alias de una sola etiqueta.
 * - estado_documento (string): filtra por d.estado_documento; valores permitidos: pendiente, revisado, archivado.
 * - pagina (int, default 1): número de página; responde 400 si es 0 o inválido.
 * - limite (int, default 50, max 100): registros por página; responde 400 si es 0 o mayor a 100.
 *
 * SQL base (JOINs):
 * - documentos d
 * - JOIN personal p ON d.id_persona = p.id
 * - LEFT JOIN tipos_documentos td ON d.id_tipo_documento = td.id
 * - LEFT JOIN servicios s ON d.id_servicio = s.id
 * - EXISTS subconsulta sobre personal_identificaciones para filtro dni.
 * - WHERE d.estado = true
 * - Subconsulta: etiquetas agregadas por documento via STRING_AGG(nombre_etiqueta, ', ') como string separado por comas.
 *
 * Respuesta 200 (JSON):
 * {
 *   "status": "success",
 *   "message": "Búsqueda completada",
 *   "data": {
 *    "paginacion": { "total_registros", "total_paginas", "pagina_actual", "limite" },
 *    "filtros_aplicados": { eco de los parámetros efectivamente usados en la búsqueda },
*     "documentos": [ { "id", "codigo_unico", "ruta_archivo", "comentario", "fecha_subido",
 *                   "prioridad", "estado_documento", "id_persona", "id_servicio", "id_tipo_documento",
 *                   "dni", "paciente", "tipo_documento", "servicio", "etiquetas" } ]
 *   }
 * }
 *
 * Errores: 400 (sin filtros, fecha inválida, pagina/limite inválidos, etiquetas inválidas), 401 (sin sesión), 405 (no GET), 500 (BD).
 *
 * Ejemplos de URL:
 * - Buscar por DNI:           ?dni=48037023
 * - Buscar por texto:         ?q=historia
 * - Buscar por código parcial:?codigo_unico_like=DOC-2026
 * - Buscar por fecha:         ?fecha_inicio=2026-01-01&fecha_fin=2026-06-30
 * - Buscar por servicio:      ?id_servicio=3&fecha_inicio=2026-01-01
 * - Buscar por etiquetas:     ?etiquetas=1,2,3
 * - Paginación:               ?dni=48037023&pagina=2&limite=10
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Incluir conexión a la base de datos
require_once __DIR__ . '/../../model/conexion_dig.php';
require_once __DIR__ . '/helpers_dig.php';

if (!isset($_SESSION['id'])) {
    jsonError(401, 'No autorizado. Por favor inicie sesión.', 'UNAUTHORIZED');
}

// Validar que la petición sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError(405, 'Método no permitido. Utilice GET.', 'METHOD_NOT_ALLOWED');
}

try {
    // Recibir y sanear parámetros básicos
    [
        'dni_paciente'           => $dni_paciente,
        'codigo_unico'           => $codigo_unico,
        'codigo_unico_like'      => $codigo_unico_like,
        'q'                      => $q,
        'id_tipo_documento'      => $id_tipo_documento,
        'id_tipo_identificacion' => $id_tipo_identificacion,
        'id_servicio'            => $id_servicio,
        'estado_documento'       => $estado_documento,
        'fecha_inicio'           => $fecha_inicio,
        'fecha_fin'              => $fecha_fin,
        'etiquetas'              => $etiquetas,
        'pagina'                 => $pagina,
        'limite'                 => $limite,
    ] = sanitizar_params_busqueda($_GET);

    // Validar formato de fechas
    if ($fecha_inicio !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
        jsonError(400, 'El parámetro fecha_inicio no tiene formato válido (YYYY-MM-DD).', 'INVALID_DATE');
    }

    if ($fecha_fin !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
        jsonError(400, 'El parámetro fecha_fin no tiene formato válido (YYYY-MM-DD).', 'INVALID_DATE');
    }

    // Validar pagina y limite
    if ($pagina === false || $pagina < 1) {
        jsonError(400, 'El parámetro pagina debe ser un entero mayor a 0.', 'INVALID_PAGINA');
    }

    if ($limite === false || $limite < 1 || $limite > 100) {
        jsonError(400, 'El parámetro limite debe ser un entero entre 1 y 100.', 'INVALID_LIMITE');
    }

    // Consulta SQL base
    $query = "
        SELECT 
            d.id, d.codigo_unico, d.ruta_archivo, d.comentario, d.fecha_subido, 
            d.prioridad, d.estado_documento, d.id_persona, d.id_servicio, d.id_tipo_documento,
            (
                SELECT pi.numero_identificacion
                FROM personal_identificaciones pi
                WHERE pi.id_personal = p.id
                AND pi.id_tipo_identificacion = 1
                LIMIT 1
            ) AS dni,
            p.apellidoynombre AS paciente,
            p.nombre AS paciente_nombre,
            p.apellido AS paciente_apellido,
            td.nombre AS tipo_documento,
            s.nombre AS servicio,
            (
                SELECT STRING_AGG(de.id_etiqueta::text, ', ')
                FROM documento_etiquetas de
                JOIN etiquetas e ON de.id_etiqueta = e.id
                WHERE de.id_documento = d.id
                AND e.estado = true
            ) AS etiqueta_ids,
            (
                SELECT STRING_AGG(e.nombre_etiqueta, ', ')
                FROM documento_etiquetas de
                JOIN etiquetas e ON de.id_etiqueta = e.id
                WHERE de.id_documento = d.id
                AND e.estado = true
            ) AS etiquetas
        FROM documentos d
        JOIN personal p ON d.id_persona = p.id
        LEFT JOIN tipos_documentos td ON d.id_tipo_documento = td.id
        LEFT JOIN servicios s ON d.id_servicio = s.id
        WHERE d.estado = true
    ";

    $params = [];

if (!empty($dni_paciente)) {
        $query .= " AND (
            p.dni = :dni
            OR EXISTS (
                SELECT 1 FROM personal_identificaciones pi
                WHERE pi.id_personal = p.id
                AND pi.numero_identificacion = :dni2";

        if ($id_tipo_identificacion) {
            $query .= " AND pi.id_tipo_identificacion = :id_tipo_identificacion";
            $params[':id_tipo_identificacion'] = $id_tipo_identificacion;
        }

        $query .= "
            )
        )";
        $params[':dni']  = $dni_paciente;
        $params[':dni2'] = $dni_paciente;
    }

    if ($id_tipo_documento) {
        $query .= " AND d.id_tipo_documento = :id_tipo_documento";
        $params[':id_tipo_documento'] = $id_tipo_documento;
    }

    if ($id_servicio) {
        $query .= " AND d.id_servicio = :id_servicio";
        $params[':id_servicio'] = $id_servicio;
    }

    if ($estado_documento !== null) {
        $query .= " AND d.estado_documento = :estado_documento";
        $params[':estado_documento'] = $estado_documento;
    }

    if ($codigo_unico !== '') {
        $query .= " AND d.codigo_unico = :codigo_unico";
        $params[':codigo_unico'] = $codigo_unico;
    }

    if ($codigo_unico_like !== '') {
        $query .= " AND d.codigo_unico ILIKE :codigo_unico_like";
        $params[':codigo_unico_like'] = '%' . $codigo_unico_like . '%';
    }

    if ($q !== '') {
        $query .= " AND (
            d.comentario ILIKE :q
            OR d.codigo_unico ILIKE :q2
            OR p.apellidoynombre ILIKE :q3
        )";
        $params[':q']  = '%' . $q . '%';
        $params[':q2'] = '%' . $q . '%';
        $params[':q3'] = '%' . $q . '%';
    }

    // Validar que al menos un filtro esté presente
    $tiene_dni               = $dni_paciente !== '';
    $tiene_tipo_doc          = $id_tipo_documento !== false && $id_tipo_documento !== null;
    $tiene_inicio            = !empty($fecha_inicio) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio) && $fecha_inicio <= date('Y-m-d');
    $tiene_fin               = !empty($fecha_fin)    && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)    && $fecha_fin   <= date('Y-m-d');
    $tiene_etiquetas         = !empty($etiquetas);
    $tiene_q                 = $q !== '';
    $tiene_codigo_unico      = $codigo_unico !== '';
    $tiene_codigo_unico_like = $codigo_unico_like !== '';
    $tiene_estado            = $estado_documento !== null;
    $tiene_servicio          = $id_servicio !== false && $id_servicio !== null;

    if (!$tiene_dni && !$tiene_tipo_doc && !$tiene_inicio && !$tiene_fin && !$tiene_etiquetas
        && !$tiene_q && !$tiene_codigo_unico && !$tiene_codigo_unico_like && !$tiene_estado && !$tiene_servicio) {
        jsonError(400, 'Debe ingresar al menos un criterio de búsqueda (dni, tipo de documento, fecha o etiqueta).', 'NO_FILTER');
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
    $query .= " ORDER BY d.fecha_subido DESC, d.id DESC";

    // Paginación
    $offset = ($pagina - 1) * $limite;
    $query .= " LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

    // Contar total de registros sin paginación
    $query_sin_orden = preg_replace('/ORDER BY.*$/s', '', $query);
    $query_sin_orden = preg_replace('/LIMIT \d+ OFFSET \d+/s', '', $query_sin_orden);
    $query_count = "SELECT COUNT(*) FROM (" . $query_sin_orden . ") AS subquery";
    $stmt_count = $bd_dig->prepare($query_count);
    $stmt_count->execute($params);
    $total_registros = (int)$stmt_count->fetchColumn();
    $total_paginas = $total_registros > 0 ? (int)ceil($total_registros / $limite) : 0;

    // Preparar y ejecutar la consulta principal
    $stmt = $bd_dig->prepare($query);
    $stmt->execute($params);
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            "filtros_aplicados" => array_filter([
                "dni"                => $dni_paciente ?: null,
                "codigo_unico"       => $codigo_unico ?: null,
                "codigo_unico_like"  => $codigo_unico_like ?: null,
                "q"                  => $q ?: null,
                "id_tipo_documento"  => $id_tipo_documento ?: null,
                "id_tipo_identificacion" => $id_tipo_identificacion ?: null,
                "id_servicio"        => $id_servicio ?: null,
                "estado_documento"   => $estado_documento,
                "fecha_inicio"       => $fecha_inicio ?: null,
                "fecha_fin"          => $fecha_fin ?: null,
                "etiquetas"          => !empty($etiquetas) ? $etiquetas : null,
            ]),
            "documentos" => $documentos
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Error DB en buscar_documentos: " . $e->getMessage());
    jsonError(500, 'Error en la base de datos. Por favor, intente nuevamente.', 'DB_ERROR');
} catch (Exception $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    jsonError($code, $e->getMessage(), 'SERVER_ERROR');
}

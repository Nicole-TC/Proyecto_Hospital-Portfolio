<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../model/conexion_dig.php';
require_once __DIR__ . '/helpers_dig.php';

if (!isset($_SESSION['id'])) {
    jsonError(401, 'No autorizado. Por favor inicie sesión.', 'UNAUTHORIZED');
}

if ($_SESSION['admin'] != 1) {
    jsonError(403, 'Acceso denegado. Solo administradores.', 'FORBIDDEN');
}

$method = $_SERVER['REQUEST_METHOD'];

// === LISTAR documentos desactivados (GET) ===
if ($method === 'GET') {
    try {
        [
            'dni_paciente'           => $dni_paciente,
            'id_tipo_documento'      => $id_tipo_documento,
            'id_servicio'            => $id_servicio,
            'fecha_inicio'           => $fecha_inicio,
            'fecha_fin'              => $fecha_fin,
            'etiquetas'              => $etiquetas,
            'pagina'                 => $pagina,
            'limite'                 => $limite,
        ] = sanitizar_params_busqueda($_GET);

        if ($fecha_inicio !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
            jsonError(400, 'El parámetro fecha_inicio no tiene formato válido (YYYY-MM-DD).', 'INVALID_DATE');
        }
        if ($fecha_fin !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
            jsonError(400, 'El parámetro fecha_fin no tiene formato válido (YYYY-MM-DD).', 'INVALID_DATE');
        }
        $hoy = date('Y-m-d');
        if ($fecha_inicio !== '' && $fecha_inicio > $hoy) {
            jsonError(400, 'La fecha "desde" no puede ser futura.', 'INVALID_DATE');
        }
        if ($fecha_fin !== '' && $fecha_fin > $hoy) {
            jsonError(400, 'La fecha "hasta" no puede ser futura.', 'INVALID_DATE');
        }
        if ($fecha_inicio !== '' && $fecha_fin !== '' && $fecha_inicio > $fecha_fin) {
            jsonError(400, 'La fecha "desde" no puede ser posterior a la fecha "hasta".', 'INVALID_DATE_RANGE');
        }

        $query_base = "
            FROM documentos d
            JOIN personal p ON d.id_persona = p.id
            LEFT JOIN tipos_documentos td ON d.id_tipo_documento = td.id
            LEFT JOIN servicios s ON d.id_servicio = s.id
            WHERE d.estado = false
        ";
        $params = [];

        if (!empty($dni_paciente)) {
            $query_base .= " AND (
                p.dni = :dni
                OR EXISTS (
                    SELECT 1 FROM personal_identificaciones pi
                    WHERE pi.id_personal = p.id
                    AND pi.numero_identificacion = :dni2
                )
            )";
            $params[':dni']  = $dni_paciente;
            $params[':dni2'] = $dni_paciente;
        }

        if ($id_tipo_documento) {
            $query_base .= " AND d.id_tipo_documento = :id_tipo_documento";
            $params[':id_tipo_documento'] = $id_tipo_documento;
        }

        if ($id_servicio) {
            $query_base .= " AND d.id_servicio = :id_servicio";
            $params[':id_servicio'] = $id_servicio;
        }

        if ($fecha_inicio !== '' && $fecha_fin !== '') {
            $query_base .= " AND d.fecha_subido::DATE BETWEEN :fecha_inicio AND :fecha_fin";
            $params[':fecha_inicio'] = $fecha_inicio;
            $params[':fecha_fin'] = $fecha_fin;
        } elseif ($fecha_inicio !== '') {
            $query_base .= " AND d.fecha_subido::DATE >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        } elseif ($fecha_fin !== '') {
            $query_base .= " AND d.fecha_subido::DATE <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }

        if (!empty($etiquetas)) {
            $tag_params = [];
            foreach ($etiquetas as $i => $tag_id) {
                $param_name = ":tag_$i";
                $tag_params[] = $param_name;
                $params[$param_name] = $tag_id;
            }
            $in_clause = implode(', ', $tag_params);
            $query_base .= " AND EXISTS (
                SELECT 1 FROM documento_etiquetas de
                WHERE de.id_documento = d.id AND de.id_etiqueta IN ($in_clause)
            )";
        }

        $tiene_dni          = !empty($dni_paciente);
        $tiene_tipo_doc     = !empty($id_tipo_documento);
        $tiene_servicio     = !empty($id_servicio);
        $tiene_inicio       = !empty($fecha_inicio);
        $tiene_fin          = !empty($fecha_fin);
        $tiene_etiquetas    = !empty($etiquetas);

        if (!$tiene_dni && !$tiene_tipo_doc && !$tiene_servicio && !$tiene_inicio && !$tiene_fin && !$tiene_etiquetas) {
            jsonError(400, 'Debe ingresar al menos un criterio de búsqueda (DNI, servicio, fecha o etiqueta).', 'NO_FILTER');
        }

        $countStmt = $bd_dig->prepare("SELECT COUNT(*) " . $query_base);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($pagina - 1) * $limite;

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
                td.nombre AS tipo_documento,
                s.nombre AS servicio,
                (
                    SELECT STRING_AGG(e.nombre_etiqueta, ', ')
                    FROM documento_etiquetas de
                    JOIN etiquetas e ON de.id_etiqueta = e.id
                    WHERE de.id_documento = d.id
                    AND e.estado = true
                ) AS etiquetas
            " . $query_base . "
            ORDER BY d.fecha_subido DESC, d.id DESC
            LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

        $stmt = $bd_dig->prepare($query);
        $stmt->execute($params);
        $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data' => [
                'documentos' => $documentos,
                'total' => $total,
                'pagina' => $pagina,
                'limite' => $limite,
                'total_paginas' => $total > 0 ? (int)ceil($total / $limite) : 0,
                'filtros_aplicados' => array_filter([
                    'dni'              => $dni_paciente ?: null,
                    'id_tipo_documento' => $id_tipo_documento ?: null,
                    'id_servicio'      => $id_servicio ?: null,
                    'fecha_inicio'     => $fecha_inicio ?: null,
                    'fecha_fin'        => $fecha_fin ?: null,
                    'etiquetas'        => !empty($etiquetas) ? $etiquetas : null,
                ])
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (PDOException $e) {
        error_log("Error DB en papelera GET: " . $e->getMessage());
        jsonError(500, 'Error en la base de datos.', 'DB_ERROR');
    }
}

// === ACCIONES: restore / hard_delete (POST) ===
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
    $action = $input['action'] ?? '';

    if (!$id) {
        jsonError(400, 'ID no proporcionado o inválido.', 'INVALID_ID');
    }

    if (!in_array($action, ['restore', 'hard_delete'], true)) {
        jsonError(400, 'Acción no válida. Use restore o hard_delete.', 'INVALID_ACTION');
    }

    try {
        // Verificar que el documento existe y está desactivado
        $check = $bd_dig->prepare("SELECT id, estado, ruta_archivo FROM documentos WHERE id = ?");
        $check->execute([$id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            jsonError(404, 'El documento no existe.', 'NOT_FOUND');
        }

        if ($action === 'restore') {
            if ($row['estado'] != 0 && $row['estado'] !== 'f') {
                jsonError(400, 'El documento ya está activo.', 'ALREADY_ACTIVE');
            }

            $stmt = $bd_dig->prepare("UPDATE documentos SET estado = true WHERE id = ?");
            $stmt->execute([$id]);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Documento restaurado correctamente.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($action === 'hard_delete') {
            $fisico = __DIR__ . '/../../' . $row['ruta_archivo'];

            $bd_dig->beginTransaction();

            $stmt1 = $bd_dig->prepare("DELETE FROM documento_etiquetas WHERE id_documento = ?");
            $stmt1->execute([$id]);

            $stmt2 = $bd_dig->prepare("DELETE FROM documentos WHERE id = ?");
            $stmt2->execute([$id]);

            $bd_dig->commit();

            // Delete physical file after DB success
            if ($row['ruta_archivo'] && file_exists($fisico)) {
                unlink($fisico);
            }

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Documento eliminado permanentemente.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

    } catch (PDOException $e) {
        if (isset($bd_dig) && $bd_dig->inTransaction()) {
            $bd_dig->rollBack();
        }
        error_log("Error DB en papelera POST: " . $e->getMessage());
        jsonError(500, 'Error en la base de datos.', 'DB_ERROR');
    }
}

jsonError(405, 'Método no permitido.', 'METHOD_NOT_ALLOWED');

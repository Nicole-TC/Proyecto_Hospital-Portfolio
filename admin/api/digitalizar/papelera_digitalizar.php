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
        $page = max(1, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $limit = max(1, min(100, filter_var($_GET['limite'] ?? 50, FILTER_VALIDATE_INT) ?: 50));
        $offset = ($page - 1) * $limit;

        // Count total
        $countStmt = $bd_dig->prepare("SELECT COUNT(*) FROM documentos WHERE estado = false");
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

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
            FROM documentos d
            JOIN personal p ON d.id_persona = p.id
            LEFT JOIN tipos_documentos td ON d.id_tipo_documento = td.id
            LEFT JOIN servicios s ON d.id_servicio = s.id
            WHERE d.estado = false
            ORDER BY d.fecha_subido DESC, d.id DESC
            LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $bd_dig->prepare($query);
        $stmt->execute();
        $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data' => [
                'documentos' => $documentos,
                'total' => $total,
                'pagina' => $page,
                'limite' => $limit,
                'total_paginas' => $total > 0 ? (int)ceil($total / $limit) : 0
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

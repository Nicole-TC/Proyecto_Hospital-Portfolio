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

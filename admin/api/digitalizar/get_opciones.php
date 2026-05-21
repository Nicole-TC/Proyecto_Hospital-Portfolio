<?php

require_once '../../model/conexion_dig.php';

header('Content-Type: application/json');

try {

    // Obtener tipos de documentos
    $tipos_documentos = [];

    $stmtTipos = $bd_dig->prepare("
        SELECT id, nombre
        FROM tipos_documentos
        WHERE estado = true
        ORDER BY nombre ASC
    ");

    if ($stmtTipos) {

        $stmtTipos->execute();

        $tipos_documentos = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener etiquetas
    $etiquetas = [];

    $stmtEtiquetas = $bd_dig->prepare("
        SELECT id, nombre_etiqueta AS name
        FROM etiquetas
        WHERE estado = true
        ORDER BY nombre_etiqueta ASC
    ");

    if ($stmtEtiquetas) {

        $stmtEtiquetas->execute();

        $etiquetas = $stmtEtiquetas->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener servicios (sectores)
    $servicios = [];
    $stmtServicios = $bd_dig->prepare("
        SELECT id, detalle AS nombre
        FROM servicios
        ORDER BY detalle ASC
    ");
    
    if ($stmtServicios) {
        $stmtServicios->execute();
        $servicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener tipos de identificacion (personas)
    $tipos_identificacion = [];
    try {
        $stmtTiposIden = $bd_dig->prepare("
            SELECT id, nombre
            FROM tipos_identificacion
            WHERE estado = true
            ORDER BY id ASC
        ");
        
        if ($stmtTiposIden) {
            $stmtTiposIden->execute();
            $tipos_identificacion = $stmtTiposIden->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // En caso de error de permisos, devolver array vacío para no romper el resto
        error_log("Error al obtener tipos de identificacion: " . $e->getMessage());
    }

    http_response_code(200);

    echo json_encode([
        "status" => "success",
        "data" => [
            "tipos_documentos" => $tipos_documentos,
            "etiquetas" => $etiquetas,
            "servicios" => $servicios,
            "tipos_identificacion" => $tipos_identificacion
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener opciones: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
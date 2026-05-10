<?php

require_once '../../model/conexion.php';

header('Content-Type: application/json');

try {

    // Obtener tipos de documentos
    $tipos_documentos = [];

    $stmtTipos = $bd->prepare("
        SELECT id, nombre
        FROM tipos_documentos
        WHERE estado = 1
        ORDER BY nombre ASC
    ");

    if ($stmtTipos) {

        $stmtTipos->execute();

        $tipos_documentos = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener etiquetas
    $etiquetas = [];

    $stmtEtiquetas = $bd->prepare("
        SELECT id, nombre_etiqueta AS name
        FROM etiquetas
        WHERE estado = 1
        ORDER BY nombre_etiqueta ASC
    ");

    if ($stmtEtiquetas) {

        $stmtEtiquetas->execute();

        $etiquetas = $stmtEtiquetas->fetchAll(PDO::FETCH_ASSOC);
    }

    http_response_code(200);

    echo json_encode([
        "status" => "success",
        "data" => [
            "tipos_documentos" => $tipos_documentos,
            "etiquetas" => $etiquetas
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener opciones"
    ], JSON_UNESCAPED_UNICODE);
}
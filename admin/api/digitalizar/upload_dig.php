<?php
header('Content-Type: application/json; charset=utf-8');

// validar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// si es POST
http_response_code(200);

// validar que exista archivo
if (!isset($_FILES['archivo'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "No se recibió ningún archivo"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// detectar archivo
if ($_FILES['archivo']['error'] !== 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Error al subir el archivo"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// éxito (archivo detectado y sin errores)
echo json_encode([
    "status" => "success",
    "message" => "Archivo recibido correctamente"
], JSON_UNESCAPED_UNICODE);

?>
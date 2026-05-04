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
echo json_encode([
    "status" => "ok",
    "message" => "Solicitud POST recibida correctamente"
], JSON_UNESCAPED_UNICODE);

?>
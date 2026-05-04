<?php

// validar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// si es POST
echo json_encode([
    "status" => "ok",
    "message" => "Solicitud POST recibida correctamente"
]);

?>
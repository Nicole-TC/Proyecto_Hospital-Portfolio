<?php

require_once '../../model/conexion_dig.php';

header('Content-Type: application/json');

if (!isset($_GET['dni']) || empty(trim($_GET['dni']))) {

    http_response_code(400);

    echo json_encode([
        "status" => "error",
        "message" => "DNI no proporcionado"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$dni = trim($_GET['dni']);

try {

    // Buscar en la tabla personal (PostgreSQL)
    $stmt = $bd_dig->prepare("
        SELECT id, nombre, apellido, correo AS email, telefono
        FROM personal
        WHERE dni = :dni
        LIMIT 1
    ");

    $stmt->execute([
        ':dni' => $dni
    ]);

    $persona = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($persona) {

        http_response_code(200);

        echo json_encode([
            "status" => "success",
            "data" => [
                "id" => $persona['id'],
                "nombre" => $persona['nombre'],
                "apellido" => $persona['apellido'],
                "email" => $persona['email'],
                "telefono" => $persona['telefono']
            ]
        ], JSON_UNESCAPED_UNICODE);

    } else {

        http_response_code(404);

        echo json_encode([
            "status" => "not_found",
            "message" => "No se encontró a la persona en la base de datos"
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error de base de datos"
    ], JSON_UNESCAPED_UNICODE);
}
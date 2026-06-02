<?php
session_start();
require_once '../../model/conexion_dig.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "No autorizado. Por favor inicie sesión."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

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

    $tipo = filter_var($_GET['tipo'] ?? null, FILTER_VALIDATE_INT);

    // Buscar en la tabla personal haciendo join con la nueva tabla
    $query = "
        SELECT p.id, p.nombre, p.apellido, p.correo AS email, p.telefono
        FROM personal p
        LEFT JOIN personal_identificaciones pi ON p.id = pi.id_personal
        WHERE (pi.numero_identificacion = :dni";

    $params = [':dni' => $dni];

    if ($tipo) {
        $query .= " AND pi.id_tipo_identificacion = :tipo";
        $params[':tipo'] = $tipo;
    }

    $query .= ")";

    // Por compatibilidad hacia atrás: buscar también en p.dni si es un número válido (integer de Postgres)
    if (ctype_digit($dni) && $dni <= 2147483647) {
        $query .= " OR p.dni = :dni_old";
        $params[':dni_old'] = $dni;
    }

    $query .= " LIMIT 1";

    $stmt = $bd_dig->prepare($query);
    $stmt->execute($params);

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
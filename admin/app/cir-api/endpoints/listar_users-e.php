<?php
header('Content-Type: application/json');
session_start();

// Validar que el usuario esté logueado en el sistema admin para evitar accesos externos por URL
if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

// Validar que el método HTTP sea estrictamente GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Código HTTP 405: Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Método no permitido. Este endpoint solo acepta peticiones GET."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_users-b.php';

try {
    // Obtenemos la lista de personas desde la base de datos PostgreSQL
    $usuarios = ConsultasUsers::listarPersonas();
    
    // Devolvemos la respuesta al front-end
    echo json_encode([
        "status" => "success", 
        "usuarios" => $usuarios
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "message" => "Error al obtener usuarios: " . $e->getMessage()
    ]);
}
?>

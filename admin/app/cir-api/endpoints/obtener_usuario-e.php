<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado."]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_users-b.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    echo json_encode(["status" => "error", "message" => "ID de persona inválido."]);
    exit;
}

try {
    $persona = ConsultasUsers::obtenerPersonaCompleta($id);
    if (!$persona) {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado."]);
        exit;
    }
    echo json_encode(["status" => "success", "persona" => $persona]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>

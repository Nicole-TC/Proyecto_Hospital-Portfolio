<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_users-b.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$id_persona = filter_var($data['id_persona'] ?? '', FILTER_VALIDATE_INT);
if (!$id_persona || $id_persona <= 0) {
    echo json_encode(["status" => "error", "message" => "ID de persona inválido."]);
    exit;
}

try {
    $ok = ConsultasUsers::desactivarPersona($id_persona);
    if ($ok) {
        echo json_encode(["status" => "success", "message" => "El usuario fue dado de baja correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No se pudo dar de baja al usuario."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>

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

$dni = trim($_GET['dni'] ?? '');

if (empty($dni)) {
    echo json_encode(["status" => "error", "message" => "DNI no proporcionado."]);
    exit;
}

if (!preg_match('/^\d{7,10}$/', $dni)) {
    echo json_encode(["status" => "error", "message" => "Formato de DNI inválido."]);
    exit;
}

try {
    $existe = ConsultasUsers::existeDNI($dni);
    echo json_encode(["status" => "success", "existe" => $existe]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>

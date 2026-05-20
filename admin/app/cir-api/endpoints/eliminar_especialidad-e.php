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

require_once __DIR__ . '/../php/consultas_especialidades-b.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No se recibieron datos."]);
    exit;
}

try {
    $id = isset($data['id']) ? (int)$data['id'] : 0;

    if (!$id) {
        echo json_encode(["status" => "error", "message" => "ID inválido."]);
        exit;
    }

    // Bloquear si tiene subespecialidades hijas
    if (ConsultasEspecialidades::tieneHijos($id)) {
        echo json_encode([
            "status"  => "error",
            "message" => "No se puede eliminar: esta especialidad tiene subespecialidades asociadas. Elimínelas primero."
        ]);
        exit;
    }

    // Bloquear si está siendo usada por profesionales o matrículas
    if (ConsultasEspecialidades::estaEnUso($id)) {
        echo json_encode([
            "status"  => "error",
            "message" => "No se puede eliminar: la especialidad está asignada a uno o más profesionales del sistema."
        ]);
        exit;
    }

    $ok = ConsultasEspecialidades::eliminar($id);

    if ($ok) {
        echo json_encode(["status" => "success", "message" => "Registro eliminado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No se pudo eliminar el registro."]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>

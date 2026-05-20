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
    $nombre  = trim($data['nombre'] ?? '');
    $idPadre = isset($data['id_especialidad_padre']) ? $data['id_especialidad_padre'] : null;
    // Normalizar: null si viene como 0, vacío o explícitamente null
    if ($idPadre !== null && (int)$idPadre === 0) $idPadre = null;
    if ($idPadre !== null) $idPadre = (int)$idPadre;

    // Validaciones
    if (empty($nombre)) {
        echo json_encode(["status" => "error", "message" => "El nombre es obligatorio."]);
        exit;
    }
    if (mb_strlen($nombre, 'UTF-8') < 2) {
        echo json_encode(["status" => "error", "message" => "El nombre debe tener al menos 2 caracteres."]);
        exit;
    }

    // Verificar duplicado
    if (ConsultasEspecialidades::existeNombre($nombre, $idPadre)) {
        $msg = $idPadre
            ? "Ya existe una subespecialidad con ese nombre en esa especialidad."
            : "Ya existe una especialidad con ese nombre.";
        echo json_encode(["status" => "error", "message" => $msg]);
        exit;
    }

    $id = ConsultasEspecialidades::crear($nombre, $idPadre);

    if ($id) {
        $tipo = $idPadre ? 'Subespecialidad' : 'Especialidad';
        echo json_encode([
            "status"  => "success",
            "message" => "$tipo creada correctamente.",
            "id"      => $id
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No se pudo guardar el registro en la base de datos."]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>

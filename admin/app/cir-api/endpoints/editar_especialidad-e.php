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
    $id      = isset($data['id']) ? (int)$data['id'] : 0;
    $nombre  = trim($data['nombre'] ?? '');
    $idPadre = isset($data['id_especialidad_padre']) ? $data['id_especialidad_padre'] : null;
    if ($idPadre !== null && (int)$idPadre === 0) $idPadre = null;
    if ($idPadre !== null) $idPadre = (int)$idPadre;

    if (!$id) {
        echo json_encode(["status" => "error", "message" => "ID inválido."]);
        exit;
    }
    if (empty($nombre)) {
        echo json_encode(["status" => "error", "message" => "El nombre es obligatorio."]);
        exit;
    }
    if (mb_strlen($nombre, 'UTF-8') < 2) {
        echo json_encode(["status" => "error", "message" => "El nombre debe tener al menos 2 caracteres."]);
        exit;
    }

    // Verificar duplicado (excluyendo el mismo registro)
    if (ConsultasEspecialidades::existeNombre($nombre, $idPadre, $id)) {
        $msg = $idPadre
            ? "Ya existe una subespecialidad con ese nombre en esa especialidad."
            : "Ya existe una especialidad con ese nombre.";
        echo json_encode(["status" => "error", "message" => $msg]);
        exit;
    }

    $ok = ConsultasEspecialidades::actualizar($id, $nombre, $idPadre);

    if ($ok) {
        echo json_encode(["status" => "success", "message" => "Registro actualizado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No se pudo actualizar el registro."]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>

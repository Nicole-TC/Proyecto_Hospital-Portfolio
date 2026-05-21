<?php
header('Content-Type: application/json');
session_start();

// 1. Validar que el usuario esté logueado en el sistema admin
if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_especialidades-b.php';

// Obtener la acción solicitada (por GET o POST)
$accion = $_GET['accion'] ?? null;

$data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true) ?? [];
    if (isset($data['accion'])) {
        $accion = $data['accion'];
    }
}

if (!$accion) {
    echo json_encode(["status" => "error", "message" => "Acción no especificada."]);
    exit;
}

try {
    switch ($accion) {
        case 'stats':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido para stats."]);
                exit;
            }
            $stats = ConsultasEspecialidades::obtenerEstadisticas();
            echo json_encode(["status" => "success", "stats" => $stats]);
            break;

        case 'listar':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido para listar."]);
                exit;
            }
            $tipo   = $_GET['tipo'] ?? 'especialidad';
            $nombre = trim($_GET['nombre'] ?? '');
            $pagina = (int)($_GET['pagina'] ?? 1);
            $limite = (int)($_GET['limite'] ?? 10);

            $resultado = ConsultasEspecialidades::listar($tipo, $nombre, $pagina, $limite);
            echo json_encode([
                "status"    => "success",
                "registros" => $resultado['registros'],
                "total"     => $resultado['total'],
                "pagina"    => $pagina,
                "limite"    => $limite
            ]);
            break;

        case 'crear':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido para crear."]);
                exit;
            }
            $nombre  = trim($data['nombre'] ?? '');
            $idPadre = isset($data['id_especialidad_padre']) ? $data['id_especialidad_padre'] : null;
            if ($idPadre !== null && (int)$idPadre === 0) $idPadre = null;
            if ($idPadre !== null) $idPadre = (int)$idPadre;

            if (empty($nombre)) {
                echo json_encode(["status" => "error", "message" => "El nombre es obligatorio."]);
                exit;
            }
            if (mb_strlen($nombre, 'UTF-8') < 2) {
                echo json_encode(["status" => "error", "message" => "El nombre debe tener al menos 2 caracteres."]);
                exit;
            }

            if (ConsultasEspecialidades::existeNombre($nombre, $idPadre)) {
                $msg = $idPadre
                    ? "Ya existe una subespecialidad con ese nombre en esa especialidad."
                    : "Ya existe una especialidad con ese nombre.";
                echo json_encode(["status" => "error", "message" => $msg]);
                exit;
            }

            $id = ConsultasEspecialidades::crear($nombre, $idPadre);
            if ($id) {
                $tipoReg = $idPadre ? 'Subespecialidad' : 'Especialidad';
                echo json_encode([
                    "status"  => "success",
                    "message" => "$tipoReg creada correctamente.",
                    "id"      => $id
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo guardar el registro."]);
            }
            break;

        case 'editar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido para editar."]);
                exit;
            }
            $id      = (int)($data['id'] ?? 0);
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
            break;

        case 'eliminar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido para eliminar."]);
                exit;
            }
            $id = (int)($data['id'] ?? 0);

            if (!$id) {
                echo json_encode(["status" => "error", "message" => "ID inválido."]);
                exit;
            }

            if (ConsultasEspecialidades::tieneHijos($id)) {
                echo json_encode([
                    "status"  => "error",
                    "message" => "No se puede eliminar: esta especialidad tiene subespecialidades asociadas. Elimínelas primero."
                ]);
                exit;
            }

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
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Acción no válida."]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
?>

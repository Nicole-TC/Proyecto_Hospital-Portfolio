<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

require_once __DIR__ . '/../php/seguimiento_postas-b.php';

$accion = $_GET['accion'] ?? null;
$data   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json   = file_get_contents('php://input');
    $data   = json_decode($json, true) ?? [];
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
        
        case 'listar_seguimiento':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }
            $fecha_desde = trim($_GET['fecha_desde'] ?? '');
            $fecha_hasta = trim($_GET['fecha_hasta'] ?? '');
            $estado_apto = trim($_GET['estado_apto'] ?? '');
            $buscar      = trim($_GET['buscar'] ?? '');

            $resultado = ConsultasSeguimiento::listarSeguimiento($fecha_desde, $fecha_hasta, $estado_apto, $buscar);
            echo json_encode(["status" => "success"] + $resultado);
            break;

        case 'registrar_apto':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            
            $id_orden       = (int)($data['id_orden'] ?? 0);
            $id_profesional = (int)($data['id_profesional'] ?? 0); // Este debe venir del frontend o ser el usuario logueado si es un profesional
            $observaciones  = trim($data['observaciones'] ?? '');
            $codigo_estado  = mb_strtoupper(trim($data['codigo_estado'] ?? ''), 'UTF-8'); // Ej: 'OK' o 'RECH'

            if (!$id_orden || !$id_profesional) {
                echo json_encode(["status" => "error", "message" => "Faltan parámetros obligatorios (id_orden o id_profesional)."]);
                exit;
            }

            if (!in_array($codigo_estado, ['OK', 'RECH'])) {
                echo json_encode(["status" => "error", "message" => "Código de estado inválido. Debe ser OK o RECH."]);
                exit;
            }

            $exito = ConsultasSeguimiento::registrarAptoFisico($id_orden, $id_profesional, $observaciones, $codigo_estado);
            
            if ($exito) {
                echo json_encode(["status" => "success", "message" => "Apto Físico registrado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo registrar el Apto Físico."]);
            }
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Acción no válida."]);
            break;
    }

} catch (Exception $e) {
    error_log("Error en seguimiento_postas-e.php: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error interno del servidor: " . $e->getMessage()]);
}
?>

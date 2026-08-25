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
            // ADM = fecha de admisión (om.fecha_orden), TUR = fecha del turno prequirúrgico.
            $tipo_fecha  = trim($_GET['tipo_fecha'] ?? 'ADM');

            $resultado = ConsultasSeguimiento::listarSeguimiento($fecha_desde, $fecha_hasta, $estado_apto, $buscar, $tipo_fecha);
            echo json_encode(["status" => "success"] + $resultado);
            break;

        case 'registrar_apto':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            
            $id_orden       = (int)($data['id_orden'] ?? 0);
            $id_profesional = (int)($data['id_profesional'] ?? 0); // Este debe venir del frontend o ser el usuario logueado si es un profesional
            $observaciones  = trim($data['observaciones'] ?? '');
            $codigo_estado  = mb_strtoupper(trim($data['codigo_estado'] ?? ''), 'UTF-8'); // Ej: 'OK' o 'RECH'
            $codigo_prioridad = isset($data['prioridad']) ? mb_strtoupper(trim($data['prioridad']), 'UTF-8') : null;
            if ($codigo_prioridad === '') {
                $codigo_prioridad = null;
            }

            if (!$id_orden || !$id_profesional) {
                echo json_encode(["status" => "error", "message" => "Faltan parámetros obligatorios (id_orden o id_profesional)."]);
                exit;
            }

            if (!in_array($codigo_estado, ['OK', 'RECH'])) {
                echo json_encode(["status" => "error", "message" => "Código de estado inválido. Debe ser OK o RECH."]);
                exit;
            }

            try {
                $exito = ConsultasSeguimiento::registrarAptoFisico($id_orden, $id_profesional, $observaciones, $codigo_estado, $codigo_prioridad);
            } catch (AptoDuplicadoException $e) {
                echo json_encode([
                    "status"          => "error",
                    "message"         => $e->getMessage(),
                    "ya_registrado"   => true,
                    "fecha_registro"  => $e->fechaExistente,
                    "estado_registro" => $e->estadoExistente
                ]);
                exit;
            }
            
            if ($exito) {
                echo json_encode(["status" => "success", "message" => "Apto Físico registrado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo registrar el Apto Físico."]);
            }
            break;

        case 'marcar_estudio':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

            // SOLO EL ADMIN PUEDE TILDAR LOS ESTUDIOS (0/4 -> 4/4).
            if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
                http_response_code(403);
                echo json_encode(["status" => "error", "message" => "Acceso restringido a administradores."]);
                exit;
            }

            $id_orden      = (int)($data['id_orden'] ?? 0);
            $id_estudio    = (int)($data['id_estudio'] ?? 0);
            $codigo_estado = mb_strtoupper(trim($data['codigo_estado'] ?? ''), 'UTF-8');

            if (!$id_orden || !$id_estudio) {
                echo json_encode(["status" => "error", "message" => "Faltan parámetros obligatorios (id_orden o id_estudio)."]);
                exit;
            }

            if (!in_array($codigo_estado, ['PEND', 'REAL', 'OBS'])) {
                echo json_encode(["status" => "error", "message" => "Código de estado inválido. Debe ser PEND, REAL u OBS."]);
                exit;
            }

            $exito = ConsultasSeguimiento::marcarEstudio($id_orden, $id_estudio, $codigo_estado);

            if ($exito) {
                echo json_encode(["status" => "success", "message" => "Estudio actualizado."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo actualizar el estudio."]);
            }
            break;

        case 'listar_estudios_disponibles':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }
            $id_orden = (int)($_GET['id_orden'] ?? 0);
            if (!$id_orden) {
                echo json_encode(["status" => "error", "message" => "Falta id_orden."]);
                exit;
            }
            $estudios = ConsultasSeguimiento::obtenerEstudiosDisponibles($id_orden);
            echo json_encode(["status" => "success", "estudios" => $estudios]);
            break;

        case 'agregar_estudio_adicional':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
            
            // SOLO EL ADMIN PUEDE AGREGAR ESTUDIOS ADICIONALES
            if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
                http_response_code(403);
                echo json_encode(["status" => "error", "message" => "Acceso restringido a administradores."]);
                exit;
            }

            $id_orden = (int)($data['id_orden'] ?? 0);
            $id_estudio = (int)($data['id_estudio'] ?? 0);

            if (!$id_orden || !$id_estudio) {
                echo json_encode(["status" => "error", "message" => "Faltan parámetros obligatorios."]);
                exit;
            }

            $exito = ConsultasSeguimiento::agregarEstudioAdicional($id_orden, $id_estudio);
            if ($exito) {
                echo json_encode(["status" => "success", "message" => "Estudio adicional agregado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo agregar el estudio."]);
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

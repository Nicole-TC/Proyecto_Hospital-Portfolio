<?php
header('Content-Type: application/json');
session_start();

// Validar que el usuario esté logueado
if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_cirugias-b.php';

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
        case 'listar_estudios':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            $estudios = ConsultasCirugias::listarEstudios();
            echo json_encode([
                "status" => "success",
                "estudios" => $estudios
            ]);
            break;

        case 'listar_cirugias_sin_combo':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            $cirugias = ConsultasCirugias::listarCirugiasSinComboSelect();
            echo json_encode([
                "status" => "success",
                "cirugias" => $cirugias
            ]);
            break;

        case 'validar_nombre_cirugia':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            $nombre = trim($_GET['nombre'] ?? '');
            if (empty($nombre)) {
                echo json_encode(["status" => "error", "message" => "Nombre vacío."]);
                exit;
            }
            $existe = ConsultasCirugias::existeNombreCirugia($nombre);
            if ($existe) {
                echo json_encode(["status" => "error", "message" => "Ya existe una cirugía con ese nombre."]);
            } else {
                echo json_encode(["status" => "success", "message" => "Nombre disponible."]);
            }
            break;

        case 'crear':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            
            $id_cirugia = isset($data['id_cirugia']) ? (int)$data['id_cirugia'] : 0;
            $nombre_cirugia = trim($data['nombre_cirugia'] ?? '');
            $descripcion = trim($data['descripcion'] ?? '');
            $estudios = $data['estudios'] ?? [];
            
            if (empty($estudios)) {
                echo json_encode(["status" => "error", "message" => "Debe seleccionar o crear al menos un estudio prequirúrgico."]);
                exit;
            }

            if ($id_cirugia > 0) {
                // Usar cirugía existente
                $guardado = ConsultasCirugias::guardarCombo($id_cirugia, $estudios);
            } else {
                // Validaciones para cirugía nueva
                if (empty($nombre_cirugia)) {
                    echo json_encode(["status" => "error", "message" => "El nombre de la cirugía es obligatorio."]);
                    exit;
                }
                
                if (mb_strlen($nombre_cirugia, 'UTF-8') < 3) {
                    echo json_encode(["status" => "error", "message" => "El nombre de la cirugía debe tener al menos 3 caracteres."]);
                    exit;
                }
                
                if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ0-9\s]+$/u', $nombre_cirugia)) {
                    echo json_encode(["status" => "error", "message" => "El nombre de la cirugía contiene caracteres no permitidos."]);
                    exit;
                }

                if (ConsultasCirugias::existeNombreCirugia($nombre_cirugia)) {
                    echo json_encode(["status" => "error", "message" => "Ya existe una cirugía con ese nombre."]);
                    exit;
                }
                
                // Crear nueva cirugía
                $id_cirugia = ConsultasCirugias::crearCirugiaCatalogo($nombre_cirugia, $descripcion);
                if (!$id_cirugia) {
                    echo json_encode(["status" => "error", "message" => "No se pudo crear la cirugía."]);
                    exit;
                }
                
                // Guardar combo
                $guardado = ConsultasCirugias::guardarCombo($id_cirugia, $estudios);
            }
            
            if ($guardado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Cirugía y estudios vinculados guardados correctamente.",
                    "id_cirugia" => $id_cirugia
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo vincular los estudios."]);
            }
            break;

        case 'crear_estudio':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Método no permitido."]);
                exit;
            }
            
            $nombre = trim($data['nombre'] ?? '');
            $descripcion = trim($data['descripcion'] ?? '');
            $observaciones = trim($data['observaciones'] ?? '');
            
            if (empty($nombre)) {
                echo json_encode(["status" => "error", "message" => "El nombre del estudio es obligatorio."]);
                exit;
            }
            
            if (mb_strlen($nombre, 'UTF-8') < 3) {
                echo json_encode(["status" => "error", "message" => "El nombre debe tener al menos 3 caracteres."]);
                exit;
            }
            
            if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ0-9\s\-\/\(\)\.]+$/u', $nombre)) {
                echo json_encode(["status" => "error", "message" => "El nombre del estudio contiene caracteres no permitidos."]);
                exit;
            }
            
            $id_existente = ConsultasCirugias::existeNombreEstudio($nombre);
            if ($id_existente) {
                echo json_encode(["status" => "error", "message" => "Ya existe un estudio con ese nombre."]);
                exit;
            }
            
            $id_estudio = ConsultasCirugias::crearEstudio($nombre, $descripcion, $observaciones);
            
            if ($id_estudio) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Estudio creado correctamente.",
                    "estudio" => [
                        "id" => $id_estudio,
                        "nombre" => mb_strtoupper($nombre, 'UTF-8'),
                        "descripcion" => $descripcion,
                        "observaciones" => $observaciones
                    ]
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo crear el estudio."]);
            }
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Acción no válida."]);
            break;
    }
} catch (Exception $e) {
    error_log("Error en cirugias-e.php: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error interno del servidor. Por favor, intente nuevamente."]);
}
?>

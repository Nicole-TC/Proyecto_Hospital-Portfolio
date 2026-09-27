<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado. Sesión no válida.']);
    exit;
}

require_once __DIR__ . '/../php/lista_espera-b.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

        if ($accion === 'config_agenda_cirugia') {
            require_once __DIR__ . '/../php/agenda-b.php';
            $idTipoTurnoCir = ConsultasAgenda::obtenerIdTipoTurnoCirugia();
            if (!$idTipoTurnoCir) {
                echo json_encode(['status' => 'error', 'sin_config' => true, 'message' => 'No hay tipo de turno para CIRUGIA.']);
                exit;
            }
            $config = ConsultasAgenda::obtenerConfigActiva($idTipoTurnoCir);
            // Si no hay ciclo vigente hoy (ej. cupos creados para la semana siguiente),
            // usar el próximo ciclo para habilitar la programación.
            if ($config['sin_config']) {
                $config = ConsultasAgenda::obtenerConfigFutura($idTipoTurnoCir);
            }
            echo json_encode(['status' => 'success', 'config' => $config]);
            exit;
        }

        if ($accion === 'obtener_cirujanos') {
            $cirujanos = ConsultasListasEspera::obtenerCirujanos();
            echo json_encode(['status' => 'success', 'cirujanos' => $cirujanos]);
            exit;
        }

        if ($accion === 'obtener_catalogos_especialidades') {
            $catalogos = ConsultasListasEspera::obtenerCatalogosEspecialidades();
            echo json_encode(['status' => 'success'] + $catalogos);
            exit;
        }

        $estado_apto = isset($_GET['estado_apto']) ? $_GET['estado_apto'] : 'OK';
        $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

        $resultado = ConsultasListasEspera::listarPacientesEspera($estado_apto, $fecha_desde, $fecha_hasta);

        echo json_encode([
            'status'    => 'success',
            'registros' => $resultado['registros'],
            'total'     => $resultado['total']
        ]);
        exit;
    }
    
    if ($method === 'POST') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $accion = isset($data['accion']) ? $data['accion'] : '';

        if ($accion === 'programar_cirugia') {
            require_once __DIR__ . '/../php/agenda-b.php';
            
            $idTipoTurnoCir = ConsultasAgenda::obtenerIdTipoTurnoCirugia();
            if (!$idTipoTurnoCir) {
                echo json_encode(['status' => 'error', 'message' => 'No hay un tipo de turno CIR configurado en el sistema.']);
                exit;
            }

            $datos_turno = [
                'id_persona_asiste' => $data['id_persona_asiste'] ?? 0,
                'id_orden'          => $data['id_orden'] ?? 0,
                'id_tipo_turno'     => $idTipoTurnoCir,
                'fecha_hora'        => $data['fecha_hora'] ?? '',
                'observaciones'     => $data['observaciones'] ?? null,
                'id_profesional'    => $data['id_profesional'] ?? null,
            ];

            $resultado = ConsultasAgenda::programarTurno($datos_turno);

            if ($resultado['ok']) {
                echo json_encode(['status' => 'success', 'message' => 'Cirugía programada exitosamente.']);
            } else {
                $errores = implode(' ', $resultado['errores']);
                echo json_encode(['status' => 'error', 'message' => $errores]);
            }
            exit;
        }
        
        echo json_encode(['status' => 'error', 'message' => 'Acción POST no válida.']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado. Sesión no válida.']);
    exit;
}

require_once __DIR__ . '/../php/asistencia-b.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $id_turno = filter_var($_GET['id_turno'] ?? null, FILTER_VALIDATE_INT);

        if ($id_turno) {
            $turno = ConsultasAsistencia::obtenerTurno($id_turno);
            if ($turno) {
                echo json_encode(['status' => 'success', 'turno' => $turno]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Turno no encontrado.']);
            }
            exit;
        }

        $filtro = $_GET['filtro'] ?? null;
        $valor  = $_GET['valor']  ?? null;
        $pagina = (int)($_GET['pagina'] ?? 1);
        $limite = (int)($_GET['limite'] ?? 10);
        $orden  = strtoupper($_GET['orden'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $resultado = ConsultasAsistencia::listarTurnos($filtro, $valor, $pagina, $limite, $orden);

        echo json_encode([
            'status'  => 'success',
            'turnos'  => $resultado['turnos'],
            'total'   => $resultado['total'],
            'pagina'  => $pagina,
            'limite'  => $limite,
        ]);
        exit;
    }

    if ($method === 'POST') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            echo json_encode(['status' => 'error', 'message' => 'JSON inválido.']);
            exit;
        }

        $accion = $data['accion'] ?? '';
        $id_turno = filter_var($data['id_turno'] ?? '', FILTER_VALIDATE_INT);

        if (!$id_turno || $id_turno <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de turno inválido.']);
            exit;
        }

        $codigos_estado = [
            'marcar_asistencia' => 'CONF',
            'marcar_ausente'    => 'AUS',
            'cancelar_turno'    => 'CANC',
        ];

        $nuevo_codigo = $codigos_estado[$accion] ?? null;
        if (!$nuevo_codigo) {
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
            exit;
        }

        $motivo = trim($data['motivo'] ?? '');
        $resultado = ConsultasAsistencia::cambiarEstadoTurno($id_turno, $nuevo_codigo, $motivo);
        echo json_encode(['status' => 'success', 'message' => $resultado['mensaje']]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

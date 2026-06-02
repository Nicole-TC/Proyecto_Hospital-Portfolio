<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

require_once __DIR__ . '/../php/agenda-b.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── GET: turnos de un día  ───────────────────────────────────
if ($method === 'GET' && $action === 'turnos_del_dia') {
    $fecha = $_GET['fecha'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['ok' => false, 'error' => 'Fecha inválida.']); exit;
    }
    try {
        $turnos = ConsultasAgenda::obtenerTurnosPorFecha($fecha);
        echo json_encode(['ok' => true, 'turnos' => $turnos]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── GET: buscar paciente con validación de circuito ───────────
if ($method === 'GET' && $action === 'buscar_paciente') {
    $dni           = preg_replace('/\D/', '', trim($_GET['dni'] ?? ''));
    $id_tipo_turno = (int)($_GET['id_tipo_turno'] ?? 0);

    if (strlen($dni) < 7 || strlen($dni) > 8) {
        echo json_encode(['ok' => false, 'error' => 'DNI inválido.']); exit;
    }
    if (!$id_tipo_turno) {
        echo json_encode(['ok' => false, 'error' => 'Tipo de turno requerido.']); exit;
    }
    try {
        echo json_encode(ConsultasAgenda::buscarPacienteConValidacion($dni, $id_tipo_turno));
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── GET: obtener id_tipo_turno prequirúrgico ───────────────────────────
if ($method === 'GET' && $action === 'obtener_tipo_turno_preq') {
    try {
        $id = ConsultasAgenda::obtenerIdTipoTurnoPrequirurgico();
        echo json_encode($id
            ? ['ok' => true,  'id_tipo_turno' => $id]
            : ['ok' => false, 'error' => 'No se encontró el tipo de turno prequirúrgico.']
        );
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── GET: buscar turnos (buscador) ──────────────────────────────────────
if ($method === 'GET' && $action === 'buscar_turnos') {
    $query  = trim($_GET['q']      ?? '');
    $filtro = trim($_GET['filtro'] ?? 'apellido');
    try {
        $turnos = ConsultasAgenda::buscarTurnos($filtro, $query);
        echo json_encode(['ok' => true, 'turnos' => $turnos]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── POST: programar turno ───────────────────────────────────
if ($method === 'POST' && $action === 'programar_turno') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['ok' => false, 'error' => 'JSON inválido.']); exit;
    }
    try {
        echo json_encode(ConsultasAgenda::programarTurno($input));
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── Método no permitido ────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método o acción no permitidos.']);
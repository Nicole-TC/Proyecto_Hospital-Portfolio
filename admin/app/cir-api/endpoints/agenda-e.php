<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

require_once __DIR__ . '/../php/agenda-b.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── GET: cupos de una fecha ────────────────────────────────────────────
if ($method === 'GET' && $action === 'obtener_cupos') {
    $fecha = $_GET['fecha'] ?? '';
    if (!$fecha) { echo json_encode(['ok' => false, 'error' => 'Fecha requerida.']); exit; }
    try {
        $cupos = ConsultasAgenda::obtenerCuposPorFecha($fecha);
        echo json_encode(['ok' => true] + $cupos);
    } catch (Exception $e) { echo json_encode(['ok' => false, 'error' => $e->getMessage()]); }
    exit;
}

// ── GET: tipos de turno ────────────────────────────────────────────────
if ($method === 'GET' && $action === 'obtener_tipos_turno') {
    try {
        $tipos = ConsultasAgenda::obtenerTiposTurno();
        echo json_encode(['ok' => true, 'tipos' => $tipos]);
    } catch (Exception $e) { echo json_encode(['ok' => false, 'error' => $e->getMessage()]); }
    exit;
}

// ── GET: buscar paciente con orden ─────────────────────────────────────
if ($method === 'GET' && $action === 'buscar_paciente') {
    $dni = str_replace('.', '', $_GET['dni'] ?? '');
    if (!preg_match('/^\d{7,8}$/', $dni)) { echo json_encode(['ok' => false, 'error' => 'DNI inválido.']); exit; }
    try {
        $paciente = ConsultasAgenda::buscarPacienteConOrden($dni);
        echo json_encode($paciente
            ? ['ok' => true, 'encontrado' => true, 'paciente' => $paciente]
            : ['ok' => true, 'encontrado' => false, 'error' => 'El paciente no tiene una orden médica registrada.']
        );
    } catch (Exception $e) { echo json_encode(['ok' => false, 'error' => $e->getMessage()]); }
    exit;
}

// ── POST: guardar cupos ────────────────────────────────────────────────
if ($method === 'POST' && $action === 'guardar_cupos') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['cupos'])) { echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']); exit; }
    try {
        foreach ($input['cupos'] as $fecha => $total) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !is_numeric($total)) continue;
            ConsultasAgenda::guardarCupos($fecha, (int)$total);
        }
        echo json_encode(['ok' => true, 'mensaje' => 'Cupos guardados correctamente.']);
    } catch (Exception $e) { echo json_encode(['ok' => false, 'error' => $e->getMessage()]); }
    exit;
}

// ── POST: programar turno ──────────────────────────────────────────────
if ($method === 'POST' && $action === 'programar_turno') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) { echo json_encode(['ok' => false, 'error' => 'JSON inválido.']); exit; }
    $errores = ConsultasAgenda::validarDatosTurno($input);
    if (!empty($errores)) { echo json_encode(['ok' => false, 'errores' => $errores]); exit; }
    try {
        echo json_encode(ConsultasAgenda::programarTurno($input));
    } catch (Exception $e) { echo json_encode(['ok' => false, 'error' => $e->getMessage()]); }
    exit;
}

// ── Método no permitido ────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método o acción no permitidos.']);
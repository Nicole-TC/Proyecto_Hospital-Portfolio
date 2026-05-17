<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

require_once __DIR__ . '/../php/agenda-b.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── GET: cupos de una fecha ────────────────────────────────────────────
if ($method === 'GET' && $action === 'obtener_cupos') {
    $fecha = $_GET['fecha'] ?? '';

    if (!$fecha) {
        echo json_encode(['ok' => false, 'error' => 'Fecha requerida.']);
        exit;
    }

    try {
        $cupos = ConsultasAgenda::obtenerCuposPorFecha($fecha);
        echo json_encode(['ok' => true] + $cupos);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}


// ── POST: guardar cupos ────────────────────────────────────────────────
if ($method === 'POST' && $action === 'guardar_cupos') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || empty($input['cupos'])) {
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']);
        exit;
    }

    try {
        foreach ($input['cupos'] as $fecha => $total) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !is_numeric($total)) continue;
            ConsultasAgenda::guardarCupos($fecha, (int)$total);
        }
        echo json_encode(['ok' => true, 'mensaje' => 'Cupos guardados correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}


// ── Método no permitido ────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método o acción no permitidos.']);
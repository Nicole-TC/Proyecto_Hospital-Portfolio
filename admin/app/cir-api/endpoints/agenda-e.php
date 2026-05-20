<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

require_once __DIR__ . '/../php/agenda-b.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── GET: obtener turnos ────────────────────────────────────────────────
if ($method === 'GET' && $action === 'obtener_turnos') {
    $fecha = $_GET['fecha'] ?? null;
    if ($fecha && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['ok' => false, 'error' => 'Fecha inválida.']); exit;
    }
    try {
        $turnos = ConsultasAgenda::obtenerTurnos($fecha ?: null);
        echo json_encode(['ok' => true, 'turnos' => $turnos]);
    } catch (Exception $e) { echo json_encode(['ok' => false, 'error' => $e->getMessage()]); }
    exit;
}

// ── GET: buscar turnos ─────────────────────────────────────────────────
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

// ── Método no permitido ────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método o acción no permitidos.']);
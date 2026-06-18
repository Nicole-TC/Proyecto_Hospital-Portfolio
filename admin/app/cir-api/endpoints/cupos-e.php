<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

require_once __DIR__ . '/../php/cupos-b.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── GET: listar cupos activos ──────────────────────────────────────────
if ($method === 'GET' && $action === 'listar_cupos') {
    try {
        echo json_encode(ConsultasCupos::listarCupos());
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── Obtener input POST ─────────────────────────────────────────────────
function getInput() {
    $input = $_POST;
    if (empty($input)) {
        $input = json_decode(file_get_contents('php://input'), true);
    }
    return $input ?: null;
}

// ── POST: guardar (crear) cupo ─────────────────────────────────────────
if ($method === 'POST' && $action === 'guardar_cupos') {
    $input = getInput();
    if (!$input) {
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']);
        exit;
    }
    try {
        echo json_encode(ConsultasCupos::guardarCupo($input));
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── POST: actualizar cupo ─────────────────────────────────────────────
if ($method === 'POST' && $action === 'actualizar_cupo') {
    $input = getInput();
    if (!$input) {
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']);
        exit;
    }
    try {
        echo json_encode(ConsultasCupos::actualizarCupo($input));
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── POST: eliminar cupo (borrado lógico) ──────────────────────────────
if ($method === 'POST' && $action === 'eliminar_cupo') {
    $input = getInput();
    $id_cupo = $input['id_cupo'] ?? null;
    if (!$id_cupo) {
        echo json_encode(['ok' => false, 'error' => 'ID de cupo requerido.']);
        exit;
    }
    try {
        echo json_encode(ConsultasCupos::eliminarCupo($id_cupo));
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── Método no permitido ────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método o acción no permitidos.']);

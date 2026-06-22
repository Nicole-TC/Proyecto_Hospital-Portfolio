<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../php/dashboard-b.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$action = $_GET['action'] ?? 'metricas';

if ($action !== 'metricas') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Acción no reconocida']);
    exit;
}

try {
    $metricas = ConsultasDashboard::obtenerMetricas();
    echo json_encode(['ok' => true, 'data' => $metricas]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
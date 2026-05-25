<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
require_once __DIR__ . '/../php/dashboard-b.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$action = $_GET['action'] ?? 'metricas';

if ($action === 'metricas') {
    try {
        $metricas = ConsultasDashboard::obtenerMetricas();
        echo json_encode(['ok' => true, 'data' => $metricas]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Error al obtener métricas: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Acción no reconocida']);


ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../php/dashboard-b.php';

try {
    $metricas = ConsultasDashboard::obtenerMetricas();

    echo json_encode([
        'ok' => true,
        'data' => $metricas
    ]);
} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}

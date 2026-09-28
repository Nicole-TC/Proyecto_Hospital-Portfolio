<?php
header('Content-Type: application/json');

// Inicia la sesion antes de abrir la conexion para que cir_conexion.php pueda
// propagar a PostgreSQL quien consulta. No restringe el acceso por rol: solo
// exige una sesion valida, igual que el resto de los endpoints del circuito.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado. Sesión no válida.']);
    exit;
}

require_once __DIR__ . '/../php/auditoria-b.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $resultado = ConsultasAuditoria::listarAuditoria([
            'tipo'   => isset($_GET['tipo'])   ? $_GET['tipo']   : 'todo',
            'valor'  => isset($_GET['valor'])  ? $_GET['valor']  : '',
            'desde'  => isset($_GET['desde'])  ? $_GET['desde']  : '',
            'hasta'  => isset($_GET['hasta'])  ? $_GET['hasta']  : '',
            'orden'  => isset($_GET['orden'])  ? $_GET['orden']  : 'desc',
            'limite' => isset($_GET['limite']) ? $_GET['limite'] : 10,
            'pagina' => isset($_GET['pagina']) ? $_GET['pagina'] : 1,
        ]);

        echo json_encode([
            'status'        => 'success',
            'registros'     => $resultado['registros'],
            'total'         => $resultado['total'],
            'pagina'        => $resultado['pagina'],
            'limite'        => $resultado['limite'],
            'total_paginas' => $resultado['total_paginas'],
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

<?php
header('Content-Type: application/json');
session_start();

// Validar que el usuario esté logueado en el sistema admin para evitar accesos externos por URL
if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}

// Validar que el método HTTP sea estrictamente GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Código HTTP 405: Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Método no permitido. Este endpoint solo acepta peticiones GET."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_users-b.php';

try {
    // Capturar parámetros de filtrado y paginación desde la URL (GET)
    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : null;
    $valor  = isset($_GET['valor'])  ? $_GET['valor']  : null;
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;

    // Obtenemos la lista de personas y el total para paginación
    $resultado = ConsultasUsers::listarPersonas($filtro, $valor, $pagina, $limite);
    $usuarios  = $resultado['usuarios'];
    $total     = $resultado['total'];
    
    // Obtenemos las estadísticas generales
    $stats = ConsultasUsers::obtenerEstadisticas();

    // Devolvemos la respuesta al front-end
    echo json_encode([
        "status" => "success", 
        "usuarios" => $usuarios,
        "stats" => $stats,
        "total" => $total,
        "pagina" => $pagina,
        "limite" => $limite
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "message" => "Error al obtener usuarios: " . $e->getMessage()
    ]);
}
?>

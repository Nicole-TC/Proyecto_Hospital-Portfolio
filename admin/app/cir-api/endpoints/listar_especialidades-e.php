<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión no válida."]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

require_once __DIR__ . '/../php/consultas_especialidades-b.php';

try {
    $tipo   = isset($_GET['tipo'])   ? $_GET['tipo']          : 'especialidad';
    $nombre = isset($_GET['nombre']) ? trim($_GET['nombre'])   : '';
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina']   : 1;
    $limite = isset($_GET['limite']) ? (int)$_GET['limite']   : 10;

    $resultado = ConsultasEspecialidades::listar($tipo, $nombre, $pagina, $limite);

    echo json_encode([
        "status"    => "success",
        "registros" => $resultado['registros'],
        "total"     => $resultado['total'],
        "pagina"    => $pagina,
        "limite"    => $limite
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}
?>

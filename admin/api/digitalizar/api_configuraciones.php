<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../model/conexion_dig.php';
require_once __DIR__ . '/helpers_dig.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "No autorizado."], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SESSION['admin'] != 1) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Acceso denegado."], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $bd_dig->query("SELECT id, clave, valor, descripcion FROM configuraciones ORDER BY id ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'PUT':
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') === false) {
            http_response_code(415);
            echo json_encode(['success' => false, 'error' => 'Content-Type debe ser application/json']);
            break;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'JSON inválido']);
            break;
        }

        $id = filter_var($data['id'] ?? '', FILTER_VALIDATE_INT);
        $valor = isset($data['valor']) ? trim($data['valor']) : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            break;
        }

        if ($valor === null || $valor === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El campo valor es obligatorio']);
            break;
        }

        $stmtClave = $bd_dig->prepare("SELECT clave FROM configuraciones WHERE id = ?");
        $stmtClave->execute([$id]);
        $config = $stmtClave->fetch(PDO::FETCH_ASSOC);

        if (!$config) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Configuración no encontrada']);
            break;
        }

        $clave = $config['clave'];

        if ($clave === 'max_peso_archivo_mb') {
            if (!is_numeric($valor) || $valor <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El valor debe ser un número positivo (MB)']);
                break;
            }
        } elseif ($clave === 'formatos_permitidos') {
            if (!preg_match('/^[a-z0-9]+(\,[a-z0-9]+)*$/i', $valor)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Formato inválido. Use extensiones separadas por coma (ej: pdf,jpg,png)']);
                break;
            }
        }

        $stmt = $bd_dig->prepare("UPDATE configuraciones SET valor = ? WHERE id = ?");
        $stmt->execute([$valor, $id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la configuración']);
            break;
        }

        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}

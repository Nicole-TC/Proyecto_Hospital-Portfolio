<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "No autorizado. Por favor inicie sesión."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../../model/conexion_dig.php';
require_once __DIR__ . '/helpers_dig.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // LISTAR
    case 'GET':
        $stmt = $bd_dig->query("SELECT id, nombre, estado::int as estado FROM tipos_documentos ORDER BY id ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // CREAR
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $errores = [];

        $nombre = capitalizar($data['nombre'] ?? '');
        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio';
        } elseif (preg_match('/\d/', $nombre)) {
            $errores[] = 'El nombre no debe contener números';
        } elseif (strlen($nombre) > 100) {
            $errores[] = 'El nombre no debe exceder 100 caracteres';
        } else {
            $dup = $bd_dig->prepare("SELECT id FROM tipos_documentos WHERE LOWER(nombre) = LOWER(?)");
            $dup->execute([$nombre]);
            if ($dup->fetch()) {
                $errores[] = 'Ya existe un tipo de documento con ese nombre';
            }
        }

        $estado = $data['estado'] ?? null;
        if (!in_array($estado, ['0', '1', 0, 1], true)) {
            $errores[] = 'El estado debe ser Activo o Inactivo';
        }

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errores' => $errores]);
            break;
        }

        $stmt = $bd_dig->prepare(
            "INSERT INTO tipos_documentos (nombre, estado, id_usuario) VALUES (?, ?, ?)"
        );
        $stmt->execute([$nombre, $estado, $_SESSION['id']]);

        echo json_encode(['success' => true, 'id' => $bd_dig->lastInsertId()]);
        break;

    // ACTUALIZAR
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $errores = [];

        $id = $data['id'] ?? null;
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            $errores[] = 'ID inválido';
        } else {
            $exist = $bd_dig->prepare("SELECT id FROM tipos_documentos WHERE id = ?");
            $exist->execute([$id]);
            if (!$exist->fetch()) {
                $errores[] = 'El tipo de documento no existe';
            }
        }

        $nombre = capitalizar($data['nombre'] ?? '');
        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio';
        } elseif (preg_match('/\d/', $nombre)) {
            $errores[] = 'El nombre no debe contener números';
        } elseif (strlen($nombre) > 100) {
            $errores[] = 'El nombre no debe exceder 100 caracteres';
        } else {
            $dup = $bd_dig->prepare("SELECT id FROM tipos_documentos WHERE LOWER(nombre) = LOWER(?) AND id != ?");
            $dup->execute([$nombre, $id]);
            if ($dup->fetch()) {
                $errores[] = 'Ya existe otro tipo de documento con ese nombre';
            }
        }

        $estado = $data['estado'] ?? null;
        if (!in_array($estado, ['0', '1', 0, 1], true)) {
            $errores[] = 'El estado debe ser Activo o Inactivo';
        }

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errores' => $errores]);
            break;
        }

        if ($estado == 0) {
            $enUso = $bd_dig->prepare("SELECT 1 FROM documentos WHERE id_tipo_documento = ? AND estado = true LIMIT 1");
            $enUso->execute([$id]);
            if ($enUso->fetch()) {
                http_response_code(400);
                $msg = 'No se puede desactivar: el tipo de documento está asignado a uno o más documentos. Primero debe quitarlo de los documentos asociados.';
                echo json_encode(['success' => false, 'errores' => [$msg]]);
                break;
            }
        }

        $stmt = $bd_dig->prepare(
            "UPDATE tipos_documentos SET nombre = ?, estado = ?, id_usuario = ? WHERE id = ?"
        );
        $stmt->execute([$nombre, $estado, $_SESSION['id'], $id]);

        echo json_encode(['success' => true]);
        break;

    // ELIMINAR
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            break;
        }

        $check = $bd_dig->prepare("SELECT estado FROM tipos_documentos WHERE id = ?");
        $check->execute([$id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'El tipo de documento no existe']);
            break;
        }

        if ($row['estado'] === 'f' || $row['estado'] === false || $row['estado'] === 0) {
            echo json_encode(['success' => false, 'error' => 'El tipo de documento ya está desactivado']);
            break;
        }

        $enUso = $bd_dig->prepare("SELECT 1 FROM documentos WHERE id_tipo_documento = ? AND estado = true LIMIT 1");
        $enUso->execute([$id]);
        if ($enUso->fetch()) {
            $msg = 'No se puede desactivar: el tipo de documento está asignado a uno o más documentos. Primero debe quitarlo de los documentos asociados.';
            echo json_encode(['success' => false, 'error' => $msg]);
            break;
        }

        $stmt = $bd_dig->prepare("UPDATE tipos_documentos SET estado = false, id_usuario = ? WHERE id = ?");
        $stmt->execute([$_SESSION['id'], $id]);

        echo json_encode(['success' => true]);
        break;
}

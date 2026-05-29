<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../model/conexion_dig.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "No autorizado. Por favor inicie sesion."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // LISTAR
    case 'GET':
        $mostrar = $_GET['mostrar'] ?? 'activos';
        if ($mostrar === 'inactivos') {
            $stmt = $bd_dig->query("SELECT id, nombre_etiqueta, color_hex, estado::int as estado FROM etiquetas WHERE estado = false ORDER BY id ASC");
        } elseif ($mostrar === 'todos') { 
            
            $stmt = $bd_dig->query("SELECT id, nombre_etiqueta, color_hex, estado::int as estado FROM etiquetas ORDER BY id ASC");
        } else {
            $stmt = $bd_dig->query("SELECT id, nombre_etiqueta, color_hex, estado::int as estado FROM etiquetas WHERE estado = true ORDER BY id ASC");
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // CREAR
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $errores = [];

        $nombre = trim($data['nombre_etiqueta'] ?? '');
        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio';
        } elseif (preg_match('/\d/', $nombre)) {
            $errores[] = 'El nombre no debe contener números';
        } elseif (strlen($nombre) > 100) {
            $errores[] = 'El nombre no debe exceder 100 caracteres';
        } else {
            $dup = $bd_dig->prepare("SELECT id FROM etiquetas WHERE LOWER(nombre_etiqueta) = LOWER(?)");
            $dup->execute([$nombre]);
            if ($dup->fetch()) {
                $errores[] = 'Ya existe una etiqueta con ese nombre';
            }
        }

        $color = $data['color_hex'] ?? '';
        if ($color === '') {
            $errores[] = 'El color es obligatorio';
        } elseif (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $errores[] = 'El color debe ser un hexadecimal válido (#RRGGBB)';
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
            "INSERT INTO etiquetas (nombre_etiqueta, color_hex, estado) VALUES (?, ?, ?)"
        );
        $stmt->execute([$nombre, $color, $estado]);

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
            $exist = $bd_dig->prepare("SELECT id FROM etiquetas WHERE id = ?");
            $exist->execute([$id]);
            if (!$exist->fetch()) {
                $errores[] = 'La etiqueta no existe';
            }
        }

        $nombre = trim($data['nombre_etiqueta'] ?? '');
        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio';
        } elseif (preg_match('/\d/', $nombre)) {
            $errores[] = 'El nombre no debe contener números';
        } elseif (strlen($nombre) > 100) {
            $errores[] = 'El nombre no debe exceder 100 caracteres';
        } else {
            $dup = $bd_dig->prepare("SELECT id FROM etiquetas WHERE LOWER(nombre_etiqueta) = LOWER(?) AND id != ?");
            $dup->execute([$nombre, $id]);
            if ($dup->fetch()) {
                $errores[] = 'Ya existe otra etiqueta con ese nombre';
            }
        }

        $color = $data['color_hex'] ?? '';
        if ($color === '') {
            $errores[] = 'El color es obligatorio';
        } elseif (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $errores[] = 'El color debe ser un hexadecimal válido (#RRGGBB)';
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
            "UPDATE etiquetas SET nombre_etiqueta = ?, color_hex = ?, estado = ? WHERE id = ?"
        );
        $stmt->execute([$nombre, $color, $estado, $id]);

        echo json_encode(['success' => true]);
        break;

    // ACTIVAR (reversa de baja lógica)
    case 'PATCH':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            break;
        }

        $check = $bd_dig->prepare("SELECT estado FROM etiquetas WHERE id = ?");
        $check->execute([$id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'La etiqueta no existe']);
            break;
        }

        if ($row['estado'] === 't' || $row['estado'] === true) {
            echo json_encode(['success' => false, 'error' => 'La etiqueta ya está activa']);
            break;
        }

        $stmt = $bd_dig->prepare("UPDATE etiquetas SET estado = true WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
        break;

    // ELIMINAR (baja lógica)
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            break;
        }

        $check = $bd_dig->prepare("SELECT estado FROM etiquetas WHERE id = ?");
        $check->execute([$id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'La etiqueta no existe']);
            break;
        }

        if ($row['estado'] === 'f' || $row['estado'] === false) {
            echo json_encode(['success' => false, 'error' => 'La etiqueta ya está desactivada']);
            break;
        }

        $stmt = $bd_dig->prepare("UPDATE etiquetas SET estado = false WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
        break;
}

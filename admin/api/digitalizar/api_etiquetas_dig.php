<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../model/conexion_dig.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // LISTAR
    case 'GET':
        $stmt = $bd_dig->query("SELECT id, nombre_etiqueta, color_hex, estado::int as estado FROM etiquetas ORDER BY id ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // CREAR
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $bd_dig->prepare(
            "INSERT INTO etiquetas (nombre_etiqueta, color_hex, estado) VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $data['nombre_etiqueta'],
            $data['color_hex'],
            $data['estado']
        ]);

        echo json_encode(['success' => true, 'id' => $bd_dig->lastInsertId()]);
        break;

    // ACTUALIZAR
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $bd_dig->prepare(
            "UPDATE etiquetas SET nombre_etiqueta = ?, color_hex = ?, estado = ? WHERE id = ?"
        );
        $stmt->execute([
            $data['nombre_etiqueta'],
            $data['color_hex'],
            $data['estado'],
            $data['id']
        ]);

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

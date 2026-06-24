<?php
session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../../model/conexion_dig.php';
require_once __DIR__ . '/helpers_dig.php';

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
        // Obtener todos los servicios
        $stmt = $bd_dig->query("
            SELECT s.id, s.nombre, s.area, s.estado::int as estado, s.id_admin, s.id_personal, s.fecha_creacion,
            p.apellidoynombre AS personal_nombre
            FROM servicios s
            LEFT JOIN personal p ON s.id_personal = p.id
            ORDER BY s.id ASC
        ");
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Necesitamos mapear los de admin
        $admins = [];
        $stmtAdmin = $bd_dig->prepare("SELECT id, nombre || ' ' || apellido AS nombre FROM admin");
        if ($stmtAdmin) {
            $stmtAdmin->execute();
            $admins_db = $stmtAdmin->fetchAll(PDO::FETCH_ASSOC);
            foreach ($admins_db as $a) {
                $admins[$a['id']] = $a['nombre'];
            }
        }

        // Asignar el nombre del responsable
        foreach ($servicios as &$s) {
            if ($s['id_personal']) {
                $s['responsable_nombre'] = $s['personal_nombre'];
                $s['tipo_responsable'] = 'personal';
                $s['responsable_id'] = $s['id_personal'];
            } elseif ($s['id_admin']) {
                $s['responsable_nombre'] = $admins[$s['id_admin']] ?? 'Admin Desconocido';
                $s['tipo_responsable'] = 'admin';
                $s['responsable_id'] = $s['id_admin'];
            } else {
                $s['responsable_nombre'] = 'Sin asignar';
                $s['tipo_responsable'] = '';
                $s['responsable_id'] = '';
            }
            unset($s['personal_nombre']);
        }

        echo json_encode($servicios);
        break;

    // CREAR
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $errores = [];

        $nombre = capitalizar($data['nombre'] ?? '');
        $area = capitalizar($data['area'] ?? '');
        $responsable = trim($data['responsable'] ?? ''); 
                
        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio';
        } else {
            $dup = $bd_dig->prepare("SELECT id FROM servicios WHERE LOWER(nombre) = LOWER(?)");
            $dup->execute([$nombre]);
            if ($dup->fetch()) {
                $errores[] = 'Ya existe un servicio con ese nombre';
            }
        }

        $id_admin = null;
        $id_personal = null;

        if ($responsable !== '') {
            $parts = explode('_', $responsable);
            if (count($parts) === 2) {
                if ($parts[0] === 'admin') {
                    $id_admin = (int)$parts[1];
                } elseif ($parts[0] === 'personal') {
                    $id_personal = (int)$parts[1];
                }
            }
        }

        $estado = $data['estado'] ?? 1;

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errores' => $errores]);
            break;
        }

        $stmt = $bd_dig->prepare(
            "INSERT INTO servicios (nombre, area, id_admin, id_personal, estado, id_usuario) VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        // Convertir variables en un formato que Postgres entienda para boolean
        $estadoVal = ($estado == '1' || $estado === 1 || $estado === true) ? 'true' : 'false';

        $stmt->execute([$nombre, $area ?: null, $id_admin, $id_personal, $estadoVal, $_SESSION['id']]);

        // PDO::lastInsertId con el sequence name para Postgres
        $lastId = $bd_dig->lastInsertId('servicios_id_seq');

        echo json_encode(['success' => true, 'id' => $lastId]);
        break;

    // ACTUALIZAR
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $errores = [];

        $id = $data['id'] ?? null;
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            $errores[] = 'ID inválido';
        } else {
            $exist = $bd_dig->prepare("SELECT id FROM servicios WHERE id = ?");
            $exist->execute([$id]);
            if (!$exist->fetch()) {
                $errores[] = 'El servicio no existe';
            }
        }

        $nombre = capitalizar($data['nombre'] ?? '');
        $area = capitalizar($data['area'] ?? '');
        $responsable = trim($data['responsable'] ?? '');
        
        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio';
        } else {
            $dup = $bd_dig->prepare("SELECT id FROM servicios WHERE LOWER(nombre) = LOWER(?) AND id != ?");
            $dup->execute([$nombre, $id]);
            if ($dup->fetch()) {
                $errores[] = 'Ya existe otro servicio con ese nombre';
            }
        }

        $id_admin = null;
        $id_personal = null;

        if ($responsable !== '') {
            $parts = explode('_', $responsable);
            if (count($parts) === 2) {
                if ($parts[0] === 'admin') {
                    $id_admin = (int)$parts[1];
                } elseif ($parts[0] === 'personal') {
                    $id_personal = (int)$parts[1];
                }
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

        $estadoVal = ($estado == '1' || $estado === 1 || $estado === true) ? 'true' : 'false';

        $stmt = $bd_dig->prepare(
            "UPDATE servicios SET nombre = ?, area = ?, id_admin = ?, id_personal = ?, estado = ?, id_usuario = ? WHERE id = ?"
        );
        $stmt->execute([$nombre, $area ?: null, $id_admin, $id_personal, $estadoVal, $_SESSION['id'], $id]);

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

        $check = $bd_dig->prepare("SELECT estado FROM servicios WHERE id = ?");
        $check->execute([$id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'El servicio no existe']);
            break;
        }

        if ($row['estado'] === 'f' || $row['estado'] === false || $row['estado'] === 0) {
            echo json_encode(['success' => false, 'error' => 'El servicio ya está desactivado']);
            break;
        }

        $stmt = $bd_dig->prepare("UPDATE servicios SET estado = false, id_usuario = ? WHERE id = ?");
        $stmt->execute([$_SESSION['id'], $id]);

        echo json_encode(['success' => true]);
        break;
}

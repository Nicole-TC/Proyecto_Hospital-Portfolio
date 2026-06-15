<?php
session_start();
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
header('Content-Type: application/json; charset=utf-8');

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

// Validar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido"], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $bd_dig->beginTransaction();

    // 1. Obtener y validar datos del formulario
    $id_documento = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
    $titulo = capitalizar($_POST['titulo'] ?? '');
    
    // Campo de identificación único
    $dni = trim($_POST['dni'] ?? $_POST['numero_identificacion'] ?? '');
    
    $nombre = capitalizar($_POST['nombre'] ?? '');
    $apellido = capitalizar($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $id_tipo_documento = filter_var($_POST['id_tipo_documento'] ?? '', FILTER_VALIDATE_INT);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tag_ids = isset($_POST['tag_id']) && is_array($_POST['tag_id']) ? $_POST['tag_id'] : [];
    $id_usuario = $_SESSION['id'] ?? null;
    
    // Campos de clasificación y género
    $id_servicio = filter_var($_POST['id_servicio'] ?? '', FILTER_VALIDATE_INT);
    $id_tipo_identificacion = filter_var($_POST['id_tipo_identificacion'] ?? '', FILTER_VALIDATE_INT);
    $genero = trim($_POST['genero'] ?? '');

    if (!$id_documento || !$dni || !$nombre || !$apellido || !$id_tipo_documento || !$titulo || !$id_servicio || !$id_tipo_identificacion) {
        $missing = [];
        if (!$id_documento) $missing[] = "ID Documento";
        if (!$dni) $missing[] = "Número de Documento";
        if (!$id_tipo_identificacion) $missing[] = "Tipo de Identificación";
        if (!$nombre) $missing[] = "Nombre";
        if (!$apellido) $missing[] = "Apellido";
        if (!$id_tipo_documento) $missing[] = "Tipo de Archivo";
        if (!$titulo) $missing[] = "Título";
        if (!$id_servicio) $missing[] = "Sector";
        throw new Exception("Faltan datos requeridos: " . implode(", ", $missing));
    }

    // Validaciones estrictas
    if (strlen($dni) < 5) {
        throw new Exception("El número de documento es demasiado corto (mínimo 5 caracteres).");
    }
    if (strlen($nombre) > 20) {
        throw new Exception("El nombre no puede superar los 20 caracteres.");
    }
    if (strlen($apellido) > 20) {
        throw new Exception("El apellido no puede superar los 20 caracteres.");
    }

    // 2. Gestionar la Persona (Buscar en `personal_identificaciones` o insertar paciente)
    $stmt = $bd_dig->prepare("
        SELECT id_personal 
        FROM personal_identificaciones 
        WHERE numero_identificacion = ? AND id_tipo_identificacion = ?
    ");
    $stmt->execute([$dni, $id_tipo_identificacion]);
    $persona = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($persona) {
        $id_persona = $persona['id_personal'];
        // Opcional: Actualizar el nombre y apellido del paciente existente si se modificaron manualmente en el form
        $stmtUpdatePers = $bd_dig->prepare("
            UPDATE personal 
            SET nombre = ?, apellido = ?, apellidoynombre = ?, telefono = COALESCE(NULLIF(?, ''), telefono), correo = COALESCE(NULLIF(?, ''), correo)
            WHERE id = ?
        ");
        $apellidoynombre = $apellido . ' ' . $nombre;
        $stmtUpdatePers->execute([$nombre, $apellido, $apellidoynombre, $telefono, $email, $id_persona]);
    } else {
        // Extraer solo los números de la identificación
        $numeros_extraidos = preg_replace('/[^0-9]/', '', $dni);
        
        if (empty($numeros_extraidos) || $numeros_extraidos > 2100000000) {
            if (strlen($numeros_extraidos) > 8) {
                $dummy_dni = (int)substr($numeros_extraidos, -8);
            } else {
                $dummy_dni = 900000000 + rand(1, 99999999);
            }
        } else {
            $dummy_dni = (int)$numeros_extraidos;
        }

        // Bucle de seguridad
        while (true) {
            $stmtCheck = $bd_dig->prepare("SELECT id FROM personal WHERE dni = ?");
            $stmtCheck->execute([$dummy_dni]);
            if (!$stmtCheck->fetch()) {
                break;
            }
            $dummy_dni += 100000000;
            if ($dummy_dni > 2100000000) {
                $dummy_dni = 900000000 + rand(1, 99999999);
            }
        }

        // Insertar paciente en `personal`
        $stmt = $bd_dig->prepare("
            INSERT INTO personal (
                dni, activo, legajo, apellidoynombre, apellido, nombre, 
                encasillamiento, cargo, rh, dependencia, regimen, turno, telefono, correo, id_usuario
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id
        ");
        $apellidoynombre = $apellido . ' ' . $nombre;
        $stmt->execute([
            $dummy_dni,
            true,
            0,
            $apellidoynombre, 
            $apellido, 
            $nombre, 
            'Paciente',
            'Paciente',
            0,
            'Ninguna',
            'Ninguno',
            'Ninguno',
            $telefono, 
            $email,
            $id_usuario
        ]);
        $id_persona = $stmt->fetchColumn();

        // Insertar la identificación real
        $stmtId = $bd_dig->prepare("
            INSERT INTO personal_identificaciones (id_personal, id_tipo_identificacion, numero_identificacion, genero)
            VALUES (?, ?, ?, ?)
        ");
        $stmtId->execute([$id_persona, $id_tipo_identificacion, $dni, $genero ?: null]);
    }

    // 3. Preparar comentario estructurado
    $comentario_final = "Título: " . $titulo . "\nDescripción: " . $descripcion;
    
    // 4. Actualizar el documento en la base de datos
    $stmt = $bd_dig->prepare("
        UPDATE documentos 
        SET id_persona = ?, 
            id_tipo_documento = ?, 
            id_servicio = ?, 
            comentario = ?
        WHERE id = ? AND estado = true
    ");
    $stmt->execute([
        $id_persona, 
        $id_tipo_documento, 
        $id_servicio,
        trim($comentario_final),
        $id_documento
    ]);

    // 5. Actualizar etiquetas vinculadas al documento
    $stmtDeleteTags = $bd_dig->prepare("DELETE FROM documento_etiquetas WHERE id_documento = ?");
    $stmtDeleteTags->execute([$id_documento]);

    if (!empty($tag_ids)) {
        $stmtTag = $bd_dig->prepare("INSERT INTO documento_etiquetas (id_documento, id_etiqueta) VALUES (?, ?)");
        foreach ($tag_ids as $tag_id) {
            if (filter_var($tag_id, FILTER_VALIDATE_INT)) {
                $stmtTag->execute([$id_documento, $tag_id]);
            }
        }
    }

    $bd_dig->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Documento editado y registrado correctamente."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    if (isset($bd_dig) && $bd_dig->inTransaction()) {
        $bd_dig->rollBack();
    }
    file_put_contents(__DIR__ . '/last_error.log', date('Y-m-d H:i:s') . ' - Edit Error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

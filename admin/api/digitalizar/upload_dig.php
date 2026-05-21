<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../model/conexion_dig.php';

// Validar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar que exista archivo
if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No se recibió ningún archivo o hubo un error al subirlo"], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $bd_dig->beginTransaction();

    // 1. Obtener y validar datos del formulario
    $titulo = trim($_POST['titulo'] ?? '');
    
    // Campo de identificación único
    $dni = trim($_POST['dni'] ?? '');
    
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $id_tipo_documento = filter_var($_POST['id_tipo_documento'] ?? '', FILTER_VALIDATE_INT);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tag_ids = isset($_POST['tag_id']) && is_array($_POST['tag_id']) ? $_POST['tag_id'] : [];
    $id_usuario = $_SESSION['id'] ?? null;
    
    // Nuevos campos del documento
    $id_servicio = filter_var($_POST['id_servicio'] ?? '', FILTER_VALIDATE_INT);
    $prioridad = trim($_POST['prioridad'] ?? 'Media');
    $estado_documento = trim($_POST['estado_documento'] ?? 'Activo');
    $forzar_subida = isset($_POST['forzar_subida']) && $_POST['forzar_subida'] == '1';

    if (!$dni || !$nombre || !$apellido || !$id_tipo_documento || !$titulo || !$id_servicio) {
        $missing = [];
        if (!$dni) $missing[] = "DNI";
        if (!$nombre) $missing[] = "Nombre";
        if (!$apellido) $missing[] = "Apellido";
        if (!$id_tipo_documento) $missing[] = "Tipo Documento";
        if (!$titulo) $missing[] = "Título";
        if (!$id_servicio) $missing[] = "Sector";
        throw new Exception("Faltan datos requeridos: " . implode(", ", $missing));
    }

    // Validaciones estrictas solicitadas
    if (!preg_match('/^[0-9]{8}$/', $dni)) {
        throw new Exception("El DNI debe contener exactamente 8 dígitos y no puede contener letras.");
    }
    if (strlen($nombre) > 20) {
        throw new Exception("El nombre no puede superar los 20 caracteres.");
    }
    if (strlen($apellido) > 20) {
        throw new Exception("El apellido no puede superar los 20 caracteres.");
    }

    // 2. Gestionar la Persona (Buscar en `personal` o insertar paciente)
    $stmt = $bd_dig->prepare("SELECT id FROM personal WHERE dni = ?");
    $stmt->execute([$dni]);
    $persona = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($persona) {
        $id_persona = $persona['id'];
    } else {
        // Insertar paciente en la tabla `personal` con valores por defecto para los NOT NULL
        $stmt = $bd_dig->prepare("
            INSERT INTO personal (
                dni, activo, legajo, apellidoynombre, apellido, nombre, 
                encasillamiento, cargo, rh, dependencia, regimen, turno, telefono, correo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id
        ");
        $apellidoynombre = $apellido . ' ' . $nombre;
        $stmt->execute([
            $dni, 
            true, // activo
            0, // legajo
            $apellidoynombre, 
            $apellido, 
            $nombre, 
            'Paciente', // encasillamiento
            'Paciente', // cargo
            0, // rh
            'Ninguna', // dependencia
            'Ninguno', // regimen
            'Ninguno', // turno
            $telefono, 
            $email
        ]);
        $id_persona = $stmt->fetchColumn();
    }

    // 3. Comprobar duplicados si no se forzó la subida
    if (!$forzar_subida) {
        $stmt_dup = $bd_dig->prepare("
            SELECT id FROM documentos 
            WHERE id_persona = ? 
            AND id_tipo_documento = ? 
            AND DATE(fecha_subido) = CURRENT_DATE
        ");
        $stmt_dup->execute([$id_persona, $id_tipo_documento]);
        if ($stmt_dup->fetch()) {
            echo json_encode([
                "status" => "duplicate",
                "message" => "⚠ Ya existe un documento similar para esta persona. Agrégalo a lo que ya tenes."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // 4. Gestionar la subida del archivo
    $archivo = $_FILES['archivo'];
    $nombreOriginal = $archivo['name'];
    $temporal = $archivo['tmp_name'];
    
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $formatosPermitidos = ["pdf", "jpg", "jpeg", "png"];
    
    if (!in_array($extension, $formatosPermitidos)) {
        throw new Exception("Formato no permitido. Solo PDF, JPG o PNG.");
    }

    // Validar peso del archivo
    $maxPesoMb = 5; // Fallback
    try {
        $stmtConf = $bd_dig->prepare("SELECT valor FROM configuraciones WHERE clave = 'max_peso_archivo_mb'");
        $stmtConf->execute();
        $dbVal = $stmtConf->fetchColumn();
        if ($dbVal) {
            $maxPesoMb = $dbVal;
        }
    } catch (PDOException $e) {
        error_log("No se pudo leer configuraciones (peso maximo): " . $e->getMessage());
    }
    
    $maxBytes = $maxPesoMb * 1024 * 1024;
    if ($archivo['size'] > $maxBytes) {
        throw new Exception("El archivo excede el límite permitido de " . $maxPesoMb . "MB.");
    }

    // Carpeta destino
    $carpetaDestino = __DIR__ . '/../../uploads/';
    if (!is_dir($carpetaDestino)) {
        if (!mkdir($carpetaDestino, 0755, true)) {
            throw new Exception("No se pudo crear el directorio de subida.");
        }
    }

    $nombreArchivoNuevo = uniqid("doc_") . '.' . $extension;
    $rutaFisica = $carpetaDestino . $nombreArchivoNuevo;
    $rutaRelativa = "uploads/" . $nombreArchivoNuevo;

    if (!move_uploaded_file($temporal, $rutaFisica)) {
        throw new Exception("Error al mover el archivo al volumen de destino.");
    }

    // 5. Generar código único y preparar comentario
    $codigo_unico = 'DOC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    $comentario_final = "Título: " . $titulo . "\nDescripción: " . $descripcion;
    
    // 6. Insertar el documento en la base de datos (se omite tipo_identificacion y numero_identificacion)
    $stmt = $bd_dig->prepare("
        INSERT INTO documentos (
            id_persona, id_tipo_documento, id_servicio, ruta_archivo, comentario, 
            id_usuario, estado, fecha_subido,
            codigo_unico, prioridad, estado_documento
        ) 
        VALUES (?, ?, ?, ?, ?, ?, true, ?, ?, ?, ?) RETURNING id
    ");
    $stmt->execute([
        $id_persona, 
        $id_tipo_documento, 
        $id_servicio,
        $rutaRelativa, 
        trim($comentario_final), 
        $id_usuario,
        date('Y-m-d H:i:s'), 
        $codigo_unico,
        $prioridad,
        $estado_documento
    ]);
    $id_documento = $stmt->fetchColumn();

    // 7. Insertar etiquetas vinculadas al documento
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
        "status" => "ok",
        "message" => "Documento subido y registrado correctamente.",
        "data" => [
            "documento" => [
                "codigo_unico" => $codigo_unico
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if ($bd_dig->inTransaction()) {
        $bd_dig->rollBack();
    }
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

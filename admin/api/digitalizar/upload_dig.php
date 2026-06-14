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

// Validar que exista archivo
if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No se recibió ningún archivo o hubo un error al subirlo"], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $bd_dig->beginTransaction();

    // 1. Obtener y validar datos del formulario
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
    
    // Nuevos campos del documento
    $id_servicio = filter_var($_POST['id_servicio'] ?? '', FILTER_VALIDATE_INT);
    $prioridad = trim($_POST['prioridad'] ?? 'Media');
    $estado_documento = trim($_POST['estado_documento'] ?? 'Activo');
    $forzar_subida = isset($_POST['forzar_subida']) && $_POST['forzar_subida'] == '1';

    // Datos de la nueva identidad
    $id_tipo_identificacion = filter_var($_POST['id_tipo_identificacion'] ?? '', FILTER_VALIDATE_INT);
    $genero = trim($_POST['genero'] ?? '');

    if (!$dni || !$nombre || !$apellido || !$id_tipo_documento || !$titulo || !$id_servicio || !$id_tipo_identificacion) {
        $missing = [];
        if (!$dni) $missing[] = "Número de Documento";
        if (!$id_tipo_identificacion) $missing[] = "Tipo de Identificación";
        if (!$nombre) $missing[] = "Nombre";
        if (!$apellido) $missing[] = "Apellido";
        if (!$id_tipo_documento) $missing[] = "Tipo de Archivo";
        if (!$titulo) $missing[] = "Título";
        if (!$id_servicio) $missing[] = "Sector";
        throw new Exception("Faltan datos requeridos: " . implode(", ", $missing));
    }

    // Validaciones estrictas solicitadas
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
    } else {
        // Idea del usuario: extraer solo los números de la identificación (ej. pasaportes)
        $numeros_extraidos = preg_replace('/[^0-9]/', '', $dni);
        
        // Si no quedaron números, o si el número extraído es más grande de lo que soporta el 'integer' de Postgres (2.147.483.647)
        if (empty($numeros_extraidos) || $numeros_extraidos > 2100000000) {
            if (strlen($numeros_extraidos) > 8) {
                // Si es enorme (ej. un número de 12 cifras), tomamos los últimos 8 dígitos que sí caben
                $dummy_dni = (int)substr($numeros_extraidos, -8);
            } else {
                $dummy_dni = 900000000 + rand(1, 99999999);
            }
        } else {
            $dummy_dni = (int)$numeros_extraidos;
        }

        // Bucle de seguridad: Verificar que no haya chocado con el UNIQUE constraint (ej: LC 123456 chocando con DNI 123456)
        while (true) {
            $stmtCheck = $bd_dig->prepare("SELECT id FROM personal WHERE dni = ?");
            $stmtCheck->execute([$dummy_dni]);
            if (!$stmtCheck->fetch()) {
                break; // Está libre
            }
            // Si hay colisión, le sumamos un prefijo alto para despejarlo, ya que no puede duplicarse
            $dummy_dni += 100000000;
            // Si al sumar se pasa del máximo permitido, generamos uno aleatorio
            if ($dummy_dni > 2100000000) {
                $dummy_dni = 900000000 + rand(1, 99999999);
            }
        }

        // Insertar paciente en la tabla `personal` con valores por defecto para los NOT NULL
        $stmt = $bd_dig->prepare("
            INSERT INTO personal (
                dni, activo, legajo, apellidoynombre, apellido, nombre, 
                encasillamiento, cargo, rh, dependencia, regimen, turno, telefono, correo, id_usuario
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id
        ");
        $apellidoynombre = $apellido . ' ' . $nombre;
        $stmt->execute([
            $dummy_dni, // dni ficticio único para evitar violar el constraint UNIQUE
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
            $email,
            $id_usuario
        ]);
        $id_persona = $stmt->fetchColumn();

        // Insertar la identificación real
        $stmtId = $bd_dig->prepare("
            INSERT INTO personal_identificaciones (id_personal, id_tipo_identificacion, numero_identificacion, genero)
            VALUES (?, ?, ?, ?)
        ");
        // Convertir strings vacíos a null para campos opcionales como el género si fuera necesario, 
        // aunque genero permite nulos o strings vacíos según tu lógica
        $stmtId->execute([$id_persona, $id_tipo_identificacion, $dni, $genero ?: null]);
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

} catch (Throwable $e) {
    if (isset($bd_dig) && $bd_dig->inTransaction()) {
        $bd_dig->rollBack();
    }
    file_put_contents(__DIR__ . '/last_error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

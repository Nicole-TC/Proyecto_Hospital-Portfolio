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
    $dni = trim($_POST['dni'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $id_tipo_documento = filter_var($_POST['id_tipo_documento'] ?? '', FILTER_VALIDATE_INT);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tag_ids = isset($_POST['tag_id']) && is_array($_POST['tag_id']) ? $_POST['tag_id'] : [];
    $id_usuario = $_SESSION['id'] ?? null;

    if (!$dni || !$nombre || !$apellido || !$id_tipo_documento || !$titulo) {
        throw new Exception("Faltan datos requeridos (Título, DNI, Nombre, Apellido o Tipo de Documento).");
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

    // 3. Gestionar la subida del archivo
    $archivo = $_FILES['archivo'];
    $nombreOriginal = $archivo['name'];
    $temporal = $archivo['tmp_name'];
    
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $formatosPermitidos = ["pdf", "jpg", "jpeg", "png"];
    
    if (!in_array($extension, $formatosPermitidos)) {
        throw new Exception("Formato no permitido. Solo PDF, JPG o PNG.");
    }

    // Carpeta destino (Docker volume mapeado en /var/www/html/admin/uploads)
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

    // 4. Insertar el documento en la base de datos
    // Como no hay campo 'titulo' en la tabla documentos, lo agregamos al comentario
    $comentario_final = "Título: " . $titulo . "\nDescripción: " . $descripcion;
    
    $stmt = $bd_dig->prepare("
        INSERT INTO documentos (id_persona, id_tipo_documento, ruta_archivo, comentario, id_usuario, estado, fecha_subido) 
        VALUES (?, ?, ?, ?, ?, true, NOW()) RETURNING id
    ");
    $stmt->execute([$id_persona, $id_tipo_documento, $rutaRelativa, trim($comentario_final), $id_usuario]);
    $id_documento = $stmt->fetchColumn();

    // 5. Insertar etiquetas vinculadas al documento
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
        "message" => "Documento subido y registrado correctamente."
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
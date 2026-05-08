<?php

require_once '../../model/conexion.php';

header('Content-Type: application/json; charset=utf-8');

// validar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// validar que exista archivo
if (!isset($_FILES['archivo'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "No se recibió ningún archivo"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// detectar archivo
if ($_FILES['archivo']['error'] !== 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Error al subir el archivo"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// leer datos del archivo
$archivo = $_FILES['archivo'];

// obtener nombre original del archivo
$nombreOriginal = basename($archivo['name']);

$tamaño = $archivo['size'];
$tipo = $archivo['type'];
$temporal = $archivo['tmp_name'];

$timestamp = time(); //devuelvo número único basado en fecha/hora actual para luego crear nombres únicos de archivos

$nombreUnico = $timestamp . "_" . $nombreOriginal;

$rutaDestino = "../../uploads/digitalizar/";

// tamaño máximo permitido (5MB)
$tamanoMaximo = 5 * 1024 * 1024;    

if ($tamaño > $tamanoMaximo) {

    http_response_code(413);

    echo json_encode([
        "status" => "error",
        "message" => "El archivo excede el tamaño máximo permitido de 5MB"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// obtener extensión del archivo
$partes = explode('.', $nombreOriginal); // separa el nombre por puntos
$extension = strtolower(end($partes)); // toma la última parte (la extensión)

# archivos permitidos y validación de formato
$formatosPermitidos = ["pdf", "jpg", "jpeg", "png"];

if (!in_array($extension, $formatosPermitidos)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Formato no permitido. Solo PDF, JPG o PNG"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// mover archivo al directorio de almacenamiento
$guardado = move_uploaded_file(
    $temporal,
    $rutaDestino . $nombreUnico
);

// verificar si se guardó correctamente
if (!$guardado) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error al guardar el archivo en el servidor",
        "archivo" => $nombreOriginal
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Recibir parámetros POST necesarios para la inserción
$id_persona = isset($_POST['id_persona']) ? (int)$_POST['id_persona'] : null;
$id_tipo_documento = isset($_POST['id_tipo_documento']) ? (int)$_POST['id_tipo_documento'] : null;
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : null;

// Validar que vengan los datos obligatorios
if ($id_persona === null || $id_tipo_documento === null) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Faltan parámetros: id_persona o id_tipo_documento"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Preparar e insertar en base de datos PostgreSQL
try {
    // Ejecutar INSERT con RETURNING para obtener el ID
    $stmt = $bd_dig->prepare("
        INSERT INTO documentos
        (
            id_persona,
            id_tipo_documento,
            ruta_archivo,
            comentario,
            fecha_subido,
            estado
        )
        VALUES
        (
            :id_persona,
            :id_tipo_documento,
            :ruta_archivo,
            :comentario,
            NOW(),
            true
        )
        RETURNING id
    ");

    $resultado = $stmt->execute([
        ':id_persona' => $id_persona,
        ':id_tipo_documento' => $id_tipo_documento,
        ':ruta_archivo' => $rutaDestino . $nombreUnico,
        ':comentario' => $comentario
    ]);

    if ($resultado) {
        // Obtener el ID del documento insertado
        $documento = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_documento = $documento['id'];

        // Verificar que el documento existe consultando la base de datos
        $verificar = $bd_dig->prepare("
            SELECT id, id_persona, id_tipo_documento, ruta_archivo, comentario, fecha_subido
            FROM documentos
            WHERE id = :id
            LIMIT 1
        ");

        $verificar->execute([':id' => $id_documento]);
        $doc_verificado = $verificar->fetch(PDO::FETCH_ASSOC);

        if ($doc_verificado) {
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Documento insertado correctamente en la base de datos",
                "data" => [
                    "id_documento" => $id_documento,
                    "archivo" => $nombreOriginal,
                    "ruta" => $rutaDestino . $nombreUnico,
                    "id_persona" => $doc_verificado['id_persona'],
                    "id_tipo_documento" => $doc_verificado['id_tipo_documento'],
                    "fecha_subido" => $doc_verificado['fecha_subido']
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Error: documento insertado pero no se puede verificar"
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Error al insertar documento en la base de datos"
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de base de datos",
        "details" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>
<?php
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

move_uploaded_file(
    $temporal,
    $rutaDestino . $nombreUnico
);

// éxito (archivo detectado y sin errores)
echo json_encode([
    "status" => "success",
    "message" => "Archivo recibido correctamente"
], JSON_UNESCAPED_UNICODE);

?>
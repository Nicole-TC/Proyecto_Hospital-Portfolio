<?php
session_start();
include_once '../../model/conexion.php';
require_once '../../funciones/ip.php'; // Devuelve la IP
require_once '../../funciones/mobile_detect.php'; // Devuelve Sistema operativo

// Validar si la sesión contiene el ID del usuario
if (empty($_SESSION['id'])) {
    //echo json_encode(["error" => "La sesión ha expirado o el usuario no está autenticado."]);
    $_SESSION['error'] = "La sesión ha expirado o el usuario no está autenticado.";
    header('Location: ../../index.php?modulo=formularios');
    exit();
}

$id_usuario = $_SESSION['id']; // Obtener el ID del usuario autenticado

// Verificar datos requeridos
if (empty($_POST['nombre']) || empty($_POST['id_servicio']) || empty($_FILES['formulario']['name'])) {
    $_SESSION['error'] = "Faltan datos necesarios para cargar el formulario.";
    header('Location: ../../index.php?modulo=formularios');
    //echo json_encode(["error" => "Faltan datos necesarios para cargar el formulario."]);
    exit();
}

// Sanitizar y validar entrada
$nombre = trim(filter_var($_POST['nombre'], FILTER_SANITIZE_SPECIAL_CHARS));
$descripcion = trim(filter_var($_POST['descripcion'], FILTER_SANITIZE_SPECIAL_CHARS));
$observaciones = trim(filter_var($_POST['observaciones'], FILTER_SANITIZE_SPECIAL_CHARS));
$id_servicio = filter_var($_POST['id_servicio'], FILTER_VALIDATE_INT);

if (!$id_servicio) {
    //echo json_encode(["error" => "El servicio seleccionado no es válido."]);
    $_SESSION['error'] = "Algo anduvo mal. El servicio seleccionado no es válido.";
    header('Location: ../../index.php?modulo=formularios');
    exit();
}

// Validar el archivo subido
$archivo = $_FILES['formulario'];
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$extensionesPermitidas = ['pdf'];

if (!in_array($extension, $extensionesPermitidas)) {
    //echo json_encode(["error" => "Tipo de archivo no permitido. Solo se aceptan DOC, DOCX y PDF."]);
    $_SESSION['error'] = "Algo anduvo mal. Tipo de archivo no permitido. Solo se aceptan archivos PDF.";
    header('Location: ../../index.php?modulo=formularios');
    exit();
}

// Crear carpeta de destino si no existe
$carpetaDestino = '../../docs/formularios/';
if (!is_dir($carpetaDestino)) {
    if (!mkdir($carpetaDestino, 0755, true)) {
        //echo json_encode(["error" => "No se pudo crear el directorio de destino."]);
        $_SESSION['error'] = "Algo anduvo mal al intentar crear el directorio de destino.";
        header('Location: ../../index.php?modulo=formularios');
        exit();
    }
}

// Generar nombre único para el archivo
$nombreArchivo = uniqid("form_") . ".$extension";
$rutaArchivo = $carpetaDestino . $nombreArchivo;

// Definir la ruta relativa para guardar en la base de datos
$rutaRelativa = "docs/formularios/" . $nombreArchivo;

// Mover el archivo subido
if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
    //echo json_encode(["error" => "Error al subir el archivo al servidor."]);
    $_SESSION['error'] = "Algo anduvo mal al intentar subir el archivo al servidor.";
    header('Location: ../../index.php?modulo=formularios');
    exit();
}

// Insertar en la base de datos
try {
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sentencia = $bd->prepare("
        INSERT INTO documentos_formularios 
        (nombre, descripcion, observaciones, id_servicio, ruta, id_usuario_carga, so_usuario_carga, ip_usuario_carga, fecha_carga) 
        VALUES 
        (:nombre, :descripcion, :observaciones, :id_servicio, :ruta, :id_usuario_carga, :so_usuario_carga, :ip_usuario_carga, NOW())
    ");

    $resultado = $sentencia->execute([
        ':nombre' => $nombre,
        ':descripcion' => $descripcion,
        ':observaciones' => $observaciones,
        ':id_servicio' => $id_servicio,
        ':ruta' => $rutaRelativa, // Guardar la ruta relativa
        ':id_usuario_carga' => $id_usuario,
        ':so_usuario_carga' => $SO,
        ':ip_usuario_carga' => $IP
    ]);

    if ($resultado) {
        $_SESSION['ok'] = "Se ha registrado el formulario $nombre correctamente.";
        header('Location: ../../index.php?modulo=formularios');
        //echo json_encode(["success" => "Formulario registrado exitosamente.", "archivo" => $nombreArchivo]);
    } else {
        $_SESSION['error'] = "Algo anduvo mal al intentar registrar el formulario.";
        header('Location: ../../index.php?modulo=formularios');
        //echo json_encode(["error" => "Error al registrar el formulario en la base de datos."]);
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Algo anduvo mal al intentar registrar el formulario.";
    header('Location: ../../index.php?modulo=formularios');
    //error_log("Error PDO: " . $e->getMessage());
    //echo json_encode(["error" => "Error interno del sistema."]);
    exit();
}
?>

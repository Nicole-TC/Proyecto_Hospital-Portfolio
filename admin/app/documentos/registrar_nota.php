<?php
session_start();
include_once '../../model/conexion.php';
require_once '../../funciones/ip.php'; // Devuelve la IP
require_once '../../funciones/mobile_detect.php'; // Devuelve Sistema operativo

// Validar si la sesión contiene el ID del usuario
if (empty($_SESSION['id'])) {
    $_SESSION['error'] = "La sesión ha expirado o el usuario no está autenticado.";
    header('Location: ../../index.php?modulo=notas');
    exit();
}

$id_usuario = $_SESSION['id']; // Obtener el ID del usuario autenticado

// Verificar datos requeridos
if (empty($_POST['asunto']) || empty($_POST['dni_personal']) || empty($_FILES['nota']['name'])) {
    $_SESSION['error'] = "Faltan datos necesarios para cargar la nota.";
    header('Location: ../../index.php?modulo=notas');
    exit();
}

// Sanitizar y validar entrada
$asunto = trim(filter_var($_POST['asunto'], FILTER_SANITIZE_SPECIAL_CHARS));
$descripcion = trim(filter_var($_POST['descripcion'], FILTER_SANITIZE_SPECIAL_CHARS));
$observaciones = trim(filter_var($_POST['observaciones'], FILTER_SANITIZE_SPECIAL_CHARS));
$dni_personal = filter_var($_POST['dni_personal'], FILTER_VALIDATE_INT);

$fecha = filter_var($_POST["fecha"], FILTER_SANITIZE_SPECIAL_CHARS);
$hora = filter_var($_POST["hora"], FILTER_SANITIZE_SPECIAL_CHARS);
$fechaHoraMySQL = $fecha . ' ' . $hora;
$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $fechaHoraMySQL);
$fecha_recibido = $dateTime->format('Y-m-d H:i:s');

if (!$dni_personal) {
    $_SESSION['error'] = "El agente seleccionado no es válido.";
    header('Location: ../../index.php?modulo=notas');
    exit();
}

// Validar el archivo subido
$archivo = $_FILES['nota'];
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$extensionesPermitidas = ['pdf', 'jpeg', 'jpg', 'png'];

if (!in_array($extension, $extensionesPermitidas)) {
    $_SESSION['error'] = "Tipo de archivo no permitido. Solo se aceptan PDF, JPEG, JPG y PNG.";
    header('Location: ../../index.php?modulo=notas');
    exit();
}

// Crear carpeta de destino si no existe
$carpetaDestino = '../../docs/notas/';
if (!is_dir($carpetaDestino)) {
    if (!mkdir($carpetaDestino, 0755, true)) {
        $_SESSION['error'] = "No se pudo crear el directorio de destino.";
        header('Location: ../../index.php?modulo=notas');
        exit();
    }
}

// Generar nombre único para el archivo
$nombreArchivo = uniqid("nota_") . ".$extension";
$rutaArchivo = $carpetaDestino . $nombreArchivo;

// Definir la ruta relativa para guardar en la base de datos
$rutaRelativa = "docs/notas/" . $nombreArchivo;

// Mover el archivo subido
if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
    $_SESSION['error'] = "Error al intentar subir el archivo al servidor.";
    header('Location: ../../index.php?modulo=notas');
    exit();
}

// Insertar en la base de datos
try {
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sentencia = $bd->prepare("
        INSERT INTO documentos_notas 
        (fecha_recibido, asunto, descripcion, observaciones, dni_personal, ruta, id_usuario_carga, so_usuario_carga, ip_usuario_carga, fecha_carga) 
        VALUES 
        (:fecha_recibido, :asunto, :descripcion, :observaciones, :dni_personal, :ruta, :id_usuario_carga, :so_usuario_carga, :ip_usuario_carga, NOW())
    ");

    $resultado = $sentencia->execute([
        ':fecha_recibido' => $fecha_recibido,
        ':asunto' => $asunto,
        ':descripcion' => $descripcion,
        ':observaciones' => $observaciones,
        ':dni_personal' => $dni_personal,
        ':ruta' => $rutaRelativa, // Guardar la ruta relativa
        ':id_usuario_carga' => $id_usuario,
        ':so_usuario_carga' => $SO,
        ':ip_usuario_carga' => $IP
    ]);

    if ($resultado) {
        $_SESSION['ok'] = "Se ha registrado la nota '$asunto' correctamente.";
        header('Location: ../../index.php?modulo=notas');
    } else {
        $_SESSION['error'] = "Algo anduvo mal al intentar registrar la nota.";
        header('Location: ../../index.php?modulo=notas');
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error interno del sistema.";
    header('Location: ../../index.php?modulo=notas');
    exit();
}
?>

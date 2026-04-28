<?php
session_start();

require_once '../../includes/validar_permiso.php';
validar_permiso($_SESSION["editar_usuario"] ?? 0);

error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido.";
    header('Location: ../../index.php?modulo=menu_usuarios');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null) {
    $_SESSION['error'] = "Algo no anduvo bien al intentar editar el usuario.";
    header('Location: ../../index.php?modulo=usuarios_activos');
    exit;
}

require_once '../../funciones/funciones.php';
require_once '../../model/conexion.php';

$consulta = $bd->prepare("SELECT id FROM admin WHERE id = ?");
$consulta->execute([$id]);
$usuario = $consulta->fetch(PDO::FETCH_OBJ);

if (!$usuario) {
    $_SESSION['error'] = "El usuario no existe.";
    header('Location: ../../index.php?modulo=menu_usuarios');
    exit;
}

$dni = filter_input(INPUT_POST, 'dni', FILTER_VALIDATE_INT);
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
$apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_SPECIAL_CHARS);
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_SPECIAL_CHARS);
$date_time = filter_input(INPUT_POST, 'date_time', FILTER_SANITIZE_SPECIAL_CHARS);

if ($dni === false || empty($nombre) || empty($apellido) || empty($username)) {
    $_SESSION['error'] = "Faltan datos obligatorios o hay datos inválidos.";
    header('Location: ../../index.php?modulo=menu_usuarios');
    exit;
}

if (empty($date_time)) {
    $date_time = date('Y-m-d H:i:s');
}

$admin = boton_switch($_POST["admin"] ?? 0);
$debug = boton_switch($_POST["debug"] ?? 0);
$miperfil = boton_switch($_POST["miperfil"] ?? 0);

$menu_usuarios = boton_switch($_POST["menu_usuarios"] ?? 0);
$nuevo_usuario = boton_switch($_POST["nuevo_usuario"] ?? 0);
$editar_usuario = boton_switch($_POST["editar_usuario"] ?? 0);
$usuarios_activos = boton_switch($_POST["usuarios_activos"] ?? 0);

$notas = boton_switch($_POST["notas"] ?? 0);
$paginas_ayuda = boton_switch($_POST["paginas_ayuda"] ?? 0);

$activo = boton_switch($_POST["activo"] ?? 0);
$en_construccion = boton_switch($_POST["en_construccion"] ?? 0);

$sentencia = $bd->prepare("
    UPDATE admin 
    SET 
        dni = ?, 
        nombre = ?, 
        apellido = ?, 
        username = ?, 
        email = ?, 
        telefono = ?, 
        admin = ?, 
        debug = ?, 
        miperfil = ?, 
        menu_usuarios = ?, 
        nuevo_usuario = ?, 
        editar_usuario = ?, 
        usuarios_activos = ?, 
        notas = ?, 
        paginas_ayuda = ?, 
        activo = ?, 
        date_time = ?, 
        en_construccion = ? 
    WHERE id = ?
");

$resultado = $sentencia->execute([
    $dni,
    $nombre,
    $apellido,
    $username,
    $email,
    $telefono,
    $admin,
    $debug,
    $miperfil,
    $menu_usuarios,
    $nuevo_usuario,
    $editar_usuario,
    $usuarios_activos,
    $notas,
    $paginas_ayuda,
    $activo,
    $date_time,
    $en_construccion,
    $id
]);

if ($resultado) {
    $_SESSION['ok'] = "Los datos del usuario [$id] $apellido, $nombre, han sido actualizados correctamente.";
} else {
    $_SESSION["error"] = "No se pudieron editar los datos de $apellido, $nombre.";
}

header('Location: ../../index.php?modulo=menu_usuarios');
exit;
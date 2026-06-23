<?php
ob_start();
session_start();

require_once '../../includes/validar_permiso.php';
validar_permiso($_SESSION["nuevo_usuario"] ?? 0);

error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido.";
    header('Location: ../../index.php?modulo=nuevo_usuario');
    exit;
}

require_once '../../funciones/funciones.php';
require_once '../../model/conexion.php';

$dni = filter_input(INPUT_POST, 'dni', FILTER_VALIDATE_INT);
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
$apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_SPECIAL_CHARS);
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
$pass = $_POST["password"] ?? '';
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_SPECIAL_CHARS);

if (
    $dni === false ||
    empty($nombre) ||
    empty($apellido) ||
    empty($username) ||
    empty($pass)
) {
    $_SESSION['error'] = "Faltan datos obligatorios.";
    header('Location: ../../index.php?modulo=nuevo_usuario');
    exit;
}

$password = password_hash($pass, PASSWORD_BCRYPT, ["cost" => 12]);

// PERMISOS (solo los que existen en la tabla)
$admin = boton_switch($_POST["admin"] ?? 0);
$debug = boton_switch($_POST["debug"] ?? 0);
$miperfil = boton_switch($_POST["miperfil"] ?? 0);

$menu_usuarios = boton_switch($_POST["menu_usuarios"] ?? 0);
$nuevo_usuario = boton_switch($_POST["nuevo_usuario"] ?? 0);
$editar_usuario = boton_switch($_POST["editar_usuario"] ?? 0);
$usuarios_activos = boton_switch($_POST["usuarios_activos"] ?? 0);

$notas = boton_switch($_POST["notas"] ?? 0);
$paginas_ayuda = boton_switch($_POST["paginas_ayuda"] ?? 0);

$menu_digitalizar = boton_switch($_POST["menu_digitalizar"] ?? 0);
$dig_nuevo_documento = boton_switch($_POST["dig_nuevo_documento"] ?? 0);
$dig_buscar_documentos = boton_switch($_POST["dig_buscar_documentos"] ?? 0);
$dig_admin_etiquetas = boton_switch($_POST["dig_admin_etiquetas"] ?? 0);
$dig_tipos_documentos = boton_switch($_POST["dig_tipos_documentos"] ?? 0);
$dig_auditoria = boton_switch($_POST["dig_auditoria"] ?? 0);
$dig_servicios = boton_switch($_POST["dig_servicios"] ?? 0);

$activo = boton_switch($_POST["activo"] ?? 0);
$en_construccion = boton_switch($_POST["en_construccion"] ?? 0);

// Campo fecha
$date_time = date('Y-m-d H:i:s');

$sentencia = $bd->prepare("
    INSERT INTO admin (
        dni,
        nombre,
        apellido,
        username,
        password,
        email,
        telefono,
        admin,
        debug,
        miperfil,
        menu_usuarios,
        nuevo_usuario,
        editar_usuario,
        usuarios_activos,
        notas,
        paginas_ayuda,
        activo,
        date_time,
        en_construccion,
        menu_digitalizar,
        dig_nuevo_documento,
        dig_buscar_documentos,
        dig_admin_etiquetas,
        dig_tipos_documentos,
        dig_auditoria,
        dig_servicios
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?
    )
");

$resultado = $sentencia->execute([
    $dni,
    $nombre,
    $apellido,
    $username,
    $password,
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
    $menu_digitalizar,
    $dig_nuevo_documento,
    $dig_buscar_documentos,
    $dig_admin_etiquetas,
    $dig_tipos_documentos,
    $dig_auditoria,
    $dig_servicios
]);

if ($resultado) {
    $_SESSION['ok'] = "Usuario $apellido, $nombre creado correctamente.";
} else {
    $_SESSION['error'] = "Error al crear el usuario.";
}

header('Location: ../../index.php?modulo=menu_usuarios');
exit;